<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class store extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'account_id',
        'name',
        'phone',
        'address',
        'email',
        'logo'
    ];

    public function user($id)
    {

        return $this->belongsTo('App\Models\User');
    }

    public function findStore($id)
    {
        return static::where('account_id', $id)->first();
    }






}
