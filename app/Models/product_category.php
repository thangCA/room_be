<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class product_category extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'product_id',
        'category_id'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\product');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\category');
    }

}
