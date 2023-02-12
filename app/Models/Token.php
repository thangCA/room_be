<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Token extends Model
{

    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'access_token',
        'refresh_token',
        'expires_in_access_token',
        'expires_in_refresh_token',
    ];

    public static function findToken($token)
    {
        return static::where('access_token', $token)->first();
    }

    public static function delete_token($token)
    {
        return static::where('access_token', $token)->delete();
    }
}
