<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Order;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    public function test_pos_page_can_be_rendered(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Roast & Co.', false);
        $response->assertSee('Riwayat');
        $response->assertSee('Dine In');
        $response->assertSee('Take Away');
    }

    public function test_checkout_successful_cash_transaction_with_table(): void
    {
        $product = Product::first();
        $this->assertNotNull($product);

        $initialStock = $product->stock;
        $qty = 2;
        $total = $product->price * $qty;
        $payAmount = $total + 10000;

        $response = $this->postJson(route('pos.checkout'), [
            'cart' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'quantity' => $qty,
                ]
            ],
            'pay_amount' => $payAmount,
            'payment_method' => 'cash',
            'order_type' => 'dine_in',
            'customer_name' => 'Budi',
            'table_number' => 'Meja 03',
            'notes' => 'Gula pisah',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'change' => 10000,
            ]);

        $this->assertDatabaseHas('orders', [
            'total_amount' => $total,
            'pay_amount' => $payAmount,
            'change_amount' => 10000,
            'payment_method' => 'cash',
            'order_type' => 'dine_in',
            'customer_name' => 'Budi',
            'table_number' => 'Meja 03',
            'notes' => 'Gula pisah',
        ]);

        $product->refresh();
        $this->assertEquals($initialStock - $qty, $product->stock);
    }

    public function test_checkout_successful_qris_transaction_takeaway(): void
    {
        $product = Product::latest()->first();
        $this->assertNotNull($product);

        $initialStock = $product->stock;
        $qty = 1;
        $total = $product->price * $qty;

        $response = $this->postJson(route('pos.checkout'), [
            'cart' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'quantity' => $qty,
                ]
            ],
            'pay_amount' => $total,
            'payment_method' => 'qris',
            'order_type' => 'takeaway',
            'customer_name' => 'Siti',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'change' => 0,
            ]);

        $this->assertDatabaseHas('orders', [
            'total_amount' => $total,
            'pay_amount' => $total,
            'change_amount' => 0,
            'payment_method' => 'qris',
            'order_type' => 'takeaway',
            'customer_name' => 'Siti',
            'table_number' => null,
        ]);

        $product->refresh();
        $this->assertEquals($initialStock - $qty, $product->stock);
    }

    public function test_checkout_fails_when_payment_is_insufficient(): void
    {
        $product = Product::first();

        $response = $this->postJson(route('pos.checkout'), [
            'cart' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'quantity' => 1,
                ]
            ],
            'pay_amount' => 100, // less than price
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Uang pembayaran kurang!',
            ]);
    }

    public function test_orders_history_endpoint(): void
    {
        $product = Product::first();

        // Create 1 order first
        $this->postJson(route('pos.checkout'), [
            'cart' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'quantity' => 1,
                ]
            ],
            'pay_amount' => $product->price,
            'payment_method' => 'cash',
            'order_type' => 'dine_in',
        ]);

        $response = $this->getJson(route('pos.history'));
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data['orders']);
        $this->assertEquals($product->id, $data['orders'][0]['items'][0]['product_id']);
    }
}
