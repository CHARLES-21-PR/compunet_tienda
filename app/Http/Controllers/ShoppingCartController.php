<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            // If this is a 'buy now' action, validate only against requested qty (do not add existing),
            // because user intent is to purchase the displayed quantity now (and frontend will redirect to checkout).
            $isBuyNow = $request->boolean('buy_now');
            $requestedTotal = $isBuyNow ? $qty : ($existing + $qty);
            if ($requestedTotal > intval($product->stock)) {
                // allowed_add: how many additional units can be added to current cart (for non-buy-now)
                $allowed = $isBuyNow ? intval($product->stock) : (intval($product->stock) - $existing);
                if ($allowed < 0) $allowed = 0;
                if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
                    return response()->json(['success' => false, 'message' => 'Stock insuficiente', 'allowed_add' => $allowed, 'allowed_total' => intval($product->stock)], 422);
                }
                return back()->with('error', 'Stock insuficiente. Solo quedan ' . intval($product->stock) . ' unidades.');
            }
        }

        $isBuyNow = $request->boolean('buy_now');

        $key = $product->id;
        $itemData = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $qty,
            'image' => $product->image ? $product->image : null,
        ];

        if ($isBuyNow) {
            // Do not alter the stored cart for 'buy now'. Create a temporary selection
            // used only for immediate checkout so the user's cart (badge/count) remains.
            session(['cart_selected' => [$key => $itemData]]);
        } else {
            $cart = session('cart', []);
            if (isset($cart[$key])) {
                $cart[$key]['quantity'] = intval($cart[$key]['quantity']) + $qty;
            } else {
                $cart[$key] = $itemData;
            }
            session(['cart' => $cart]);
        }

        // Totales simples: calcular sobre el carrito persistente (no sobre la selección temporal)
        $persistentCart = session('cart', []);
        $count = $persistentCart ? array_sum(array_column($persistentCart, 'quantity')) : 0;
        $total = 0;
        foreach ($persistentCart as $item) {
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

    /**
     * Prepare checkout with selected cart keys (session)
     * Accepts JSON { selected: [key1, key2, ...] }
     */
    public function checkoutSelected(Request $request)
    {
        $data = $request->validate([
            'selected' => 'required|array|min:1'
        ]);

        $selected = $data['selected'];
        $cart = session('cart', []);
        if (!is_array($cart) || empty($cart)) {
            return response()->json(['success' => false, 'message' => 'El carrito está vacío'], 422);
        }

        $new = [];
        foreach ($selected as $k) {
            // if frontend sent the exact session key
            if (isset($cart[$k])) {
                $new[$k] = $cart[$k];
                continue;
            }

            // if frontend sent a numeric id or string id, try to match by 'id' or 'product_id' inside cart entries
            foreach ($cart as $ck => $entry) {
                // entry may be array with 'id' or 'product_id'
                if (is_array($entry)) {
                    if (isset($entry['id']) && ((string)$entry['id'] === (string)$k)) {
                        $new[$ck] = $entry; break;
                    }
                    if (isset($entry['product_id']) && ((string)$entry['product_id'] === (string)$k)) {
                        $new[$ck] = $entry; break;
                    }
                } else {
                    // entry could be object-like
                    if (isset($entry->id) && ((string)$entry->id === (string)$k)) {
                        $new[$ck] = (array)$entry; break;
                    }
                }
            }
        }

        // Log for debugging if selection doesn't match
        if (empty($new)) {
            Log::debug('checkoutSelected: no matches', ['selected' => $selected, 'cart_keys' => array_keys($cart)]);
        } else {
            Log::debug('checkoutSelected: matched items', ['selected' => $selected, 'matched_keys' => array_keys($new)]);
        }

        if (empty($new)) {
            return response()->json(['success' => false, 'message' => 'No se encontraron los productos seleccionados en el carrito'], 422);
        }

        // Store selected items separately (non-destructive): use 'cart_selected'
        session(['cart_selected' => $new]);

        return response()->json(['success' => true]);
    }
}
