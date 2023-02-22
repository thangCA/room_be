<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class cart_product extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'cart_id',
        'product_id'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\product');
    }

    public function cart()
    {
        return $this->belongsTo('App\Models\cart');
    }
}
