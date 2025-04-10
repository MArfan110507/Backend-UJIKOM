<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'items',
        'total_price',
        'status',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'is_refunded',
        'refund_reason',
    ];

    protected $casts = [
        'items' => 'array',
        'is_refunded' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Transaction.php
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

}

