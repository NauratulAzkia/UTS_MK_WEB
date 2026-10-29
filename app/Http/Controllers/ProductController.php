<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->when($request->filled('q'), function ($query) use ($request) {
            $term = '%'.$request->string('q').'%';
            $query->where(fn ($inner) => $inner->where('name', 'like', $term)->orWhere('sku', 'like', $term));
        })->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->boolean('low_stock'), fn ($query) => $query->whereColumn('stock', '<=', 'minimum_stock'));

        return view('masters.index', [
            'title' => 'Produk', 'type' => 'products', 'rows' => $query->orderBy('name')->paginate(12)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'filters' => true,
            'columns' => ['sku' => 'SKU', 'name' => 'Nama', 'category.name' => 'Kategori', 'selling_price' => 'Harga jual', 'stock' => 'Stok', 'minimum_stock' => 'Batas minimum'],
        ]);
    }

    public function show(Product $product)
    {
        return view('masters.show', ['title' => 'Detail produk', 'type' => 'products', 'row' => $product->load('category')]);
    }

    public function create()
    {
        return view('masters.form', ['title' => 'Produk', 'type' => 'products', 'row' => new Product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        Product::create($this->validated($request));

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('masters.form', ['title' => 'Produk', 'type' => 'products', 'row' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request, $product));

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->purchaseItems()->exists() || $product->saleItems()->exists()) {
            return back()->withErrors(['delete' => 'Produk sudah tercatat dalam transaksi dan tidak dapat dihapus.']);
        }
        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ];
        if ($product === null) {
            $rules['stock'] = ['required', 'integer', 'min:0'];
        }

        return $request->validate($rules);
    }
}
