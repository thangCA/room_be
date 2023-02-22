<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class product_comment extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'product_id',
        'account_id',
        'comment'
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
