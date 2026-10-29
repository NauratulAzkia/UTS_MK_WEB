<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\InventoryTransactionService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('user')->when(! $request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id));
        $this->filter($request, $query);

        return view('transactions.index', ['type' => 'sales', 'title' => 'Penjualan', 'rows' => $query->latest('sold_at')->paginate(12)->withQueryString()]);
    }

    public function create()
    {
        return view('transactions.create', ['type' => 'sales', 'title' => 'Penjualan baru', 'products' => Product::orderBy('name')->get()]);
    }

    public function store(Request $request, InventoryTransactionService $service)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);
        $sale = $service->createSale($data, $request->user()->id);

        return redirect()->route('sales.show', $sale)->with('success', 'Penjualan tersimpan dan stok berkurang.');
    }

    public function show(Request $request, Sale $sale)
    {
        abort_unless($request->user()->isAdmin() || $sale->user_id === $request->user()->id, 403);

        return view('transactions.show', ['type' => 'sales', 'title' => 'Detail penjualan', 'row' => $sale->load(['user', 'items.product'])]);
    }

    public function cancel(Sale $sale, InventoryTransactionService $service)
    {
        $service->cancelSale($sale);

        return back()->with('success', 'Penjualan dibatalkan dan stok dipulihkan.');
    }

    private function filter(Request $request, $query): void
    {
        $filters = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'status' => ['nullable', 'in:completed,cancelled']]);
        $query->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('sold_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('sold_at', '<=', $date))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));
    }
}
