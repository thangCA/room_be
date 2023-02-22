<?php

namespace App\Common;

use App\Models\User;

class common
{
    public function check_phone_existed($phone) {
        $user = User::where('accountPhone', $phone)->first();
        if($user) {
            return false;
        }
        return true;
    }
    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function check_email_existed($email) {
        $user = User::where('accountEmail', $email)->first();
        if($user) {
            return false;
        }
        return true;
    }

}

