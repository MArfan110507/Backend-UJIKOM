<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'order_id',
        'payment_method',
        'status',
        'snap_token',
        'transaction_id',
        'transaction_time',
        'payment_type',
        'gross_amount',
    ];

    public function order() {
        return $this->belongsTo(Orders::class, 'order_id');
    }
}