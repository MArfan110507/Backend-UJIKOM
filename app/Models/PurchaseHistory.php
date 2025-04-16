<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sellaccount_id',
        'total_price',
        'purchase_date',
        'game_email',
        'game_password',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sellaccount()
    {
        return $this->belongsTo(SellAccount::class);
    }
}
