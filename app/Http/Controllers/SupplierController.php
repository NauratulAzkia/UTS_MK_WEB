<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        return view('masters.index', [
            'title' => 'Pemasok', 'type' => 'suppliers', 'rows' => Supplier::orderBy('name')->paginate(12),
            'columns' => ['name' => 'Nama', 'phone' => 'Telepon', 'email' => 'Email'],
        ]);
    }

    public function show(Supplier $supplier)
    {
        return view('masters.show', ['title' => 'Detail pemasok', 'type' => 'suppliers', 'row' => $supplier]);
    }

    public function create()
    {
        return view('masters.form', ['title' => 'Pemasok', 'type' => 'suppliers', 'row' => new Supplier]);
    }

    public function store(Request $request)
    {
        Supplier::create($this->validated($request));

        return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('masters.form', ['title' => 'Pemasok', 'type' => 'suppliers', 'row' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request, $supplier));

        return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return back()->withErrors(['delete' => 'Pemasok memiliki riwayat pembelian dan tidak dapat dihapus.']);
        }
        $supplier->delete();

        return back()->with('success', 'Pemasok berhasil dihapus.');
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('suppliers')->ignore($supplier?->id)],
        ]);
    }
}
