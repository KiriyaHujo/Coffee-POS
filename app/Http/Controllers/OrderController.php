<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'pay_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|in:cash,qris',
            'order_type' => 'nullable|in:dine_in,takeaway',
            'customer_name' => 'nullable|string|max:100',
            'table_number' => 'nullable|string|max:20',
            'payment_provider' => 'nullable|string',
            'notes' => 'nullable|string|max:255',
        ]);

        $paymentMethod = $request->input('payment_method', 'cash');
        $orderType = $request->input('order_type', 'dine_in');
        $customerName = $request->input('customer_name');
        $tableNumber = ($orderType === 'dine_in') ? $request->input('table_number') : null;
        $notes = $request->input('notes');

        $totalAmount = 0;
        foreach ($request->cart as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        $payAmount = (float) $request->pay_amount;
        if ($paymentMethod === 'qris') {
            $payAmount = $totalAmount;
            $changeAmount = 0;
        } else {
            if ($payAmount < $totalAmount) {
                return response()->json(['message' => 'Uang pembayaran kurang!'], 422);
            }
            $changeAmount = $payAmount - $totalAmount;
        }

        try {
            DB::beginTransaction();

            $order = Order::create([
                'invoice_number' => 'RC-' . time(),
                'customer_name' => $customerName,
                'order_type' => $orderType,
                'table_number' => $tableNumber,
                'payment_method' => $paymentMethod,
                'total_amount' => $totalAmount,
                'pay_amount' => $payAmount,
                'change_amount' => $changeAmount,
                'payment_provider' => $request->payment_provider,
                'notes' => $notes,
            ]);

            foreach ($request->cart as $item) {
                $product = Product::findOrFail($item['id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi!");
                }

                $product->decrement('stock', $item['quantity']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();

            $order->load('items.product');

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'invoice' => $order->invoice_number,
                'change' => $order->change_amount,
                'order' => $order,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function history()
    {
        $orders = Order::with('items.product')
            ->latest()
            ->take(30)
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }
}