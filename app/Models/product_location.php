<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class product_location extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'product_id',
        'country',
        'city',
        'address'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\product');
    }
}
