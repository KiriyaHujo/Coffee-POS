<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_name',
        'order_type',
        'table_number',
        'payment_method',
        'total_amount',
        'pay_amount',
        'change_amount',
        'payment_provider',
        'notes',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
