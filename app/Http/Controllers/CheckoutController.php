<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Facades\CheckoutFacade as Checkout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        // ejemplo: preparar orden usando el resolver de precios (closure)
        $result = Checkout::prepareOrder(function($item){
            // si $item tiene price en session, use eso, sino buscar producto
            if (isset($item['price'])) return $item['price'];
            // fallback: buscar Product si es necesario
            return 0;
        });

        if (!$result['success']) {
            return redirect()->route('shopping_carts.index')->with('error', $result['message']);
        }

        // calcular IGV (18%) sobre el subtotal
        $subtotal = round($result['total'], 2);
        $igv = round($subtotal * 0.18, 2);
        $totalWithIgv = round($subtotal + $igv, 2);

        // mostrar la vista de checkout con items y desglose
        return view('checkout.index', [
            'items' => $result['items'],
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $totalWithIgv,
        ]);
    }

    public function pay(Request $request)
    {
        // ejemplo simple: charge y crear invoice
        $payload = $request->validate([
            'payment_method' => 'required|string',
            // customer info
            'customer_name' => 'required|string',
            'dni' => 'nullable|string',
            'ruc' => 'nullable|string',
            'address' => 'nullable|string',
            // card fields optional but may be required by PaymentService
            'card_number' => 'nullable|string',
            'card_holder' => 'nullable|string',
            'expiry' => 'nullable|string',
            'cvc' => 'nullable|string',
            'phone' => 'nullable|string'
        ]);

        // Require at least dni or ruc for fiscal documents
        if (empty($payload['dni']) && empty($payload['ruc'])) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Debe proporcionar DNI (para boleta) o RUC (para factura) antes de pagar.'], 422);
            return back()->with('error', 'Debe proporcionar DNI (para boleta) o RUC (para factura) antes de pagar.');
        }
        // preparar orden desde carrito
        $cartService = app(\App\Services\CartService::class);
        $cart = $cartService->getCart();
        $priceResolver = function($item){ return isset($item['price']) ? $item['price'] : 0; };
        $prepare = Checkout::prepareOrder($priceResolver);
        if (!$prepare['success']) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => $prepare['message']], 422);
            return back()->with('error', $prepare['message']);
        }

        $subtotal = round($prepare['total'], 2);
        $igv = round($subtotal * 0.18, 2);
        $totalWithIgv = round($subtotal + $igv, 2);

        // crear orden en DB (total incluye IGV)
        $shipping = [
            'name' => $payload['customer_name'] ?? null,
            'dni' => $payload['dni'] ?? null,
            'ruc' => $payload['ruc'] ?? null,
            'address' => $payload['address'] ?? null,
            'currency' => 'PEN',
        ];

        $orderId = \Illuminate\Support\Facades\DB::table('orders')->insertGetId([
            'user_id' => Auth::check() ? Auth::id() : null,
            'total' => $totalWithIgv,
            'status' => 'processing',
            'shipping_address' => json_encode($shipping),
            'currency' => 'PEN',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // insertar items
        foreach ($prepare['items'] as $it) {
            \Illuminate\Support\Facades\DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_id' => $it['product']->id ?? null,
                'name' => $it['product']->name ?? ($it['product'] ?? 'Producto'),
                'price' => $it['price'],
                'quantity' => $it['quantity'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // añadir datos al payload para el payment service
        $payload['order_id'] = $orderId;
        $payload['amount'] = $totalWithIgv;

        $charge = Checkout::chargePayment($payload);
        if (empty($charge['success'])) {
            // marcar orden como failed
            \Illuminate\Support\Facades\DB::table('orders')->where('id', $orderId)->update(['status' => 'failed']);
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Pago rechazado'], 402);
            return back()->with('error', 'Pago rechazado');
        }

        // Restar stock por cada item (usar InventoryService)
        $inventory = app(\App\Services\InventoryService::class);
        $stockErrors = [];
        foreach ($prepare['items'] as $it) {
            $prod = $it['product'];
            $qty = intval($it['quantity']);
            $ok = $inventory->decreaseStock($prod, $qty);
            if (! $ok) {
                $stockErrors[] = $prod->name ?? ($prod->id ?? 'producto');
            }
        }

        if (! empty($stockErrors)) {
            // marcar orden como failed si no se pudo decrementar stock
            \Illuminate\Support\Facades\DB::table('orders')->where('id', $orderId)->update(['status' => 'failed']);
            $names = implode(', ', $stockErrors);
            $msg = 'No fue posible reservar stock para: ' . $names . '. Pedido marcado como fallido.';
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => $msg], 409);
            return back()->with('error', $msg);
        }

        // marcar orden como paid (stock actualizado correctamente)
        \Illuminate\Support\Facades\DB::table('orders')->where('id', $orderId)->update(['status' => 'paid']);

        // crear invoice: pasar solo datos sanitizados de pago (método y transaction id)
        $paymentInfo = [
            'transaction_id' => $charge['transaction_id'] ?? null,
            'method' => isset($charge['method']) ? $charge['method'] : ($payload['payment_method'] ?? 'unknown'),
        ];

        $invoice = Checkout::createInvoice([
            'order_id' => $orderId,
            'transaction' => $paymentInfo['transaction_id'],
            'amount' => $totalWithIgv,
            'payment' => $paymentInfo,
        ]);

        // limpiar carrito
        $cartService->clear();

        // flash session for showing the success modal on arrival
        $redirectUrl = route('checkout.success', ['order' => $orderId]);
        if ($request->wantsJson()) {
            // include a query param so the success view knows to auto-show modal
            return response()->json([
                'success' => true,
                'order_id' => $orderId,
                'redirect' => $redirectUrl . '?just_paid=1'
            ]);
        }

        return redirect()->route('checkout.success', ['order' => $orderId])->with('just_paid', true);
    }

    /**
     * Mostrar página de éxito/confirmación de compra
     */
    public function success($order)
    {
        $orderRow = DB::table('orders')->where('id', $order)->first();
        if (! $orderRow) {
            return redirect()->route('categories.index')->with('error', 'Orden no encontrada.');
        }

        $items = DB::table('order_items')->where('order_id', $orderRow->id)->get();
        $payment = DB::table('payments')->where('order_id', $orderRow->id)->orderBy('id', 'desc')->first();
        $invoice = DB::table('invoices')->where('order_id', $orderRow->id)->orderBy('id', 'desc')->first();

        return view('checkout.success', [
            'order' => $orderRow,
            'items' => $items,
            'payment' => $payment,
            'invoice' => $invoice,
        ]);
    }
}
