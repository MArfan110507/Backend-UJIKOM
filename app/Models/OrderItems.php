<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    protected $table = 'order_items'; // Add this line
    
    protected $fillable = [
        'order_id',
        'sellaccount_id',
        'price',
        'quantity',
        'subtotal',
    ];
    
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function sellaccount()
    {
        return $this->belongsTo(\App\Models\SellAccount::class);
    }

}
