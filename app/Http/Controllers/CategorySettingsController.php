<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategorySettingsController extends Controller
{
    public function index()
    {
        // Paginate categories to avoid loading all at once
        $categories = Category::orderBy('id')->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }
    
   
    public function create()
    {
        return view('admin.categories.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:categories,name',
        ]);

        Category::create([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría actualizada exitosamente.');
    }
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}
