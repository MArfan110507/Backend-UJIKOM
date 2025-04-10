<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JualAkun extends Model
{
    use HasFactory;

    protected $fillable = [
        'game',
        'image',
        'images',
        'stock',
        'server',
        'title',
        'price',
        'discount',
        'level',
        'features'
    ];

    protected $casts = [
        'images' => 'array',
        'features' => 'array',
    ];

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

}
