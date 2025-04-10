<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAQChat extends Model
{
    use HasFactory;

    protected $fillable = ['faq_id', 'user_id', 'message'];

    public function faq()
    {
        return $this->belongsTo(FAQ::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
