<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'sellaccount_id', 'sender_id', 'receiver_id', 'message', 'status', 'type'
    ];
    

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function sellAccount()
    {
        return $this->belongsTo(SellAccount::class, 'sellaccount_id');
    }
}
