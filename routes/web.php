<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $products = Product::where('stock', '>', 0)->get();
    return view('index', compact('products'));
})->name('pos.index');

Route::post('/checkout', [OrderController::class, 'store'])->name('pos.checkout');
Route::get('/orders/history', [OrderController::class, 'history'])->name('pos.history');

Route::prefix('products')->group(function () {
    Route::post('/verify-pin', [ProductController::class, 'verifyPin'])->name('products.verify-pin');
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::post('/', [ProductController::class, 'store'])->name('products.store');
    Route::post('/{id}/update', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
});
