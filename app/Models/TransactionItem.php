<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'jualakun_id',
        'quantity',
        'price',
    ];

    /**
     * Relasi ke transaksi induk.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Relasi ke akun yang dijual (jualakun).
     */
    public function akun()
    {
        return $this->belongsTo(SellAccount::class, 'sellaccount_id');
    }
}
