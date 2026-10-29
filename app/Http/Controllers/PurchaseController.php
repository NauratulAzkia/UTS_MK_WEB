<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\InventoryTransactionService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'user'])->when(! $request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id));
        $this->filter($request, $query, 'purchased_at');

        return view('transactions.index', ['type' => 'purchases', 'title' => 'Pembelian', 'rows' => $query->latest('purchased_at')->paginate(12)->withQueryString()]);
    }

    public function create()
    {
        return view('transactions.create', ['type' => 'purchases', 'title' => 'Pembelian baru', 'products' => Product::orderBy('name')->get(), 'suppliers' => Supplier::orderBy('name')->get()]);
    }

    public function store(Request $request, InventoryTransactionService $service)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ]);
        $purchase = $service->createPurchase($data, $request->user()->id);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Pembelian tersimpan dan stok bertambah.');
    }

    public function show(Request $request, Purchase $purchase)
    {
        abort_unless($request->user()->isAdmin() || $purchase->user_id === $request->user()->id, 403);

        return view('transactions.show', ['type' => 'purchases', 'title' => 'Detail pembelian', 'row' => $purchase->load(['supplier', 'user', 'items.product'])]);
    }

    public function cancel(Purchase $purchase, InventoryTransactionService $service)
    {
        $service->cancelPurchase($purchase);

        return back()->with('success', 'Pembelian dibatalkan dan stok diperbarui.');
    }

    private function filter(Request $request, $query, string $dateColumn): void
    {
        $filters = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'status' => ['nullable', 'in:completed,cancelled']]);
        $query->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate($dateColumn, '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate($dateColumn, '<=', $date))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));
    }
}
