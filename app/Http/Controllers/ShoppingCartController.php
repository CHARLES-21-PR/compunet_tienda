<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\shoppingCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShoppingCartController extends Controller
{
    public function index()
    {
        // Si el usuario está autenticado, usar sus items guardados; si no, usar la sesión (invitado)
        if (Auth::check()) {
            $user = Auth::user();

            // Migrar carrito de sesión a base de datos si existe
            $sessionCart = session('cart', []);
            if (!empty($sessionCart)) {
                foreach ($sessionCart as $key => $item) {
                    $productId = $item['id'] ?? $item['product_id'] ?? $key;
                    $qty = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                    
                    $dbItem = shoppingCart::where('user_id', $user->id)->where('product_id', $productId)->first();
                    if ($dbItem) {
                        $dbItem->quantity += $qty;
                        $dbItem->save();
                    } else {
                        $prod = Product::find($productId);
                        if ($prod) {
                            shoppingCart::create([
                                'user_id' => $user->id,
                                'product_id' => $productId,
                                'quantity' => $qty,
                                'price' => $prod->price,
                            ]);
                        }
                    }
                }
                session()->forget('cart');
            }

            // Eager load la relación product para evitar N+1 queries
            $raw = shoppingCart::where('user_id', $user->id)->with('product')->get();
            // Normalize DB-stored cart items and prefer current product price from DB
            $cartItems = [];
            foreach ($raw as $it) {
                // Asegurar que usamos el precio actual del producto
                if ($it->product) {
                    // Bypass Eloquent accessor if any to ensure raw price
                    $rawPrice = DB::table('products')->where('id', $it->product_id)->value('price');
                    
                    // Sync price in shopping_carts table if it differs (e.g. if it was stored as 0.38)
                    if (abs($it->price - $rawPrice) > 0.01) {
                        DB::table('shoppings_carts')->where('id', $it->id)->update(['price' => $rawPrice]);
                        $it->price = $rawPrice;
                    }
                    
                    $it->product->price = $rawPrice;
                }
                // Pasamos el objeto modelo completo para que la vista pueda acceder a ->product
                $cartItems[$it->id] = $it;
            }
        } else {
            // Para invitados, leer la sesión y sincronizar precios con la base de datos
            $cart = session('cart', []);
            $normalized = [];
            if (is_array($cart)) {
                foreach ($cart as $key => $it) {
                    // determinar product id desde la entrada (varias formas posibles)
                    $productId = null;
                    if (is_array($it)) {
                        $productId = $it['id'] ?? $it['product_id'] ?? $key;
                    } elseif (is_object($it)) {
                        $productId = $it->id ?? $it->product_id ?? $key;
                    } else {
                        $productId = $key;
                    }

                    $product = Product::find($productId);

                    $priceFromDb = $product ? (float)$product->price : null;

                    // preferir precio desde la BD cuando esté disponible, sino mantener el precio en sesión
                    $price = $priceFromDb !== null ? $priceFromDb : (isset($it['price']) ? (float)$it['price'] : 0.0);

                    $entry = is_array($it) ? $it : (array)$it;
                    $entry['price'] = round($price, 2);
                    // garantizar quantity
                    $entry['quantity'] = isset($entry['quantity']) ? (int)$entry['quantity'] : (isset($entry['qty']) ? (int)$entry['qty'] : 1);

                    $normalized[$key] = $entry;
                }
                // actualizar la sesión con precios sincronizados (evita mostrar 0.40 en vistas)
                session(['cart' => $normalized]);
            }

            $cartItems = $normalized;
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
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($data['product_id']);
        $qty = intval($data['quantity']);
        $isBuyNow = $request->boolean('buy_now');

        // Logic for Authenticated User
        if (Auth::check()) {
            $user = Auth::user();

            // Validate stock
            if (isset($product->stock) && $product->stock !== null) {
                $existingItem = shoppingCart::where('user_id', $user->id)->where('product_id', $product->id)->first();
                $existingQty = $existingItem ? $existingItem->quantity : 0;
                
                $requestedTotal = $isBuyNow ? $qty : ($existingQty + $qty);
                
                if ($requestedTotal > intval($product->stock)) {
                    $allowed = $isBuyNow ? intval($product->stock) : (intval($product->stock) - $existingQty);
                    if ($allowed < 0) $allowed = 0;
                    
                    if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
                        return response()->json(['success' => false, 'message' => 'Stock insuficiente', 'allowed_add' => $allowed, 'allowed_total' => intval($product->stock)], 422);
                    }
                    return back()->with('error', 'Stock insuficiente. Solo quedan '.intval($product->stock).' unidades.');
                }
            }

            if ($isBuyNow) {
                // For buy now, use session 'cart_selected' temporarily
                $key = $product->id;
                $itemData = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $this->normalizePrice($product->price),
                    'quantity' => $qty,
                    'image' => $product->image ? $product->image : null,
                ];
                session(['cart_selected' => [$key => $itemData]]);
            } else {
                // Persistent cart in DB
                $cartItem = shoppingCart::where('user_id', $user->id)->where('product_id', $product->id)->first();
                if ($cartItem) {
                    $cartItem->quantity += $qty;
                    $cartItem->save();
                } else {
                    shoppingCart::create([
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'price' => $product->price,
                    ]);
                }
            }

            // Calculate totals
            $cartItems = shoppingCart::where('user_id', $user->id)->with('product')->get();
            $count = $cartItems->sum('quantity');
            $total = 0;
            foreach ($cartItems as $item) {
                $price = $item->product ? $item->product->price : $item->price;
                $total += $item->quantity * $price;
            }

        } else {
            // Logic for Guest (Session)
            // Validar stock si existe
            if (isset($product->stock) && $product->stock !== null) {
                // cantidad existente en carrito
                $cart = session('cart', []);
                $existing = isset($cart[$product->id]) ? intval($cart[$product->id]['quantity']) : 0;
                
                $requestedTotal = $isBuyNow ? $qty : ($existing + $qty);
                if ($requestedTotal > intval($product->stock)) {
                    // allowed_add: how many additional units can be added to current cart (for non-buy-now)
                    $allowed = $isBuyNow ? intval($product->stock) : (intval($product->stock) - $existing);
                    if ($allowed < 0) {
                        $allowed = 0;
                    }
                    if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
                        return response()->json(['success' => false, 'message' => 'Stock insuficiente', 'allowed_add' => $allowed, 'allowed_total' => intval($product->stock)], 422);
                    }

                    return back()->with('error', 'Stock insuficiente. Solo quedan '.intval($product->stock).' unidades.');
                }
            }

            $key = $product->id;
            $itemData = [
                'id' => $product->id,
                'name' => $product->name,
                // Normalize price at the source so views don't need defensive hacks
                'price' => $this->normalizePrice($product->price),
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
                $price = isset($item['price']) ? $this->normalizePrice($item['price']) : 0;
                $total += ($item['quantity'] * $price);
            }
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
            'quantity' => 'required|integer|min:1',
        ]);

        $key = $data['product_id'];
        $product = Product::find($key);
        $requested = intval($data['quantity']);

        // Validar stock si aplica
        if (isset($product->stock) && $product->stock !== null) {
            if ($requested > intval($product->stock)) {
                // si supera, retornar información para que el frontend ajuste
                return response()->json(['success' => false, 'message' => 'Stock insuficiente', 'allowed_max' => intval($product->stock)], 422);
            }
        }

        if (Auth::check()) {
            $user = Auth::user();
            $cartItem = shoppingCart::where('user_id', $user->id)->where('product_id', $key)->first();
            if ($cartItem) {
                $cartItem->quantity = $requested;
                $cartItem->save();
            }

            // Recalcular totales
            $cartItems = shoppingCart::where('user_id', $user->id)->with('product')->get();
            $count = $cartItems->sum('quantity');
            $total = 0;
            foreach ($cartItems as $item) {
                $price = $item->product ? $item->product->price : $item->price;
                $total += $item->quantity * $price;
            }
        } else {
            $cart = session('cart', []);
            if (isset($cart[$key])) {
                $cart[$key]['quantity'] = $requested;
                session(['cart' => $cart]);
            }

            // recalcular normalizando precios al leer
            $count = array_sum(array_column($cart, 'quantity'));
            $total = 0;
            foreach ($cart as $item) {
                $price = isset($item['price']) ? $this->normalizePrice($item['price']) : 0;
                $total += ($item['quantity'] * $price);
            }
        }

        return response()->json(['success' => true, 'count' => $count, 'total' => round($total, 2)]);
    }

    /**
     * Eliminar un item del carrito de sesión.
     */
    public function remove(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $key = $data['product_id'];

        if (Auth::check()) {
            $user = Auth::user();
            shoppingCart::where('user_id', $user->id)->where('product_id', $key)->delete();

            // Recalcular totales
            $cartItems = shoppingCart::where('user_id', $user->id)->with('product')->get();
            $count = $cartItems->sum('quantity');
            $total = 0;
            foreach ($cartItems as $item) {
                $price = $item->product ? $item->product->price : $item->price;
                $total += $item->quantity * $price;
            }
        } else {
            $cart = session('cart', []);
            if (isset($cart[$key])) {
                unset($cart[$key]);
                session(['cart' => $cart]);
            }

            $count = $cart ? array_sum(array_column($cart, 'quantity')) : 0;
            $total = 0;
            foreach ($cart as $item) {
                $price = isset($item['price']) ? $this->normalizePrice($item['price']) : 0;
                $total += ($item['quantity'] * $price);
            }
        }

        return response()->json(['success' => true, 'count' => $count, 'total' => round($total, 2)]);
    }

    /**
     * Ensure price is a float with two decimals and correct unit.
     * Some inputs (from older code or external data) accidentally store prices
     * as fractions (e.g. 0.4 meaning 40.00). Normalize here.
     */
    private function normalizePrice($price): float
    {
        $p = is_numeric($price) ? (float) $price : 0.0;
        // Keep simple: cast to float and round to 2 decimals.
        // Do NOT multiply by 100 here — prices should be stored in correct units at source.
        return round($p, 2);
    }

    /**
     * Prepare checkout with selected cart keys (session or DB)
     * Accepts JSON { selected: [key1, key2, ...] }
     */
    public function checkoutSelected(Request $request)
    {
        $data = $request->validate([
            'selected' => 'required|array|min:1',
        ]);

        $selected = $data['selected'];
        $new = [];

        if (Auth::check()) {
            // Authenticated: fetch from DB
            $user = Auth::user();
            // Fetch items where product_id is in the selected list
            $dbItems = shoppingCart::where('user_id', $user->id)
                ->whereIn('product_id', $selected)
                ->with('product')
                ->get();

            foreach ($dbItems as $item) {
                // Format as array compatible with CartService/CheckoutService
                // Ensure price is normalized
                $price = $item->product ? (float)$item->product->price : (float)$item->price;
                
                $new[$item->product_id] = [
                    'id' => $item->product_id,
                    'product_id' => $item->product_id,
                    'name' => $item->product ? $item->product->name : 'Producto',
                    'quantity' => $item->quantity,
                    'price' => $this->normalizePrice($price),
                    'image' => $item->product ? $item->product->image : null,
                ];
            }
        } else {
            // Guest: fetch from Session
            $cart = session('cart', []);
            if (! is_array($cart) || empty($cart)) {
                return response()->json(['success' => false, 'message' => 'El carrito está vacío'], 422);
            }

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
                        if (isset($entry['id']) && ((string) $entry['id'] === (string) $k)) {
                            $new[$ck] = $entry;
                            break;
                        }
                        if (isset($entry['product_id']) && ((string) $entry['product_id'] === (string) $k)) {
                            $new[$ck] = $entry;
                            break;
                        }
                    } else {
                        // entry could be object-like
                        if (isset($entry->id) && ((string) $entry->id === (string) $k)) {
                            $new[$ck] = (array) $entry;
                            break;
                        }
                    }
                }
            }
        }

        // Log for debugging if selection doesn't match
        if (empty($new)) {
            Log::debug('checkoutSelected: no matches', ['selected' => $selected, 'auth' => Auth::check()]);
            return response()->json(['success' => false, 'message' => 'No se encontraron los productos seleccionados para procesar el pago'], 422);
        } else {
            Log::debug('checkoutSelected: matched items', ['selected' => $selected, 'matched_count' => count($new)]);
        }

        // Store selected items separately (non-destructive): use 'cart_selected'
        session(['cart_selected' => $new]);

        return response()->json(['success' => true]);
    }
}
