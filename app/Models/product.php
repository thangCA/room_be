<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class product extends Model
{
    use HasFactory, Notifiable;

protected $fillable = [
        'id',
        'store_id',
        'name',
        'price',
        'quantity',
        'description',
        'discount',
    ];

    public function store()
    {
        return $this->belongsTo('App\Models\store');
    }
}
