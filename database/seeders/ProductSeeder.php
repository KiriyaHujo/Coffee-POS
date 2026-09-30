<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Espresso Single',
                'price' => 18000,
                'category' => 'minuman',
                'image' => 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&q=80&w=400',
                'stock' => 50,
            ],
            [
                'name' => 'Caffè Latte',
                'price' => 28000,
                'category' => 'minuman',
                'image' => 'https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&q=80&w=400',
                'stock' => 40,
            ],
            [
                'name' => 'Cappuccino',
                'price' => 26000,
                'category' => 'minuman',
                'image' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&q=80&w=400',
                'stock' => 35,
            ],
            [
                'name' => 'Croissant Butter',
                'price' => 22000,
                'category' => 'makanan',
                'image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&q=80&w=400',
                'stock' => 20,
            ],
            [
                'name' => 'Chocolate Brownie',
                'price' => 25000,
                'category' => 'makanan',
                'image' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&q=80&w=400',
                'stock' => 15,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}