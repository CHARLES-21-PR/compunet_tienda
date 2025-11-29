<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductSettingsController extends Controller
{
    public function index(Request $request)
    {
        // Build query with optional search and category filter
        $q = $request->query('q');
        $categoryId = $request->query('category');

        $query = Product::with('category');

        if (!empty($q)) {
            $query->where(function($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('id')->paginate(10)->withQueryString();

        // categories for the filter select
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'q', 'categoryId'));
    }
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        request()->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'nullable|integer',
            'brand' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'status' => 'nullable|in:activo,inactivo',
        ]);

        
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                try {
                    $imagePath = $file->store('products', 'public');
                } catch (\Throwable $e) {
                    
                    Log::error('Error al almacenar imagen de producto', ['error' => $e->getMessage()]);
                    $imagePath = null;
                }
            } else {
                Log::warning('Archivo de imagen inválido en ProductSettingsController@store', ['user' => optional(auth()->user())->id]);
            }
        }

        Product::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'category_id' => $request->input('category_id'),
            'stock' => $request->input('stock', 0),
            'brand' => $request->input('brand'),
            'image' => $imagePath,
            'status' => $request->input('status', 'activo'),
        ]);
        return redirect()->route('settings.products.index')->with('success', 'Producto creado exitosamente.');
    }
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        // If this is an AJAX request (modal), return only the form partial to avoid full layout/nav being returned
        if (request()->ajax() || request()->wantsJson()) {
            return view('admin.products.partials.edit-form', compact('product', 'categories'));
        }
        return view('admin.products.edit', compact('product', 'categories'));
    }
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'nullable|integer|min:0',
            'brand' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
            'status' => 'nullable|in:activo,inactivo',
        ]);

        $data = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'category_id' => $request->input('category_id'),
            'stock' => $request->input('stock') ?? $product->stock,
            'brand' => $request->input('brand') ?? $product->brand,
            'status' => $request->input('status') ?? $product->status,
        ];

       
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado exitosamente.');
    }
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
