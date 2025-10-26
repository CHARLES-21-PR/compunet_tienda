<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategorySettingsController extends Controller
{
    public function index()
    {
        // puedes cambiar a paginate(15) si quieres paginación
        $categories = Category::orderBy('id')->get();
        return view('settings.categories.index', compact('categories'));
    }
    
    /**
     * Mostrar formulario de creación de categoría
     */
    public function create()
    {
        return view('settings.categories.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:categories,name',
        ]);

        Category::create([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('settings.categories.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function edit(Category $category)
    {
        return view('settings.categories.edit', compact('category'));
    }
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('settings.categories.index')->with('success', 'Categoría actualizada exitosamente.');
    }
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('settings.categories.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}
