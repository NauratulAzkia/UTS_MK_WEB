<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryTransactionService
{
    public function createPurchase(array $data, int $userId): Purchase
    {
        return DB::transaction(function () use ($data, $userId) {
            $products = $this->lockProducts($data['items']);
            $total = '0.00';
            $lines = [];

            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']];
                $cost = (string) $item['unit_cost'];
                $subtotal = bcmul($cost, (string) $item['quantity'], 2);
                $total = bcadd($total, $subtotal, 2);
                $this->ensureTotalFits($total);
                $lines[] = ['product' => $product, 'quantity' => $item['quantity'], 'unit_cost' => $cost, 'subtotal' => $subtotal];
            }

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $userId,
                'purchase_number' => $this->number('PUR'),
                'total' => $total,
                'status' => 'completed',
                'purchased_at' => now(),
            ]);

            foreach ($lines as $line) {
                $purchase->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_cost' => $line['unit_cost'],
                    'subtotal' => $line['subtotal'],
                ]);
                $line['product']->increment('stock', $line['quantity']);
                $line['product']->update(['purchase_price' => $line['unit_cost']]);
            }

            return $purchase;
        });
    }

    public function createSale(array $data, int $userId, ?array $customerDetails = null): Sale
    {
        return DB::transaction(function () use ($data, $userId, $customerDetails) {
            $products = $this->lockProducts($data['items']);
            $total = '0.00';
            $lines = [];

            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']];
                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak mencukupi (tersedia {$product->stock}).",
                    ]);
                }
                $price = (string) $product->selling_price;
                $subtotal = bcmul($price, (string) $item['quantity'], 2);
                $total = bcadd($total, $subtotal, 2);
                $this->ensureTotalFits($total);
                $lines[] = ['product' => $product, 'quantity' => $item['quantity'], 'unit_price' => $price, 'subtotal' => $subtotal];
            }

            $sale = Sale::create([
                'user_id' => $userId,
                'sale_number' => $this->number('SAL'),
                'total' => $total,
                'status' => 'completed',
                'sold_at' => now(),
                'customer_phone' => $customerDetails['customer_phone'] ?? null,
                'shipping_address' => $customerDetails['shipping_address'] ?? null,
            ]);

            foreach ($lines as $line) {
                $sale->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);
                $line['product']->decrement('stock', $line['quantity']);
            }

            return $sale;
        });
    }

    public function cancelPurchase(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $purchase = Purchase::query()->lockForUpdate()->findOrFail($purchase->id);
            if ($purchase->status !== 'completed') {
                throw ValidationException::withMessages(['status' => 'Transaksi ini sudah dibatalkan.']);
            }

            $items = $purchase->items()->get();
            $products = $this->lockProductIds($items->pluck('product_id')->all());
            foreach ($items as $item) {
                if ($products[$item->product_id]->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'status' => "Pembatalan ditolak: stok {$products[$item->product_id]->name} sudah terpakai.",
                    ]);
                }
            }
            foreach ($items as $item) {
                $products[$item->product_id]->decrement('stock', $item->quantity);
            }
            $purchase->update(['status' => 'cancelled']);
        });
    }

    public function cancelSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale = Sale::query()->lockForUpdate()->findOrFail($sale->id);
            if ($sale->status !== 'completed') {
                throw ValidationException::withMessages(['status' => 'Transaksi ini sudah dibatalkan.']);
            }
            $items = $sale->items()->get();
            $products = $this->lockProductIds($items->pluck('product_id')->all());
            foreach ($items as $item) {
                $products[$item->product_id]->increment('stock', $item->quantity);
            }
            $sale->update(['status' => 'cancelled']);
        });
    }

    private function lockProducts(array $items): array
    {
        return $this->lockProductIds(array_column($items, 'product_id'));
    }

    private function lockProductIds(array $ids): array
    {
        $products = Product::query()->whereIn('id', array_unique($ids))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        if ($products->count() !== count(array_unique($ids))) {
            throw ValidationException::withMessages(['items' => 'Satu atau lebih produk tidak ditemukan.']);
        }

        return $products->all();
    }

    private function number(string $prefix): string
    {
        return $prefix.'-'.now()->format('YmdHis').'-'.str()->upper(str()->random(6));
    }

    private function ensureTotalFits(string $total): void
    {
        if (bccomp($total, '999999999999.99', 2) > 0) {
            throw ValidationException::withMessages(['items' => 'Total transaksi melebihi batas maksimum.']);
        }
    }
}
