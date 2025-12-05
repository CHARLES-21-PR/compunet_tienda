<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategorySettingsController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSettingsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/settings/nuestras_tiendas', [Controller::class, 'nuestra_tiendas'])->name('nuestras_tiendas');

Route::get('/Internet_Ilimitado', [Controller::class, 'Internet_Ilimitado'])->name('Internet_Ilimitado');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas para clientes autenticados
Route::middleware('auth')->group(function () {
    // Mis pedidos (vista ya creada)
    Route::get('/mis-pedidos', function () {
        return view('View_Client.MisPedidos');
    })->name('client.orders.index');

    // Descargar comprobante (solo propietario)
    Route::get('/mis-pedidos/{order}/invoice/download', [\App\Http\Controllers\ClientOrderController::class, 'downloadInvoice'])->name('client.orders.invoice.download');
});

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/products/{id_product}', [ProductController::class, 'index'])->name('products.details');

// Ruta para que los clientes vean sus pedidos (Mis pedidos)
Route::middleware('auth')->get('/mis-pedidos', function () {
    return view('View_Client.MisPedidos');
})->name('client.orders.index');

// Carrito de compras - público (accesible sin rol admin)
Route::get('/shopping-cart', [\App\Http\Controllers\ShoppingCartController::class, 'index'])->name('shopping_carts.index');
// Checkout routes
Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/pay', [\App\Http\Controllers\CheckoutController::class, 'pay'])->name('checkout.pay');
Route::get('/checkout/success/{order}', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
// Añadir item al carrito (público - actualiza la sesión para invitados)
Route::post('/shopping-cart/add', [\App\Http\Controllers\ShoppingCartController::class, 'add'])->name('shopping_carts.add');
// Actualizar cantidad de un item en el carrito
Route::post('/shopping-cart/update', [\App\Http\Controllers\ShoppingCartController::class, 'update'])->name('shopping_carts.update');
// Eliminar item del carrito
Route::post('/shopping-cart/remove', [\App\Http\Controllers\ShoppingCartController::class, 'remove'])->name('shopping_carts.remove');
// Preparar checkout con items seleccionados
Route::post('/shopping-cart/checkout-selected', [\App\Http\Controllers\ShoppingCartController::class, 'checkoutSelected'])->name('shopping_carts.checkout_selected');

// Ruta temporal de depuración: devuelve el contenido de la sesión 'cart' en JSON
Route::get('/debug/session-cart', function () {
    return response()->json(session('cart', []));
});

Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/settings/categories', [CategorySettingsController::class, 'index'])->name('admin.categories.index');

    // Dashboard de administracion
    Route::get('/settings/dashboard', [Controller::class, 'index_dashboard'])->name('admin.dashboard.index');

    // Endpoint to fetch low-stock products for a category (AJAX)
    Route::get('/settings/categories/{category}/low-products', [Controller::class, 'low_products'])->name('admin.categories.low_products');

    // crud categorias
    Route::get('/settings/categories/create', [CategorySettingsController::class, 'create'])->name('admin.categories.create');
    Route::post('/settings/categories', [CategorySettingsController::class, 'store'])->name('admin.categories.store');
    Route::get('/settings/categories/{category:id}/edit', [CategorySettingsController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/settings/categories/{category:id}', [CategorySettingsController::class, 'update'])->name('admin.categories.update');
    Route::delete('/settings/categories/{category:id}', [CategorySettingsController::class, 'destroy'])->name('admin.categories.destroy');

    // crud productos

    Route::get('/settings/products', [ProductSettingsController::class, 'index'])->name('admin.products.index');
    Route::get('/settings/products/create', [ProductSettingsController::class, 'create'])->name('admin.products.create');
    Route::post('/settings/products', [ProductSettingsController::class, 'store'])->name('admin.products.store');
    Route::get('/settings/products/{product:id}/edit', [ProductSettingsController::class, 'edit'])->name('admin.products.edit');
    Route::put('/settings/products/{product:id}', [ProductSettingsController::class, 'update'])->name('admin.products.update');
    Route::delete('/settings/products/{product:id}', [ProductSettingsController::class, 'destroy'])->name('admin.products.destroy');

    // Pedidos (ordenes)
    Route::get('/settings/orders', [\App\Http\Controllers\OrderSettingsController::class, 'index'])->name('admin.orders.index');
    Route::get('/settings/orders/{order}', [\App\Http\Controllers\OrderSettingsController::class, 'show'])->name('admin.orders.show');
    Route::get('/settings/orders/{order}/edit', [\App\Http\Controllers\OrderSettingsController::class, 'edit'])->name('admin.orders.edit');
    Route::put('/settings/orders/{order}', [\App\Http\Controllers\OrderSettingsController::class, 'update'])->name('admin.orders.update');
    Route::delete('/settings/orders/{order}', [\App\Http\Controllers\OrderSettingsController::class, 'destroy'])->name('admin.orders.destroy');
    Route::post('/settings/orders/{order}/invoice', [\App\Http\Controllers\OrderSettingsController::class, 'generateInvoice'])->name('admin.orders.generate_invoice');
    Route::post('/settings/orders/{order}/invoice/ajax', [\App\Http\Controllers\OrderSettingsController::class, 'generateInvoiceAjax'])->name('admin.orders.generate_invoice_ajax');
    // Fallback GET that generates invoice and redirects to download (useful when JS fails)
    Route::get('/settings/orders/{order}/invoice/generate-download', [\App\Http\Controllers\OrderSettingsController::class, 'generateInvoiceDownload'])->name('admin.orders.generate_invoice_download');
    Route::get('/settings/invoices/{invoice}/download', [\App\Http\Controllers\OrderSettingsController::class, 'downloadInvoice'])->name('admin.invoices.download');

    // Clientes (CRUD básico)
    Route::get('/settings/clients', [\App\Http\Controllers\Settings\ClientController::class, 'index'])->name('admin.clients.index');
    Route::get('/settings/clients/create', [\App\Http\Controllers\Settings\ClientController::class, 'create'])->name('admin.clients.create');
    Route::post('/settings/clients', [\App\Http\Controllers\Settings\ClientController::class, 'store'])->name('admin.clients.store');
    Route::get('/settings/clients/{client}/edit', [\App\Http\Controllers\Settings\ClientController::class, 'edit'])->name('admin.clients.edit');
    Route::put('/settings/clients/{client}', [\App\Http\Controllers\Settings\ClientController::class, 'update'])->name('admin.clients.update');
    Route::delete('/settings/clients/{client}', [\App\Http\Controllers\Settings\ClientController::class, 'destroy'])->name('admin.clients.destroy');
    Route::get('/settings/clients/{client}', [\App\Http\Controllers\Settings\ClientController::class, 'show'])->name('admin.clients.show');
    Route::get('/settings/orders/{order}/invoice/xml', [\App\Http\Controllers\OrderSettingsController::class, 'exportInvoiceXml'])->name('admin.orders.export_xml');

    // Notifications for admin (pending Yape orders, low stock)
    Route::get('/settings/notifications', [\App\Http\Controllers\Settings\NotificationController::class, 'index'])->name('admin.notifications.index');
});

require __DIR__.'/auth.php';
