<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductSettingsController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('settings.products.index', compact('products'));
    }
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('settings.products.create', compact('categories'));
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
        return view('settings.products.edit', compact('product', 'categories'));
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

        return redirect()->route('settings.products.index')->with('success', 'Producto actualizado exitosamente.');
    }
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('settings.products.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
