<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellAccount extends Model
{
    protected $table = 'sellaccounts';

    protected $fillable = [
        'admin_id',
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

    protected $appends = ['admin_name'];

    public function getAdminNameAttribute()
    {
        return $this->admin ? $this->admin->name : null;
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

}
