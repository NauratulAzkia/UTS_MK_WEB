<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('masters.index', [
            'title' => 'Kategori', 'type' => 'categories',
            'rows' => Category::withCount('products')->orderBy('name')->paginate(12),
            'columns' => ['name' => 'Nama kategori', 'products_count' => 'Jumlah produk'],
        ]);
    }

    public function create()
    {
        return view('masters.form', ['title' => 'Kategori', 'type' => 'categories', 'row' => new Category]);
    }

    public function show(Category $category)
    {
        return view('masters.show', ['title' => 'Detail kategori', 'type' => 'categories', 'row' => $category->load('products')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:categories,name']]);
        Category::create($data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('masters.form', ['title' => 'Kategori', 'type' => 'categories', 'row' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        $category->update($request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)]]));

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['delete' => 'Kategori masih digunakan oleh produk.']);
        }
        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
