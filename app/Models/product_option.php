<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class product_option extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'product_id',
        'name'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\product');
    }


}
