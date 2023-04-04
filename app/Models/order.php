<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class order extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'name',
        'account_id',
        'product_id',
        'quantity',
        'time',
        'payment',
        'state',
        'phone',
        'prices'
    ];

    protected $casts = [
        'prices' => 'array'
    ];
    public function product()
    {
        return $this->belongsTo('App\Models\product');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
