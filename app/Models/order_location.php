<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class order_location extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'order_id',
        'country',
        'city',
        'address'
    ];

    public function order()
    {
        return $this->belongsTo('App\Models\order');
    }
}
