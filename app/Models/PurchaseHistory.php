<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseHistory extends Model
{
    protected $fillable = ['user_id', 'sellaccounts_id', 'transaction_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sellAccount()
    {
        return $this->belongsTo(SellAccount::class, 'sellaccounts_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}

