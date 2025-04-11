<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellAccount extends Model
{
    use HasFactory;

    protected $table = 'sellaccounts';

    protected $fillable = [
        'game',
        'images',
        'stock',
        'server',
        'title',
        'price',
        'discount',
        'level',
        'features',
        'game_email',
        'game_password',
    ];
}
