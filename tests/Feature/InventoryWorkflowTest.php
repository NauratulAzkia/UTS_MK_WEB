<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private Category $category;

    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->category = Category::create(['name' => 'Elektronik']);
        $this->supplier = Supplier::create(['name' => 'Pemasok Satu', 'email' => 'supplier@example.test']);
    }

    public function test_guest_cannot_open_dashboard_and_wrong_password_is_rejected(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->from(route('login'))->post(route('login.store'), [
            'email' => $this->admin->email, 'password' => 'incorrect',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_dashboard_shows_the_pet_store_summary_without_existing_transactions(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Stok rapi')
            ->assertSee('Aktivitas penjualan')
            ->assertSee('Belum ada penjualan minggu ini');
    }

    public function test_demo_seeder_populates_a_cat_food_catalog_with_retail_reference_prices(): void
    {
        $this->seed();

        $this->assertDatabaseCount('products', 12);
        $this->assertDatabaseHas('products', [
            'sku' => 'RC-FIT32-2KG',
            'name' => 'Royal Canin Fit 32 Adult 2 kg',
            'selling_price' => 269000,
        ]);
        $this->assertDatabaseHas('products', [
            'sku' => 'WHISKAS-POUCH-80G',
            'category_id' => Category::where('name', 'Makanan Basah')->value('id'),
        ]);
    }

    public function test_customer_can_register_and_open_their_order_history(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Pelanggan Baru',
            'email' => 'catlover@example.test',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ])->assertRedirect(route('shop.orders.index'))->assertSessionHasNoErrors();

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'catlover@example.test', 'role' => 'customer']);
        $this->get(route('shop.orders.index'))->assertOk()->assertSee('Riwayat pembelian');
    }

    public function test_admin_can_create_a_customer_account_from_user_management(): void
    {
        $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Pembeli Paw Pantry',
            'email' => 'buyer@example.test',
            'password' => 'secure-pass-123',
            'role' => 'customer',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => 'buyer@example.test', 'role' => 'customer']);
        $this->actingAs($this->admin)->get(route('users.index'))->assertOk()->assertSee('Pelanggan');
        $this->actingAs($this->admin)->get(route('users.edit', User::where('email', 'buyer@example.test')->firstOrFail()))
            ->assertOk()
            ->assertSee('value="customer"', false);
    }

    public function test_customer_can_place_an_order_and_print_their_invoice(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::create($this->productData('SKU-ONLINE', ['stock' => 6, 'selling_price' => 17500]));

        $this->get(route('shop.index'))->assertOk()->assertSee($product->name)->assertSee('Katalog produk');
        $this->actingAs($customer)->post(route('shop.checkout.store'), [
            'items' => [$product->id => 2],
            'customer_phone' => '081234567890',
            'shipping_address' => 'Jalan Kucing No. 7, Bandung',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 4]);
        $this->assertDatabaseHas('sales', [
            'user_id' => $customer->id,
            'total' => '35000.00',
            'customer_phone' => '081234567890',
            'shipping_address' => 'Jalan Kucing No. 7, Bandung',
        ]);
        $sale = Sale::firstOrFail();

        $this->get(route('shop.orders.index'))->assertOk()->assertSee($sale->sale_number)->assertSee('Lihat faktur');
        $this->get(route('shop.orders.invoice', $sale))->assertOk()->assertSee($sale->sale_number)->assertSee('Cetak faktur')->assertSee($product->name);
    }

    public function test_customers_cannot_view_another_customers_invoice_or_staff_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $anotherCustomer = User::factory()->create(['role' => 'customer']);
        $product = Product::create($this->productData('SKU-PRIVATE', ['stock' => 1]));
        $sale = app(InventoryTransactionService::class)->createSale(
            ['items' => [['product_id' => $product->id, 'quantity' => 1]]],
            $anotherCustomer->id,
        );

        $this->actingAs($customer)->get(route('shop.orders.invoice', $sale))->assertNotFound();
        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('sales.index'))->assertForbidden();
        $this->actingAs($this->staff)->get(route('shop.checkout'))->assertForbidden();
    }

    public function test_customer_checkout_rejects_empty_cart_and_overselling(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::create($this->productData('SKU-ONLINE-STOCK', ['stock' => 1]));
        $payload = [
            'customer_phone' => '081234567890',
            'shipping_address' => 'Jalan Kucing No. 7, Bandung',
        ];

        $this->actingAs($customer)->from(route('shop.checkout'))->post(route('shop.checkout.store'), $payload + [
            'items' => [$product->id => 0],
        ])->assertSessionHasErrors('items');
        $this->from(route('shop.checkout'))->post(route('shop.checkout.store'), $payload + [
            'items' => [$product->id => 2],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_admin_can_create_product_but_duplicate_sku_and_negative_price_are_rejected(): void
    {
        $payload = $this->productData('SKU-UNIK');
        $this->actingAs($this->admin)->post(route('products.store'), $payload)->assertRedirect(route('products.index'));
        $this->actingAs($this->admin)->from(route('products.create'))->post(route('products.store'), $payload)->assertSessionHasErrors('sku');
        $this->actingAs($this->admin)->from(route('products.create'))->post(route('products.store'), $this->productData('SKU-NEG', ['selling_price' => -10]))->assertSessionHasErrors('selling_price');
        $this->assertDatabaseCount('products', 1);
    }

    public function test_purchase_adds_stock_and_sale_uses_database_price_and_reduces_stock(): void
    {
        $product = Product::create($this->productData('SKU-WORK', ['stock' => 0]));

        $this->actingAs($this->staff)->post(route('purchases.store'), [
            'supplier_id' => $this->supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 8, 'unit_cost' => 5000]],
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
        $this->assertDatabaseHas('purchases', ['total' => '40000.00', 'status' => 'completed']);

        $this->actingAs($this->staff)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
        $this->assertDatabaseHas('sales', ['total' => '45000.00', 'status' => 'completed']);
        $this->assertDatabaseHas('sale_items', ['product_id' => $product->id, 'unit_price' => '15000.00', 'subtotal' => '45000.00']);
    }

    public function test_overselling_is_rejected_without_changing_stock_or_creating_sale(): void
    {
        $product = Product::create($this->productData('SKU-STOCK', ['stock' => 2]));
        $this->actingAs($this->staff)->from(route('sales.create'))->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 2]);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_failure_in_any_sale_item_leaves_all_stock_unchanged(): void
    {
        $available = Product::create($this->productData('SKU-AVAILABLE', ['stock' => 5]));
        $short = Product::create($this->productData('SKU-SHORT', ['stock' => 1]));

        $this->actingAs($this->staff)->post(route('sales.store'), [
            'items' => [
                ['product_id' => $available->id, 'quantity' => 2],
                ['product_id' => $short->id, 'quantity' => 2],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseHas('products', ['id' => $available->id, 'stock' => 5]);
        $this->assertDatabaseHas('products', ['id' => $short->id, 'stock' => 1]);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_purchase_and_sale_cancellation_reverse_stock_once_and_purchase_cancel_cannot_overdraw(): void
    {
        $product = Product::create($this->productData('SKU-CANCEL', ['stock' => 0]));
        $this->actingAs($this->staff)->post(route('purchases.store'), [
            'supplier_id' => $this->supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 4000]],
        ]);
        $purchase = Purchase::firstOrFail();
        $this->actingAs($this->staff)->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 2]]]);
        $sale = Sale::firstOrFail();

        $this->actingAs($this->admin)->post(route('purchases.cancel', $purchase))->assertSessionHasErrors('status');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
        $this->actingAs($this->admin)->post(route('sales.cancel', $sale))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
        $this->actingAs($this->admin)->post(route('sales.cancel', $sale))->assertSessionHasErrors('status');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
    }

    public function test_staff_cannot_access_admin_user_management_or_master_mutations(): void
    {
        $this->actingAs($this->staff)->get(route('users.index'))->assertForbidden();
        $this->actingAs($this->staff)->get(route('products.create'))->assertForbidden();
        $this->actingAs($this->staff)->get(route('categories.index'))->assertOk();
    }

    public function test_product_search_category_filter_and_pagination_work(): void
    {
        for ($i = 1; $i <= 13; $i++) {
            Product::create($this->productData(sprintf('SKU-%02d', $i), ['name' => 'Produk '.$i]));
        }
        Product::create($this->productData('FIND-ME', ['name' => 'Barang Khusus']));

        $this->actingAs($this->staff)->get(route('products.index', ['q' => 'FIND-ME']))->assertOk()->assertSee('Barang Khusus')->assertDontSee('Produk 1</');
        $this->actingAs($this->staff)->get(route('products.index', ['category_id' => $this->category->id]))->assertOk()->assertSee('pagination');
    }

    public function test_admin_can_cancel_completed_sale_and_view_report_filters(): void
    {
        $product = Product::create($this->productData('SKU-REPORT', ['stock' => 4]));
        $this->actingAs($this->staff)->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]]]);
        $sale = Sale::firstOrFail();

        $this->actingAs($this->staff)->get(route('reports.index', ['from' => today()->toDateString()]))->assertOk()->assertSee('Total penjualan');
        $this->actingAs($this->admin)->post(route('sales.cancel', $sale))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'status' => 'cancelled']);
    }

    private function productData(string $sku, array $overrides = []): array
    {
        return array_merge([
            'category_id' => $this->category->id,
            'sku' => $sku,
            'name' => 'Produk '.$sku,
            'purchase_price' => 5000,
            'selling_price' => 15000,
            'stock' => 0,
            'minimum_stock' => 2,
        ], $overrides);
    }
}
