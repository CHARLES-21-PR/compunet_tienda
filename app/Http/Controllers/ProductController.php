<?php

namespace App\Http\Controllers;
use App\Models\Product;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    function index($id_product)
    {
        $product = Product::find($id_product);
        return view('categories_products.product_details', compact('product'));
    }
}
