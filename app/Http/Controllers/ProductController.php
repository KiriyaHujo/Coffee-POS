<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private function getManagerPin(): string
    {
        return (string) env('POS_MANAGER_PIN', '1234');
    }

    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
        ]);

        if ($request->pin === $this->getManagerPin()) {
            return response()->json([
                'success' => true,
                'message' => 'PIN Manager valid',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'PIN Manager salah!',
        ], 403);
    }

    public function index()
    {
        $products = Product::latest()->get();
        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'category' => 'required|in:minuman,makanan',
            'stock' => 'required|integer|min:0',
            'image_file' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:3072',
            'image_url' => 'nullable|string|url|max:500',
        ]);

        if ($request->pin !== $this->getManagerPin()) {
            return response()->json(['message' => 'PIN Manager salah!'], 403);
        }

        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $imagePath = '/storage/' . $path;
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->image_url;
        } else {
            // Default placeholder image based on category
            $imagePath = $request->category === 'minuman'
                ? 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&q=80&w=400'
                : 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&q=80&w=400';
        }

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'category' => $request->category,
            'stock' => $request->stock,
            'image' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan!',
            'product' => $product,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pin' => 'required|string',
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'category' => 'required|in:minuman,makanan',
            'stock' => 'required|integer|min:0',
            'image_file' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:3072',
            'image_url' => 'nullable|string|url|max:500',
        ]);

        if ($request->pin !== $this->getManagerPin()) {
            return response()->json(['message' => 'PIN Manager salah!'], 403);
        }

        $product = Product::findOrFail($id);

        $data = [
            'name' => $request->name,
            'price' => $request->price,
            'category' => $request->category,
            'stock' => $request->stock,
        ];

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $data['image'] = '/storage/' . $path;
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->image_url;
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil diperbarui!',
            'product' => $product,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $request->validate([
            'pin' => 'required|string',
        ]);

        if ($request->pin !== $this->getManagerPin()) {
            return response()->json(['message' => 'PIN Manager salah!'], 403);
        }

        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil dihapus!',
        ]);
    }
}
