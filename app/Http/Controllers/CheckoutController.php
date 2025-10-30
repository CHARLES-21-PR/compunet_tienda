<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Facades\Checkout;

class CheckoutController extends Controller
{
 public function procesar(Request $request)
    {
        $cliente = auth()->user();
        $productos = $request->input('productos');
        $monto = $request->input('monto');
        $tokenCliente = $request->input('token');

        try {
            $resultado = Checkout::procesarCompra($cliente, $productos, $monto, $tokenCliente);
            return response()->json(['success' => true, 'data' => $resultado]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
