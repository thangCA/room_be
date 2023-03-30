<?php

namespace App\Common;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class common
{

    public function call_function_auto ($function_name, $name, $product_id) {
        if ($function_name == 'category') {

            foreach ($name as $key => $value) {
                $category = DB::table('categories')->where('name', $value)->first();
                if (!$category) {
                    $cate = DB::table('categories')->insert([
                        'name' => $value,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    DB::table('product_categories')->insert([
                        'product_id' => $name['product_id'],
                        'category_id' => $cate,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }else{
                    $cate_product = DB::table('product_categories')->where('product_id', $name['product_id'])->where('category_id', $category->id)->first();
                    if (!$cate_product) {
                        DB::table('product_categories')->insert([
                            'product_id' => $name['product_id'],
                            'category_id' => $category->id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }
        }
        elseif ( $function_name == 'option'){
            foreach ($name as $key => $value){
                $op_product = DB::table('product_options')->where('product_id', $product_id)->where('name', $value)->first();
                if (!$op_product) {
                    DB::table('product_options')->insert([
                        'product_id' => $name['product_id'],
                        'name' => $value,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
        elseif ( $function_name == 'location'){
            $lo_product = DB::table('product_locations')->where('product_id', $product_id)->first();
            if (!$lo_product) {
                DB::table('product_locations')->update([
                    'product_id' => $name['product_id'],
                    'country' => $name[0],
                    'city' => $name[1],
                    'address' => $name[2],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        elseif($function_name == 'file'){
            $fi_product = DB::table('product_files')->where('product_id', $product_id)->first();
            foreach ($name as $key => $value){
                if (!$fi_product) {
                    DB::table('product_files')->insert([
                        'product_id' => $name['product_id'],
                        'type' => $value['type'],
                        'url' => $value['url'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        return true;
    }

    public function delete_function_auto($function_name, $name, $product_id){
        if ($function_name == 'category'){
            $category = DB::table('product_category')->where('product_id', $product_id);
            DB::table('product_category')->where('product_id', $product_id)->where('category_id', $category[$name]->id)->delete();
        }
        elseif ($function_name == 'option'){
            $op = DB::table('product_options')->where('product_id', $product_id);
            DB::table('product_options')->where('product_id', $product_id)->where('name', $op[$name]->name)->delete();
        }

    }

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

