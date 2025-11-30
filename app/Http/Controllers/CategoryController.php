<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Schema;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return view('categories.index', compact('categories'));
    }

    public function show($category)
    {
        if (Schema::hasColumn('categories', 'slug')) {
            $cat = Category::where('slug', $category)
                ->orWhereRaw('LOWER(name) = ?', [strtolower($category)])
                ->first();
        } else {
            $cat = Category::whereRaw('LOWER(name) = ?', [strtolower($category)])->first();
        }

        if (! $cat) {

            $cat = new Category;
            $cat->name = ucwords(str_replace(['-', '_'], ' ', $category));
            $cat->setRelation('products', collect());
        } else {
            $cat->load('products');
        }

        return view('categories_products.view_cat_prod', ['category' => $cat]);
    }
}
