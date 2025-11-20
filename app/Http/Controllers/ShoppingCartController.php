<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;

class ShoppingCartController extends Controller
{
    public function index()
    {
        // Si el usuario está autenticado, usar sus items guardados; si no, usar la sesión (invitado)
        if (Auth::check()) {
            $user = Auth::user();
            // Evitar errores si la relación no existe
            $cartItems = $user->shoppingCartItems ?? [];
        } else {
            $cartItems = session('cart', []);
        }

        return view('shopping_carts.index', compact('cartItems'));
    }

    /**
     * Añadir un producto al carrito (sesión para invitados).
     * Si la petición acepta JSON, devuelve count/total para actualizar el badge.
     */
    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::find($data['product_id']);
        $qty = intval($data['quantity']);
        // Validar stock si existe
        if (isset($product->stock) && $product->stock !== null) {
            // cantidad existente en carrito
            $cart = session('cart', []);
            $existing = isset($cart[$product->id]) ? intval($cart[$product->id]['quantity']) : 0;
            $requestedTotal = $existing + $qty;
            if ($requestedTotal > intval($product->stock)) {
                $allowed = intval($product->stock) - $existing;
                if ($allowed < 0) $allowed = 0;
                if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
                    return response()->json(['success' => false, 'message' => 'Stock insuficiente', 'allowed_add' => $allowed, 'allowed_total' => intval($product->stock)], 422);
                }
                return back()->with('error', 'Stock insuficiente. Solo quedan ' . intval($product->stock) . ' unidades.');
            }
        }

        $cart = session('cart', []);

        $key = $product->id;
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = intval($cart[$key]['quantity']) + $qty;
        } else {
            $cart[$key] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $qty,
                'image' => $product->image ? $product->image : null,
            ];
        }

        session(['cart' => $cart]);

        // Totales simples
        $count = array_sum(array_column($cart, 'quantity'));
        $total = 0;
        foreach ($cart as $item) {
            $total += ($item['quantity'] * $item['price']);
        }

        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'count' => $count,
                'total' => round($total, 2),
            ]);
        }

        // If request indicates 'buy_now' (from non-AJAX form submit), redirect to checkout
        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index');
        }

        return back()->with('success', 'Producto agregado al carrito');
    }

    /**
     * Actualizar la cantidad de un item en el carrito de sesión.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = session('cart', []);
        $key = $data['product_id'];
        $product = Product::find($key);

        // Validar stock si aplica
        if (isset($product->stock) && $product->stock !== null) {
            $requested = intval($data['quantity']);
            if ($requested > intval($product->stock)) {
                // si supera, retornar información para que el frontend ajuste
                return response()->json(['success' => false, 'message' => 'Stock insuficiente', 'allowed_max' => intval($product->stock)], 422);
            }
        }

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = intval($data['quantity']);
            session(['cart' => $cart]);
        }

        // recalcular
        $count = array_sum(array_column($cart, 'quantity'));
        $total = 0;
        foreach ($cart as $item) {
            $total += ($item['quantity'] * $item['price']);
        }

        return response()->json(['success' => true, 'count' => $count, 'total' => round($total,2)]);
    }

    /**
     * Eliminar un item del carrito de sesión.
     */
    public function remove(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id'
        ]);

        $cart = session('cart', []);
        $key = $data['product_id'];
        if (isset($cart[$key])) {
            unset($cart[$key]);
            session(['cart' => $cart]);
        }

        // recalcular
        $count = $cart ? array_sum(array_column($cart, 'quantity')) : 0;
        $total = 0;
        foreach ($cart as $item) {
            $total += ($item['quantity'] * $item['price']);
        }

        return response()->json(['success' => true, 'count' => $count, 'total' => round($total,2)]);
    }
}
