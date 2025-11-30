<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function index($id_product)
    {
        $product = Product::find($id_product);

        return view('categories_products.product_details', compact('product'));
    }
}
