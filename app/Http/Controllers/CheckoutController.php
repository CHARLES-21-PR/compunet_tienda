<?php

namespace App\Http\Controllers;

use App\Facades\CheckoutFacade as Checkout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function index()
    {
        // Debug: log current cart state to help diagnose mismatches between cart icon and checkout items
        Log::debug('checkout.index session debug', ['cart' => session('cart'), 'cart_selected' => session('cart_selected')]);

        // ejemplo: preparar orden usando el resolver de precios (closure)
        $result = Checkout::prepareOrder(function ($item) {
            // si $item tiene price en session, use eso, sino buscar producto
            if (isset($item['price'])) {
                return $item['price'];
            }

            // fallback: buscar Product si es necesario
            return 0;
        });

        if (! $result['success']) {
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
            'yape_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            // card fields optional but may be required by PaymentService
            'card_number' => 'nullable|string',
            'card_holder' => 'nullable|string',
            'expiry' => 'nullable|string',
            'cvc' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        // Require at least dni or ruc for fiscal documents unless payment method is Yape (manual upload)
        $paymentMethod = $payload['payment_method'] ?? '';
        if ($paymentMethod !== 'yape') {
            if (empty($payload['dni']) && empty($payload['ruc'])) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Debe proporcionar DNI (para boleta) o RUC (para factura) antes de pagar.'], 422);
                }

                return back()->with('error', 'Debe proporcionar DNI (para boleta) o RUC (para factura) antes de pagar.');
            }
        }
        // preparar orden desde carrito
        $cartService = app(\App\Services\CartService::class);
        $cart = $cartService->getCart();
        $priceResolver = function ($item) {
            return isset($item['price']) ? $item['price'] : 0;
        };
        $prepare = Checkout::prepareOrder($priceResolver);
        if (! $prepare['success']) {
            if ($request->wantsJson()) {
                return response()->json(array_merge(['success' => false, 'message' => $prepare['message']], ['details' => $prepare]), 422);
            }

            // attach debug info to session so admin/dev can inspect
            return back()->with('error', $prepare['message'])->with('prepare_details', $prepare);
        }

        $subtotal = round($prepare['total'], 2);
        $igv = round($subtotal * 0.18, 2);
        $totalWithIgv = round($subtotal + $igv, 2);

        // Restricción: Yape sólo permitido para pedidos hasta S/.500 (<= 500). Si es mayor, rechazar.
        if (($paymentMethod === 'yape') && ($totalWithIgv > 500)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Yape sólo está disponible para pedidos hasta S/.500.'], 422);
            }

            return back()->with('error', 'Yape sólo está disponible para pedidos hasta S/.500.');
        }

        // crear orden en DB (total incluye IGV)
        // paymentMethod already computed above
        // usar estados en español: si es Yape -> 'pendiente', si es tarjeta -> 'pagado'
        $initialStatus = ($paymentMethod === 'yape') ? 'pendiente' : 'pagado';
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
            'status' => $initialStatus,
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
        // If Yape selected, expect a receipt upload and mark order as pending. Do not charge or decrement stock yet.
        if ($paymentMethod === 'yape') {
            if (! $request->hasFile('yape_receipt')) {
                // require receipt
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Debe subir el comprobante para Yape.'], 422);
                }

                return back()->with('error', 'Debe subir el comprobante para Yape.');
            }
            $file = $request->file('yape_receipt');
            try {
                $path = $file->store('receipts', 'public');
            } catch (\Throwable $e) {
                $path = null;
            }
            // create a payments record marking as pending verification
            DB::table('payments')->insert([
                'order_id' => $orderId,
                'method' => 'yape',
                'transaction_id' => null,
                'amount' => $totalWithIgv,
                'status' => 'pendiente',
                'metadata' => json_encode(['receipt_path' => $path]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // do not decrement stock or create invoice yet
        } else {
            // Standard flow: charge payment, decrement stock, mark paid and create invoice
            $charge = Checkout::chargePayment($payload);
            if (empty($charge['success'])) {
                // marcar orden como fallido
                DB::table('orders')->where('id', $orderId)->update(['status' => 'fallido']);
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Pago rechazado'], 402);
                }

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
                // marcar orden como fallido si no se pudo decrementar stock
                DB::table('orders')->where('id', $orderId)->update(['status' => 'fallido']);
                $names = implode(', ', $stockErrors);
                $msg = 'No fue posible reservar stock para: '.$names.'. Pedido marcado como fallido.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 409);
                }

                return back()->with('error', $msg);
            }

            // marcar orden como pagado (stock actualizado correctamente)
            DB::table('orders')->where('id', $orderId)->update(['status' => 'pagado']);

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
        }

        // Remove only the purchased items from the persistent cart (do not clear everything)
        try {
            $persistent = session('cart', []);
            if (is_array($persistent) && ! empty($persistent)) {
                foreach ($prepare['items'] as $it) {
                    $pid = null;
                    if (is_array($it) && isset($it['product'])) {
                        $prod = $it['product'];
                        $pid = is_object($prod) ? ($prod->id ?? null) : ($prod['id'] ?? null);
                    } elseif (is_array($it) && isset($it['product_id'])) {
                        $pid = $it['product_id'];
                    } elseif (is_array($it) && isset($it['id'])) {
                        $pid = $it['id'];
                    } elseif (is_object($it) && isset($it->product->id)) {
                        $pid = $it->product->id;
                    }

                    if ($pid !== null && isset($persistent[$pid])) {
                        unset($persistent[$pid]);
                    }
                }
                session(['cart' => $persistent]);
            }
        } catch (\Throwable $e) {
            // fallback: if anything fails, do not clear the cart to avoid data loss
            Log::error('Error removing purchased items from cart: '.$e->getMessage());
        }

        // Also clear any temporary selected items for checkout
        session()->forget('cart_selected');

        // flash session for showing the success modal on arrival
        $redirectUrl = route('checkout.success', ['order' => $orderId]);
        if ($request->wantsJson()) {
            // include a query param so the success view knows to auto-show modal
            return response()->json([
                'success' => true,
                'order_id' => $orderId,
                'redirect' => $redirectUrl.'?just_paid=1',
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
