<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellAccount extends Model
{
    protected $table = 'sellaccounts';

    protected $fillable = [
        'game',
        'images',
        'stock',
        'game_server',
        'title',
        'price',
        'discount',
        'level',
        'features',
        'game_email',
        'game_password',
    ];

    protected $casts = [
        'images' => 'array',
        'features' => 'array',
    ];
}
