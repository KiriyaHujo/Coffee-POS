<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_pin_with_correct_pin(): void
    {
        $response = $this->postJson(route('products.verify-pin'), [
            'pin' => '1234',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_verify_pin_fails_with_incorrect_pin(): void
    {
        $response = $this->postJson(route('products.verify-pin'), [
            'pin' => '9999',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_store_product_successfully(): void
    {
        $response = $this->postJson(route('products.store'), [
            'pin' => '1234',
            'name' => 'Caramel Macchiato',
            'price' => 32000,
            'category' => 'minuman',
            'stock' => 50,
            'image_url' => 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Caramel Macchiato',
            'price' => 32000,
            'category' => 'minuman',
            'stock' => 50,
        ]);
    }

    public function test_store_product_with_image_upload(): void
    {
        Storage::fake('public');

        // Minimal valid JPEG binary (1x1 pixel), no GD required
        $jpegBytes = base64_decode(
            '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8U' .
            'HRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgN' .
            'DRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy' .
            'MjL/wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAA' .
            'AAAAAAAAAAAAAAP/EABQBAQAAAAAAAAAAAAAAAAAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA' .
            '/9oADAMBAAIRAxEAPwCwABmX/9k='
        );

        $file = UploadedFile::fake()->createWithContent('coffee.jpg', $jpegBytes);

        $response = $this->post(route('products.store'), [
            'pin' => '1234',
            'name' => 'V60 Specialty Pour Over',
            'price' => 28000,
            'category' => 'minuman',
            'stock' => 30,
            'image_file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);

        $product = Product::where('name', 'V60 Specialty Pour Over')->first();
        $this->assertNotNull($product);
        $this->assertStringStartsWith('/storage/products/', $product->image);
    }

    public function test_update_product_successfully(): void
    {
        $product = Product::create([
            'name' => 'Cold Brew',
            'price' => 25000,
            'category' => 'minuman',
            'stock' => 20,
            'image' => 'https://example.com/image.jpg',
        ]);

        $response = $this->postJson(route('products.update', $product->id), [
            'pin' => '1234',
            'name' => 'Cold Brew Nitro',
            'price' => 30000,
            'category' => 'minuman',
            'stock' => 45,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Cold Brew Nitro',
            'price' => 30000,
            'stock' => 45,
        ]);
    }

    public function test_delete_product_successfully(): void
    {
        $product = Product::create([
            'name' => 'Seasonal Muffin',
            'price' => 20000,
            'category' => 'makanan',
            'stock' => 10,
            'image' => 'https://example.com/muffin.jpg',
        ]);

        $response = $this->deleteJson(route('products.destroy', $product->id), [
            'pin' => '1234',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
