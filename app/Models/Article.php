<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'game',
        'status',
        'image_url',
    ];

    /**
     * Get the user who authored the article.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}