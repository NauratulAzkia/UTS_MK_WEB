<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\InventoryTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomerOrderController extends Controller
{
    public function create(Request $request)
    {
        $selectedProduct = $request->integer('product');

        return view('shop.checkout', [
            'products' => Product::where('stock', '>', 0)->orderBy('name')->get(),
            'selectedProduct' => $selectedProduct,
        ]);
    }

    public function store(Request $request, InventoryTransactionService $service)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['required', 'integer', 'min:0', 'max:1000000'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'shipping_address' => ['required', 'string', 'max:2000'],
        ]);

        $items = [];
        foreach ($data['items'] as $productId => $quantity) {
            if ((int) $quantity > 0) {
                $items[] = ['product_id' => (int) $productId, 'quantity' => (int) $quantity];
            }
        }

        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'Pilih minimal satu produk untuk dipesan.']);
        }

        $validatedItems = Validator::make(['items' => $items], [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ])->validate();

        $sale = $service->createSale(
            $validatedItems,
            $request->user()->id,
            [
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
            ],
        );

        return redirect()->route('shop.orders.invoice', $sale)->with('success', 'Pesanan berhasil dibuat. Simpan faktur sebagai bukti pesanan.');
    }

    public function index(Request $request)
    {
        $orders = Sale::query()
            ->where('user_id', $request->user()->id)
            ->withCount('items')
            ->latest('sold_at')
            ->paginate(10);

        return view('shop.orders', ['orders' => $orders]);
    }

    public function invoice(Request $request, Sale $sale)
    {
        abort_unless($sale->user_id === $request->user()->id, 404);

        return view('shop.invoice', [
            'order' => $sale->load(['user', 'items.product']),
        ]);
    }
}
