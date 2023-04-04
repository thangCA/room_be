<?php

namespace App\Http\Controllers;
use App\Mail\MyEmail;
use App\Mail\ThangDuc;
use App\Models\store;
use App\Models\Token;
use DateTime;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use mysql_xdevapi\Exception;
use Redis;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;
use Illuminate\Support\Facades\Cookie;
use App\Common\common;
use function PHPUnit\Framework\isEmpty;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
//    public function __construct() {
//        $this->middleware('auth:api', ['except' => ['login', 'register']]);
//    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function call_function_auto ($function_name, $name, $product_id) {

        if ($function_name == 'category') {

            foreach ($name as $key => $value) {
                $category = DB::table('category')->where('name', $value)->first();
                if (!$category) {
                    $cate = DB::table('category')->insert([
                        'name' => $value,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    DB::table('product_category')->insert([
                        'product_id' => $product_id,
                        'category_id' => $cate,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }else{
                    $cate_product = DB::table('product_category')->where('product_id', $product_id)->where('category_id', $category->id)->first();
                    if (!$cate_product) {
                        DB::table('product_category')->insert([
                            'product_id' => $product_id,
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
                $op_product = DB::table('product_option')->where('product_id', $product_id)->where('name', $value)->first();
                if (!$op_product) {
                    DB::table('product_option')->insert([
                        'product_id' => $product_id,
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
    public function login(Request $request)

    {
        $user = User::where('accountEmail', $request->accountEmail)->first();
        if ($user == null) {
            return response()->json([
                'authentication' => "handle authentication: wrong email or password",
            ], 200);
        }
        if (password_verify($request->accountPassword, $user->accountPassword)) {
            $store_id = DB::table('store')->where('account_id', $user->id)->first();
            if ($store_id != null) {
                $store_id = $store_id->id;
            } else {
                $store_id = '';
            }
            $token = JWTAuth::fromUser($user);
            $resp = [
                'message' => 'handle authentication: success',
                'accountId' => $user->id,
                'storeId' => $store_id,
            ];
            $expires = time() + 60 * 60 * 24;
            $access_token = $this->set_cookie($token, $user->id);
            //set cookie
            $cookie = cookie('access_token', $access_token, $expires, '/');
            $cookie1 = cookie('user_id', $user->id, $expires, '/');



            return response()->json([
                "authentication" => $resp
            ])->withHeaders(
                [
                    'access_token' => $access_token,
                    'user_id' => $user->id,
                ]
            )->withCookie($cookie)->withCookie($cookie1
            );
        } else {
            return response()->json([
                'authentication' => "handle authentication: wrong email or password",
            ], 200);
        }

    }

    /**
     * @throws \RedisException
     */
    public function set_cookie($token, $user_id)
    {
        $access_token = $token;
        $expires = time() + 60 * 60 * 24;
//        setcookie('access_token', $access_token, $expires, '/');
//        setcookie('user_id', $user_id, $expires, '/');
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $data = [
            'access_token' => $access_token,
            'user_id' => $user_id,
            'expires' => $expires,
        ];
        $redis->set($user_id, json_encode($data), $expires);
        $redis->close();
        return $access_token;
    }

    public function delete_cookie($user_id)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null) {
            print_r("miss cookie");
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {

            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $redis->del($user_id);
            $redis->close();
            $cookie = cookie('access_token', $access_token, -1);
            $cookie1 = cookie('user_id', $user_id, -1);


            //delete cookie
            return response()->json([
                'logout' => 'handle logout: success',
            ])->withCookie($cookie)->withCookie($cookie1);
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $accountPassword_confirmation = $request->accountPassword;
        if ($request->accountPassword != $accountPassword_confirmation) {
            return response()->json([
                'register' => 'handle register: invalid credentials',
                'errors' => 'password not match'
            ], 200);
        }
        $user_check = User::where('accountEmail', $request->accountEmail)->first();
        if ($user_check != null) {
            return response()->json([
                'register' => 'handle register: email is existed',
                'errors' => 'email already exists'
            ], 200);
        }
        if ($request->accountPhone != null) {
            $user_check = User::where('accountPhone', $request->accountPhone)->first();
            if ($user_check != null) {
                return response()->json([
                    'register' => 'handle register: phone is existed',
                    'errors' => 'phone already exists'
                ], 200);
            }
        }

        $user = DB::table('users')->insert(
            [
                'accountName' => $request->accountName,
                'accountEmail' => $request->accountEmail,
                'accountPassword' => Hash::make($request->accountPassword),
                'accountPhone' => $request->accountPhone,
            ]
        );
        return response()->json([
            'register' => 'handle register: success',
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function logout(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {

            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $redis->del($user_id);
            $redis->close();

            //delete cookie
            return response()->json([
                'logout' => 'handle logout: success',
            ]);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            $redis->close();
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $user_query = $request->query('account');
                    $user = User::where('id', $user_query)->first();
                    if ($user) {
                        $result = [
                            '_id' => $user->id,
                            'avatar' => $user->avatar ? $user->avatar : '',
                            'cart' => DB::table('cart')->where('account_id', $user->id)->get() ? DB::table('cart')->where('account_id', $user->id)->get() : [],
                            'email' => $user->accountEmail,
                            'name' => $user->accountName,
                            'order' => DB::table('order')->where('account_id', $user->id)->get() ? DB::table('order')->where('account_id', $user->id)->get() : [],
                            'phone' => $user->accountPhone,
                        ];
                        $infor =[
                            "message" => "load account manage info: success",
                            "result" => $result,
                        ];
                        return response()->json([
                            "info" => $infor,
                        ]);
                    } else {
                        return response()->json([
                            'user' => 'not found',
                        ], 400);
                    }
                }
            }
        }
    }

    public function updateProfile(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            $redis->close();
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $user_query = $request->query('account');
                    $user = User::where('id', $user_query)->first();
                    if ($user) {
                        $update = [
                             $request->email,
                            $request->name,
                            $request->phone,
                        ];
                        $result = [
                            'accountEmail' => $update[0],
                            'accountName' => $update[1],
                            'accountPhone' => $update[2]
                        ];

                        foreach ($update as $key => $value) {
                            if ($value == null) {
                                unset($update[$key]);
                            }else{
                                foreach ($result as $key => $value) {
                                    if ($value == null) {
                                        unset($result[$key]);
                                    }else{
                                        $result[$key] = $value;

                                    }
                                }

                            }
                        }
                        DB::table('users')->where('id', $user_query)->update($result);
                        DB::table('users')->where('id', $user_query)->update([
                            'updated_at' => now(),
                        ]);
                        return response()->json([
                            'account' => 'edit account info: success',
                        ]);
                    } else {
                        return response()->json([
                            'account' => 'edit account info: account is not existed',
                        ], 400);
                    }
                }

            }
        }
    }

    public function change_password(Request $request)
    {


        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            $redis->close();
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $user_query = $request->query('account');
                    $user = User::where('id', $user_query)->first();
                    if ($user) {
                        $old_password = $request->old_password;
                        $new_password = $request->new_password;
                        if (Hash::check($old_password, $user->accountPassword)) {
                            $user->accountPassword = Hash::make($new_password);
                            $user->save();
                            return response()->json([
                                'account' => 'change password: success',
                            ]);
                        } else {
                            return response()->json([
                                'account' => 'change password: old password is not correct',
                            ], 400);
                        }
                    } else {
                        return response()->json([
                            'account' => 'change password: account is not existed',
                        ], 400);
                    }
                }
            }
        }
    }

    public function change_avatar(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            $redis->close();
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $user_query = $request->query('account');
                    $user = User::where('id', $user_query)->first();
                    if ($user) {
                        $user->avatar = $request->avatar;
                        $user->save();
                        return response()->json([
                            'account' => 'change avatar: success',
                        ]);
                    } else {
                        return response()->json([
                            'account' => 'change avatar: account is not existed',
                        ], 400);
                    }
                }
            }
        }
    }

    public function send_email_change_password(Request $request)
    {
        $email = $request->email;
        $user = User::query()->where('accountEmail', $email)->first();
        if ($user) {
            $message = rand(100000, 999999);
            $redis = new Redis();

            $code = "code: " . strval($message);
            $redis->connect('127.0.0.1', 6379);
            $redis->set($email, $message, time() + 60 * 5);
            $redis->close();
            Mail::to($email)->send(new MyEmail($code));
            return response()->json([
                'authentication' => 'forgot authentication password: success',
            ]);
        } else {
            return response()->json([
                'authentication' => 'forgot authentication password: account is not existed',
            ], 400);

        }
    }

    public function reset_password(Request $request)
    {
        $email = $request->email;
        $code = $request->code;
        $password = $request->password;
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $data = $redis->get($email);
        $redis->close();
        if ($data == null) {
            return response()->json([
                'authentication' => 'reset password: code is not existed',
            ], 400);
        } else {
            if ($data == $code) {
                $user = User::query()->where('accountEmail', $email)->first();
                $user->accountPassword = Hash::make($password);
                $user->save();
                $redis = new Redis();
                $redis->connect('127.0.0.1', 6379);
                $redis->del($email);
                $redis->close();
                return response()->json([
                    'authentication' => 'reset authentication password: success',
                ]);
            } else {
                return response()->json([
                    'authentication' => 'reset authentication password: verify code is wrong',
                ], 400);
            }
        }
    }

    public function create_store(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            $redis->close();

            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $user_query = $request->query('account');
                    $user = User::where('id', $user_query)->first();
                    if ($user) {
                        if (DB::table('store')->where('email', $request->storeEmail)->exists()) {
                            return response()->json([
                                'store' => 'create store: email is existed',
                            ], 400);
                        } else {
                            DB::table('store')->insert([
                                'account_id' => $user->id,
                                'name' => $request->storeName,
                                'address' => $request->storeAddress,
                                'phone' => $request->storePhone ?? "https://www.demo.com",
                                'email' => $request->storeEmail,
                                'logo' => $request->storeLogo,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            return response()->json([
                                'account' => 'create store: success',
                            ], 201);
                        }
                    } else {
                        return response()->json([
                            'store' => 'create store: account is not existed',
                        ], 400);
                    }
                }
            }
        }
    }

    public function load_store(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            $redis->close();
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $store_query = $request->query('store');
                    $store = DB::table('store')->where('id', $store_query)->first();
                    $product = DB::table('product')->where('store_id', $store_query)->get();
                    $product_list = [];
                    foreach ($product as $item) {
                        array_push($product_list, $item->id);
                    }
                    if ($store) {
                        $resp = [
                            '_id' => $store->id,
                            'name' => $store->name,
                            'address' => $store->address,
                            'phone' => $store->phone ? $store->phone : '',
                            'email' => $store->email,
                            'logo' => $store->logo,
                            'product' => $product_list,
                        ];
                        $result = [
                            'message' => 'load store manage info: success',
                            'result' => $resp,
                        ];
                        return response()->json([
                            'info' => $result,
                        ]);
                    } else {
                        return response()->json([
                            'info' => 'load store manage info: store is not existed',
                        ], 400);
                    }
                }
            }
        }
    }

    public function update_store(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            $redis->close();
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $store_query = $request->query('store');
                    $store = DB::table('store')->where('id', $store_query)->first();
                    if ($store) {
                        $update = [
                            $request->name, $request->address, $request->phone, $request->email,
                        ];
                        $result = [
                            'name' => $update[0],
                            'address' => $update[1],
                            'phone' => $update[2],
                            'email' => $update[3],
                        ];
                        foreach ($update as $key => $value) {
                            if ($value == null) {
                                unset($update[$key]);
                            }else{
                                foreach ($result as $keys => $values) {
                                    if ($value == null) {
                                        unset($result[$keys]);
                                    }else{
                                        $result[$keys] = $values;

                                    }
                                }

                            }
                        }

                        DB::table('store')->where('id', $store_query)->update(
                            $result

                        );
                        DB::table('store')->where('id', $store_query)->update([
                            'updated_at' => now(),
                        ]);
                        return response()->json([
                            'info' => 'edit store info: success',
                        ]);
                    } else {
                        return response()->json([
                            'info' => 'update store manage info: store is not existed',
                        ], 400);
                    }
                }
            }
        }
    }

     public function update_store_logo(Request $request){
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            $redis->close();
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $store_query = $request->query('store');
                    $store = DB::table('store')->where('id', $store_query)->first();
                    if ($store) {
                        $update = [
                            $request->logo,
                        ];
                        $result = [
                            'logo' => $update[0],
                        ];
                        foreach ($update as $key => $value) {
                            if ($value == null) {
                                unset($update[$key]);
                            } else {

                                foreach ($result as $key => $value) {
                                    if ($value == null) {
                                        unset($result[$key]);
                                    } else {
                                        $result[$key] = $value;

                                    }
                                }

                            }
                        }

                        DB::table('store')->where('id', $store_query)->update(
                            $result
                        );
                        DB::table('store')->where('id', $store_query)->update([
                            'updated_at' => now(),
                        ]);
                        return response()->json([
                            'info' => 'edit store logo: success',
                        ]);
                    } else {
                        return response()->json([
                            'info' => 'update store logo: store is not existed',
                        ], 400);
                    }
                }
            }
        }

     }

     public function create_product(Request $request){
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            $redis->close();

            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $store_query = $request->query('store');
                    $store = DB::table('store')->where('id', $store_query)->first();
                    if ($store) {
                        //                        {
//                          "postTime": "2023-3-30 - 9:48:51",
//                          "productName": "Ao Khoac",
//                          "productCategory": [ "Fashion" ],
//                          "productFile": [
//                            {
//                                "type": "image",
//                              "url": "https://firebasestorage.googleapis.com/v0/b/vshop-4a2d5.appspot.com/o/store%2F642237c8020331e197cf9091%2Fproduct%2Fdate%2F2023-3-30%20-%209%3A48%3A51%2FAo%20Khoac%2Ffile%2Fz4155763466675_d3ef575c83937478ef04a6bf72c8826b.jpg?alt=media&token=d1b309dc-9efb-4b5e-a4e3-865510d537f4"
//                            }
//                          ],
//                          "productOption": [ "XX" ],
//                          "productPrice": "20",
//                          "productQuantity": "10",
//                          "productDescription": "Ao dep",
//                          "productAddress": [ "VIETNAM", "Ho Chi Minh", "Tan Binh" ]
//                        }

                         $product_request = [
                            'productName' => $request->productName,
                            'productCategory' => $request->productCategory,
                            'productFile' => $request->productFile,
                            'productOption' => $request->productOption,
                            'productPrice' => $request->productPrice,
                            'productQuantity' => $request->productQuantity,
                            'productDescription' => $request->productDescription,
                            'productAddress' => $request->productAddress,

                         ];

                         //save db product
                        $product = DB::table('product')->insertGetId(
                            [
                                'store_id' => $store_query,
                                'name' => $product_request['productName'],
                                'price' => floatval($product_request['productPrice']),
                                'quantity' => $product_request['productQuantity'],
                                'description' => $product_request['productDescription'],
                                'created_at' => now(),
                            ]);
                        //save db product_file
                        foreach ($product_request['productFile'] as $key => $value) {
                            DB::table('product_file')->insert(
                                [
                                    'product_id' => $product,
                                    'type' => $value['type'],
                                    'url' => $value['url'],
                                    'created_at' => now(),
                                ]);
                        }


                        //save db category and product_category
                        foreach ($product_request['productCategory'] as $key => $value) {
                            $category = DB::table('category')->where('name', $value)->first();
                            if ($category) {
                                $category_id = $category->id;
                            } else {
                                $category_id = DB::table('category')->insertGetId(
                                    [
                                        'name' => $value,
                                        'created_at' => now(),
                                    ]);
                            }
                            DB::table('product_category')->insert(
                                [
                                    'product_id' => $product,
                                    'category_id' => $category_id,
                                    'created_at' => now(),
                                ]);
                        }

                        //save db product_option
                        foreach ($product_request['productOption'] as $key => $value) {
                            DB::table('product_option')->insert(
                                [
                                    'product_id' => $product,
                                    'name' => $value,
                                    'created_at' => now(),
                                ]);
                        }

                        //save db product_address
                        $country = $product_request['productAddress'][0];
                        $city = $product_request['productAddress'][1];
                        $district = $product_request['productAddress'][2];

                        DB::table('product_location')->insert(
                            [
                                'product_id' => $product,
                                'country' => $country,
                                'city' => $city,
                                'address' => $district,
                                'created_at' => now(),
                            ]);
                        return response()->json([
                            'product' => 'post product detail: success',
                        ], 200);




                    } else {
                        return response()->json([
                            'info' => 'create product: store is not existed',
                        ], 400);
                    }
                }
            }
        }
    }

    public function load_product(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $product_query = $request->query('product');
                    $product = DB::table('product')->where('id', $product_query)->first();
                    if ($product) {

                        $product_category = DB::table('product_category')->where('product_id', $product_query)->get();
                        $product_option = DB::table('product_option')->where('product_id', $product_query)->get();
                        $product_file = DB::table('product_file')->where('product_id', $product_query)->get();
                        $product_location = DB::table('product_location')->where('product_id', $product_query)->first();
                        $comment = DB::table('product_comment')->where('product_id', $product_query)->get();
                        $store = DB::table('store')->where('id', $product->store_id)->first();
                        $product_from_store = DB::table('product')->where('store_id', $product->store_id)->get();
                        $comment_array = [];
                        $product_category_array = [];
                        $product_option_array = [];
                        foreach ($product_category as $value) {
                            $category = DB::table('category')->where('id', $value->category_id)->first();
                            array_push($product_category_array, $category->name);
                        }
                        foreach ($product_option as $value) {
                           $product_option_array = [
                                $value->name,
                            ];
                        }
                        if ($product_file){
                            foreach ($product_file as $value) {
                                $product_file_array[] = [
                                    'type' => $value->type,
                                    'url' => url($value->url),
                                    '_id' => $value->id,
                                ];
                            }
                        }
                        if ($comment -> isEmpty()){
                            $comment_array = [];
                        }else{
                            foreach ($comment as $value) {
                                $user = DB::table('user')->where('id', $value->user_id)->first();
                                if($user == $store->user_id){
                                    $seller = true;
                                }else{
                                    $seller = false;
                                }
                                $comment_array[] = [
                                    'account'=> [
                                        '_id' => $user->id,
                                        'name' => $user->name,
                                        'avatar' => $user->avatar,
                                    ],
                                    'comment'=>[
                                        '_id' => $user->id,
                                        'seller' =>$seller,
                                        'content' => $value->content,

                                    ]
                                ];
                            }

                        }
                        $store_resp = [
                            '_id' => $product->store_id,
                            'name' => $store->name,
                            'email' => $store->email,
                            'phone' => $store->phone,
                            'logo' => $store->logo,
                            'address' => $store->address,
                        ];
                        foreach ($product_from_store as $value) {
                            $store_resp['product'] = [
                                $value->id,
                            ];
                        }

                        $product_location = [
                           $product_location->country,
                            $product_location->city,
                           $product_location->address,
                        ];

                        $product_array = [
                            '_id' => $product->id,
                            'time' => $product->created_at,
                            'name' => $product->name,
                            'price' => intval($product->price),
                            'quantity' => $product->quantity,
                            'description' => $product->description,
                            'category' => $product_category_array,
                            'option' => $product_option_array,
                            'discount' => $product->discount ? $product->discount : 0,
                            'file' => $product_file_array,
                            'address' => $product_location,
                        ];
                        $rs = [
                            'message' => 'load product detail: success',
                            'detail' => $product_array,
                            'comment' => $comment_array,
                            'store' => $store_resp,
                        ];
                        return response()->json([
                            'product' => $rs,
                        ], 200);
                    } else {
                        return response()->json([
                            'info' => 'load product: product is not existed',
                        ], 400);
                    }

                } else {
                    return response()->json([
                        'protect' => 'miss',
                    ], 400);
                }
            }
        }
    }

    public function update_product(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $product_request = $request->all();
                    $product_id = $request->query('product');
                    if(count($product_request['category']) >3 ){
                        return response()->json([
                            'info' => 'update product: category is not existed',
                        ], 400);
                    }
                    $product = DB::table('product')->where('id', $product_id)->first();
                    if ($product) {
                        foreach ($product_request['category'] as $value) {
                            $category = DB::table('category')->where('name', $value)->first();
                            if ($category == null) {
                                $cate_id = DB::table('category')->insertGetId(
                                    ['name' => $value]
                                );

                                DB::table('product_category')->insert(
                                    ['product_id' => $product_id, 'category_id' => $cate_id]
                                );
                            } else {
                                $cate_id = $category->id;
                                DB::table('product_category')->insert(
                                    ['product_id' => $product_id, 'category_id' => $cate_id]
                                );
                            }
                        }

                        foreach ($product_request['file'] as $k_o => $v_o) {
                            if ($k_o == '_id') {
                                continue;
                            } else {
                                $file = DB::table('product_file')->where('product_id', $product_id)->first();
                                if ($file == null) {
                                    DB::table('product_option')->update(
                                        ['product_id' => $product_id, 'type' => $v_o['type'], 'url' => $v_o['url']]);
                                } else {
                                    DB::table('product_file')->where('product_id', $product_id)->insert(
                                        ['product_id' => $product_id, 'type' => $v_o['type'], 'url' => $v_o['url']]);
                                }
                            }
                        }
                        return response()->json([
                            'info' => 'update product: success',
                        ], 200);
                    } else {
                        return response()->json([
                            'info' => 'update product: product is not existed',
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'protect' => 'miss',
                    ], 400);

                }
            }
        }
    }

    public function  add_product_item(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $product = $request->query('product');
                    $field = $request->query('field');

                    $product = DB::table('product')->where('id', $product)->first();
                    $productData = $request->productData;
                    if($product){
                        $call = $this->call_function_auto($field, $productData, $product->id);
                        if($call){
                            return response()->json([
                                'info' => 'add product item: success',
                            ], 200);
                        }else{
                            return response()->json([
                                'info' => 'add product item: fail',
                            ], 400);
                        }
                    }
                }
            }
        }
    }

    public function  delete_product_item(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $product = $request->query('product');
                    $field = $request->query('field');
                    $where = $request->query('where');
                    $this->delete_function_auto($field, intval($where), $product);
                    return response()->json([
                        'info' => 'delete product item: success',
                    ], 200);
                }
            }
        }
    }

    public function delete_product (Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $product = $request->query('product');
                    $store = $request->query('store');

                    $product = DB::table('product')->where('id', $product)->where('store_id', $store)->first();
                    if($product){
                        $product_category = DB::table('product_category')->where('product_id', $product->id)->get();
                        $product_option = DB::table('product_option')->where('product_id', $product->id)->get();
                        $product_file = DB::table('product_file')->where('product_id', $product->id)->get();
                        $product_location = DB::table('product_location')->where('product_id', $product->id)->first();
                        if($product_category){
                            foreach ($product_category as $value){
                                DB::table('product_category')->where('id', $value->id)->delete();
                            }
                        }
                        if($product_option){
                            foreach ($product_option as $value){
                                DB::table('product_option')->where('id', $value->id)->delete();
                            }
                        }
                        if($product_file){
                            foreach ($product_file as $value){
                                DB::table('product_file')->where('id', $value->id)->delete();
                            }
                        }
                        if($product_location){
                            DB::table('product_location')->where('id', $product_location->id)->delete();
                        }
                        DB::table('product')->where('id', $product->id)->delete();
                        return response()->json([
                            'info' => 'delete product: success',
                        ], 200);
                    }
                }
            }
        }
    }

    public function search_product(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $search = $request->searchInput;
                    $product = DB::table('product')->where('name', 'like', '%' . $search . '%')->get();
                    $rs = [];
                    foreach ($product as $item) {
                        $r = [
                            '_id' => $item->id,
                            'name' => $item->name,
                            'price' => $item->price,
                            'description' => $item->description,
                        ];
                       array_push($rs,$r);
                    }

                    return response()->json([
                        'info' => 'search product: success',
                        'data' => $rs,
                    ], 200);
                }
            }
        }
    }

    public function load_list_category (Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs =[];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $category_name = $request->query('category');
                    $category = DB::table('category')->where('name', $category_name)->first();
                    $product_cate_id = DB::table('product_category')->where('category_id', $category->id)->get();
                    foreach ($product_cate_id as $item) {
                        $product = DB::table('product')->where('id', $item->product_id)->first();
                        $file = DB::table('product_file')->where('product_id', $product->id)->get();
                        $location = DB::table('product_location')->where('product_id', $product->id)->first();
                        $rs_file =[];
                        if($file){
                            foreach ($file as $cate_item) {
                                $r = [
                                    '_id' => $cate_item->id,
                                    'type' => $cate_item->type,
                                    'url' => $cate_item->url,
                                ];
                                array_push($rs_file,$r);
                            }
                        }else{
                            $rs_file = null;
                        }
                        if ($location){
                            $addr = [
                                'country' => $location->country,
                                'city' => $location->city,
                                'address' => $location->address,
                            ];
                        }else{
                            $addr = null;
                        }
                        $r = [
                            '_id' => $product->id,
                            'time' => $product->created_at,
                            'name' => $product->name,
                            'price' => intval($product->price),
                            'quantity' => intval($product->quantity),
                            'description' => $product->description,
                            'file' => $rs_file,
                            'address' => $addr,
                        ];
                        array_push($rs,$r);
                    }
                    $product = [
                        'message' => "success",
                        "result" => $rs,
                    ];

                    return response()->json([
                        'product' => $product,
                    ], 200);
                }
            }
        }
    }

    public function load_name_product(Request $request){
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs =[];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $product_name = $request->productName;
                    $product = DB::table('product')->where('name', $product_name)->first();
                    $file = DB::table('product_file')->where('product_id', $product->id)->get();
                    $location = DB::table('product_location')->where('id', $product->id)->first();
                    $rs_file =[];
                    if($file){
                        foreach ($file as $item) {
                            $r = [
                                'file_id' => $item->id,
                                'file_name' => $item->file_name,
                                'file_url' => $item->file_url,
                            ];
                            array_push($rs_file,$r);
                        }
                        }else{
                        $rs_file = null;
                    }
                    if ($location){
                        $addr = [
                            'country' => $location->country,
                            'city' => $location->city,
                            'address' => $location->address,
                        ];
                    }else{
                        $addr = null;
                    }

                    $rs_product = [
                        '_id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'description' => $product->description,
                        'file' => $rs_file,
                        'address' => $addr,
                        'quantity' => $product->quantity,
                        'discount' => $product->discount,
                    ];

                    $rs = [
                        "message" => "success",
                        "result" => $rs_product,
                    ];

                    return response()->json([
                        'product' => $rs,
                    ], 200);

                }
            }
        }
    }

    public function comment_product(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs = [];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $account_id = $request->query('account');
                    $product_id = $request->query('product');

                    if ($account_id == null or $product_id == null) {
                        return response()->json([
                            'product' => 'post product comment: account is not existed',
                        ], 400);
                    } else if ($account_id == $user_id) {
                        $comment = $request->comment;
                        $product = DB::table('product')->where('id', $product_id)->first();
                        if ($product == null) {
                            return response()->json([
                                'product' => 'post product comment: product is not existed',
                            ], 400);
                        }
                        DB::table('comment')->insert([
                            'account_id' => $account_id,
                            'product_id' => $product_id,
                            'comment' => $comment,
                        ]);

                        return response()->json([
                            "product" => "post product comment: success",
                        ], 200);

                    } else {
                        return response()->json([
                            'protect' => 'miss',
                        ], 400);
                    }

                }
            }
        }
    }

    public function load_store_product(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs = [];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $store = $request->query('store');
                    $store_ = DB::table('store')->where('id', $store)->first();
                    if ($store_ == null) {
                        return response()->json([
                            "message"=> "load selling manage product list: store is not existed"
                        ], 400);
                    }
                    $product = DB::table('product')->where('store_id', $store)->get();

                    $rs_product = [];
                    $rs_order = [];


                    if($product){
                        foreach ($product as $item){
                            $file = DB::table('product_file')->where('product_id', $item->id)->get();
                            $location = DB::table('product_location')->where('product_id', $item->id)->first();
                            $catefory_product = DB::table('product_category')->where('product_id', $item->id)->get();
                            $order = DB::table('order')->where('product_id', $item->id)->get();
                            $conment = DB::table('product_comment')->where('product_id', $item->id)->get();
                            $option = DB::table('product_option')->where('product_id', $item->id)->get();
                            $order_lo = [];
                            $rs_file =[];
                            $addr = [];
                            $rs_conment = [];
                            $rs_catefory = [];
                            $rs_option = [];
                            if($file){
                                foreach ($file as $fi) {
                                    $r = [
                                        '_id' => $fi->id,
                                        'type' => $fi->type,
                                        'url' => $fi->url,
                                    ];
                                    array_push($rs_file,$r);
                                }
                            }else{
                                $rs_file = null;
                            }

                            if ($location){
                                $addr_ = [
                                   $location->country,
                                    $location->city,
                                    $location->address,
                                ];
                            }else{
                                $addr_ = null;
                            }

                            if($catefory_product){
                                foreach ($catefory_product as $cate){
                                    $catefory = DB::table('category')->where('id', $cate->category_id)->first();
                                    $rs_catefory_ = [
                                        $catefory->name,
                                    ];
                                    array_push($rs_catefory,$rs_catefory_);
                                }
                            }else{
                                $rs_catefory = null;
                            }

                            if ($conment){
                                foreach ($conment as $co){
                                    $account = DB::table('users')->where('id', $co->account_id)->first();
                                    $rs_conment_ = [
                                        'account_id' => $account->id,
                                        'account_name' => $account->name,
                                        'account_avatar' => $account->avatar,
                                        'comment' => $co->comment,
                                    ];
                                    array_push($rs_conment,$rs_conment_);
                                }
                            }
                            else{
                                $rs_conment = null;
                            }

                            if ($order){
                                foreach ($order as $od){
                                    $or_lo = DB::table('order_location')->where('order_id', $od->id)->first();
                                    $order_lo_ = [
                                         $or_lo->country,
                                         $or_lo->city,
                                         $or_lo->address,
                                    ];

                                    array_push($order_lo,$order_lo_);
                                    $price = json_decode($od->prices, true);
                                    $od_lo = DB::table('order_location')->where('order_id', $od->id)->first();
                                    $order_item_ = [
                                        '_id' => $od->id,
                                        'time' => $od->created_at,
                                        'product' =>$od->product_id,
                                        'name' => $od->name,
                                        'phone' => $od->phone,
                                        'address' =>[
                                            $od_lo->country,
                                            $od_lo->city,
                                            $od_lo->address,
                                        ],
                                        'option' => $od->option,
                                        'quantity' => $od->quantity,
                                        'price' => [
                                            $price[0],
                                            $price[1],
                                        ],
                                        'payment' => $od->payment,
                                        'state' => $od->state,
                                    ];

                                    array_push($rs_order,$order_item_);

                                }
                            }else{
                                return response()->json([
                                    "product"=> "load selling manage product list: empty list"
                                ], 400);
                            }

                            if($option){
                                foreach ($option as $op){
                                    $option_item = DB::table('product_option')->where('id', $op->id)->first();
                                    $option_item_ = [
                                        $option_item->name,
                                    ];
                                    array_push($rs_option,$option_item_);
                                }
                            }else{
                                $rs_option = null;
                            }

                            $rs_product_ = [
                                '_id' => $item->id,
                                'time' => $item->created_at,
                                'name' => $item->name,
                                'price' => $item->price,
                                'category' => $rs_catefory,
                                'description' => $item->description,
                                'comment' => $rs_conment,
                                'file' => $rs_file,
                                'address' => $addr_,
                                'quantity' => $item->quantity,
                                'discount' => $item->discount,
                                'option' => $rs_option,
                            ];


                            $rs_ = [
                                'product' => $rs_product_,
                                'order' => $rs_order,
                            ];

                            array_push($rs,$rs_);

                        }
                    }
                    else{
                        $rs = null;
                    }

                    $rs_ = [
                        "message" => "load selling manage product list: success",
                        "result" => $rs,
                    ];

                    return response()->json([
                        "product" => $rs_,
                    ], 200);

                }
            }
        }
    }

    public function load_product_at_month(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs_product = [];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $time = $request->time;

                    $time_ = explode("-", $time);
                    $month = $time_[1];
                    $year = $time_[0];

                    $product = DB::table('product')
                        ->whereRaw('EXTRACT(month from product.created_at) = ?', [$month])
                        ->whereRaw('EXTRACT(year from product.created_at) = ?', [$year])
                        ->get();

                    if ($product) {
                        foreach ($product as $item) {
                            $rs_product = [];
                            $rs_catefory = [];
                            $address = [];
                            $file = [];
                            $option = [];
                            $comment = [];


                            $catefory_product = DB::table('product_category')->where('product_id', $item->id)->get();
                            $addr_product = DB::table('product_location')->where('product_id', $item->id)->first();
                            $file_product = DB::table('product_file')->where('product_id', $item->id)->get();
                            $option_product = DB::table('product_option')->where('product_id', $item->id)->get();
                            $comment_product = DB::table('product_comment')->where('product_id', $item->id)->get();






                            if($catefory_product){
                                foreach ($catefory_product as $cate){
                                    $catefory = DB::table('category')->where('id', $cate->category_id)->first();
                                    $rs_catefory_ = [
                                        $catefory->name,
                                    ];
                                    array_push($rs_catefory,$rs_catefory_);
                                }
                            }else{
                                $rs_catefory = null;
                            }

                            if ($addr_product){
                                $address_ = [
                                    $addr_product->country,
                                    $addr_product->city,
                                    $addr_product->address,
                                ];
                                array_push($address,$address_);
                            }else{
                                $address = null;
                            }

                            if($file_product){
                                foreach ($file_product as $file_){
                                    $file__ = [
                                        '_id' => $file_->id,
                                        'type' => $file_->type,
                                        'url' => $file_->url,
                                    ];
                                    array_push($file,$file__);
                                }
                            }else{
                                $file = null;
                            }

                            if ($option_product){
                                foreach ($option_product as $option_){
                                    $option__ = [
                                        $option_->name,
                                    ];
                                    array_push($option,$option__);
                                }
                            }else{
                                $option = null;
                            }

                            if ($comment_product){
                                foreach ($comment_product as $comment_){
                                    $comment__ = [
                                        'comment_id' => $comment_->id,
                                        'comment_content' => $comment_->content,
                                    ];
                                    array_push($comment,$comment__);
                                }
                            }else{
                                $comment = null;
                            }

                            $rs_product_ = [
                                '_id' => $item->id,
                                'time' => $item->created_at,
                                'name' => $item->name,
                                'price' => $item->price,
                                'category' => $rs_catefory,
                                'option' => $option,
                                'description' => $item->description,
                                'comment' => $comment,
                                'file' => $file,
                                'address' => $address,
                                'quantity' => $item->quantity,
                                'discount' => $item->discount,
                            ];

                            array_push($rs_product,$rs_product_);
                        }
                    }
                    else{
                        $rs_product = null;
                    }

                    $rs = [
                        'message' => "success",
                        'result' => $rs_product,
                    ];

                    return response()->json([
                        "product" => $rs,
                    ], 200);
                }
                else{
                    return response()->json([
                        'protect' => 'miss',
                    ], 400);
                }
            }
        }
    }

    public function load_discount(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs_product = [];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $date_rq = $request->time;

                    $date_rq_ = explode("-", $date_rq);
                    $month = $date_rq_[1];
                    $year = $date_rq_[0];
                    $rs = [];

                    $product = DB::table('product')
                        ->whereRaw('EXTRACT(month from product.created_at) = ?', [$month])
                        ->whereRaw('EXTRACT(year from product.created_at) = ?', [$year])
                        ->get();


                    if ($product) {
                        foreach ($product as $item) {
                            $rs_product = [];
                            $rs_catefory = [];
                            $address = [];
                            $file = [];
                            $option = [];

                            if ($item->discount != null) {
                                $catefory_product = DB::table('product_category')->where('product_id', $item->id)->get();
                                $addr_product = DB::table('product_location')->where('product_id', $item->id)->first();
                                $file_product = DB::table('product_file')->where('product_id', $item->id)->get();
                                $option_product = DB::table('product_option')->where('product_id', $item->id)->get();

                                if ($catefory_product) {
                                    foreach ($catefory_product as $cate) {
                                        $catefory = DB::table('category')->where('id', $cate->category_id)->first();
                                        $rs_catefory_ = [
                                            $catefory->name,
                                        ];
                                        array_push($rs_catefory, $rs_catefory_);
                                    }
                                } else {
                                    $rs_catefory = null;
                                }

                                if ($addr_product) {
                                    $address_ = [
                                        $addr_product->country,
                                        $addr_product->city,
                                        $addr_product->address,
                                    ];
                                    array_push($address, $address_);
                                } else {
                                    $address = null;
                                }

                                if ($file_product) {
                                    foreach ($file_product as $file_) {
                                        $file__ = [
                                            '_id' => $file_->id,
                                            'type' => $file_->type,
                                            'url' => $file_->url,
                                        ];
                                        array_push($file, $file__);
                                    }
                                } else {
                                    $file = null;
                                }

                                if ($option_product) {
                                    foreach ($option_product as $option_) {
                                        $option__ = [
                                            $option_->name,
                                        ];
                                        array_push($option, $option__);
                                    }
                                } else {
                                    $option = null;
                                }

                                $rs_product_ = [
                                    '_id' => $item->id,
                                    'time' => $item->created_at,
                                    'name' => $item->name,
                                    'price' => $item->price,
                                    'category' => $rs_catefory,
                                    'option' => $option,
                                    'description' => $item->description,
                                    'file' => $file,
                                    'address' => $address,
                                    'quantity' => $item->quantity,
                                    'discount' => $item->discount,
                                ];

                                array_push($rs_product, $rs_product_);


                            }

                            return response()->json([
                                "product" => $rs_product,
                            ], 200);


                        }
                    } else {
                        $rs_product = null;
                    }


                }
            }
        }
    }

    public function  add_to_cart(Request $request){
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs_product = [];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $account = $request->account;
                    $account_qr = DB::table('users')->where('id', $account)->first();
                    if($account_qr == null){
                        return response()->json([
                            'cart' => 'add cart item: account is not existed',
                        ], 400);
                    }

                    $product_id = $request->product;

                    $cart_qr = DB::table('cart')->where('account_id', $account)->where('product_id', $product_id)->first();
                    if($cart_qr == null){
                        $cart_id = DB::table('cart')->insertGetId(
                            ['account_id' => $account, 'product_id' => $product_id]
                        );

                        DB::table('cart_product')->insert(
                            ['cart_id' => $cart_id, 'product_id' => $product_id]
                        );

                        return response()->json([
                            'cart' => 'add cart item: success',
                        ], 200);
                    }else {
                        return response()->json([
                            'cart' => 'add cart item: existed item',
                        ], 200);
                    }

                }
                else {
                    return response()->json([
                        'protect' => 'miss',
                    ], 400);
                }
            }
        }
    }

    public function load_cart(Request $request){
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs_product = [];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $account = $request->account;


                    $cart_id = DB::table('cart')->where('account_id', $account)->get();
                    if ($cart_id) {
                        foreach ($cart_id as $item) {
                            $product = DB::table('product')->where('id', $item->product_id)->first();
                            if ($product) {

                                $rs_catefory = [];
                                $address = [];
                                $file = [];
                                $option = [];
                                $comment = [];





                                $catefory_product = DB::table('product_category')->where('product_id', $item->product_id)->get();
                                $addr_product = DB::table('product_location')->where('product_id', $item->product_id)->first();
                                $file_product = DB::table('product_file')->where('product_id', $item->product_id)->get();
                                $option_product = DB::table('product_option')->where('product_id', $item->product_id)->get();
                                $comment_product = DB::table('product_comment')->where('product_id', $item->product_id)->get();



                                if($catefory_product){
                                    foreach ($catefory_product as $cate){
                                        $catefory = DB::table('category')->where('id', $cate->category_id)->first();
                                        $rs_catefory_ = [
                                            $catefory->name,
                                        ];
                                        array_push($rs_catefory,$rs_catefory_);
                                    }
                                }else{
                                    $rs_catefory = null;
                                }

                                if ($addr_product){
                                    $address_ = [
                                        $addr_product->country,
                                        $addr_product->city,
                                        $addr_product->address,
                                    ];
                                    array_push($address,$address_);
                                }else{
                                    $address = null;
                                }

                                if($file_product){
                                    foreach ($file_product as $file_){
                                        $file__ = [
                                            '_id' => $file_->id,
                                            'type' => $file_->type,
                                            'url' => $file_->url,
                                        ];
                                        array_push($file,$file__);
                                    }
                                }else{
                                    $file = null;
                                }

                                if ($option_product){
                                    foreach ($option_product as $option_){
                                        $option__ = [
                                            $option_->name,
                                        ];
                                        array_push($option,$option__);
                                    }
                                }else{
                                    $option = null;
                                }

                                if ($comment_product){
                                    foreach ($comment_product as $comment_){
                                        $comment__ = [
                                            'comment_id' => $comment_->id,
                                            'comment_content' => $comment_->content,
                                        ];
                                        array_push($comment,$comment__);
                                    }
                                }else{
                                    $comment = null;
                                }










                                $rs_product_ = [
                                    '_id' => $product->id,
                                    'time' => $product->created_at,
                                    'name' => $product->name,
                                    'price' => intval($product->price),
                                    'quantity' => intval($product->quantity),
                                    'discount' => $product->discount ? intval($product->discount) : 0,
                                    'description' => $product->description,
                                    'file' => $file,
                                    'address' => $address,
                                    'category' => $rs_catefory,
                                    'comment' => $comment,
                                    'option' => $option,
                                ];
                                array_push($rs_product, $rs_product_);
                            }
                        }
                    } else {
                        return response()->json([
                            'cart' => "load cart item: empty list",
                        ], 200);
                    }

                    $rs = [
                        "message" => "load cart item: success",
                        "result" => $rs_product,
                    ];

                    return response()->json([
                        'cart' => $rs,
                    ], 200);

                }
                else{
                    return response()->json([
                        'protect' => 'miss',
                    ], 400);
                }
            }
        }
    }

    public function delete_cart(Request $request){
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs_product = [];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $account = $request->account;
                    $product_id = $request->product;

                    $cart_id = DB::table('cart')->where('account_id', $account)->get();

                    if($cart_id){
                        foreach ($cart_id as $item){
                            DB::table('cart_product')->where('id', $item->id)->delete();
                            DB::table('cart')->where('id', $item->id)->delete();
                        }

                        return response()->json([
                            'cart' => 'delete cart item: success',
                        ], 200);

                    }else{
                        return response()->json([
                            'cart' => 'delete cart item: account is not existed',
                        ], 200);
                    }
                }
                else{
                    return response()->json([
                        'protect' => 'miss',
                    ], 400);
                }
            }
        }
    }


    public function load_order(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs = [];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {
                    $account = $request->account;


                    $order_id = DB::table('order')->where('account_id', $account)->get();

                    if ($order_id == null) {
                        $rs_err = [
                            "message" => "load order list: empty",
                        ];

                        return response()->json([
                            'order' => $rs_err,
                        ], 200);
                    } else {
                        foreach ($order_id as $item) {
                            $item_order = DB::table('order')->where('id', $item->id)->first();
                            $location_oder = DB::table('order_location')->where('order_id', $item->id)->first();

                            $product = DB::table('product')->where('id', $item->product_id)->first();

                            $rs_catefory = [];
                            $address = [];
                            $file = [];
                            $option = [];
                            $comment = [];


                            $catefory_product = DB::table('product_category')->where('product_id', $product->id)->get();
                            $addr_product = DB::table('product_location')->where('product_id', $product->id)->first();
                            $file_product = DB::table('product_file')->where('product_id', $product->id)->get();
                            $option_product = DB::table('product_option')->where('product_id', $product->id)->get();
                            $comment_product = DB::table('product_comment')->where('product_id', $product->id)->get();


                            if ($catefory_product) {
                                foreach ($catefory_product as $cate) {
                                    $catefory = DB::table('category')->where('id', $cate->category_id)->first();
                                    $rs_catefory_ = [
                                        $catefory->name,
                                    ];
                                    array_push($rs_catefory, $rs_catefory_);
                                }
                            } else {
                                $rs_catefory = null;
                            }

                            if ($addr_product) {
                                $address_ = [
                                    $addr_product->country,
                                    $addr_product->city,
                                    $addr_product->address,
                                ];
                                array_push($address, $address_);
                            } else {
                                $address = null;
                            }

                            if ($file_product) {
                                foreach ($file_product as $file_) {
                                    $file__ = [
                                        '_id' => $file_->id,
                                        'type' => $file_->type,
                                        'url' => $file_->url,
                                    ];
                                    array_push($file, $file__);
                                }
                            } else {
                                $file = null;
                            }

                            if ($option_product) {
                                foreach ($option_product as $option_) {
                                    $option__ = [
                                        $option_->name,
                                    ];
                                    array_push($option, $option__);
                                }
                            } else {
                                $option = null;
                            }

                            if ($comment_product) {
                                foreach ($comment_product as $comment_) {
                                    $comment__ = [
                                        'comment_id' => $comment_->id,
                                        'comment_content' => $comment_->content,
                                    ];
                                    array_push($comment, $comment__);
                                }
                            } else {
                                $comment = null;
                            }

                            $rs_product_ = [
                                '_id' => $product->id,
                                'time' => $product->created_at,
                                'name' => $product->name,
                                'price' => $product->price,
                                'category' => $rs_catefory,
                                'option' => $option,
                                'description' => $product->description,
                                'comment' => $comment,
                                'file' => $file,
                                'address' => $address,
                                'quantity' => $product->quantity,
                                'discount' => $product->discount,
                            ];

                            $price = json_decode($item_order->prices, true);

                            $rs_order = [
                                '_id' => $item_order->id,
                                'time' => $item_order->created_at,
                                'product' => $item_order->product_id,
                                'name' => $item_order->name,
                                'phone' => $item_order->phone,
                                'address' =>$address = [
                                    $location_oder->country,
                                    $location_oder->city,
                                    $location_oder->address,
                                    ],
                                'option' => $item_order->option,
                                'quantity' => $item_order->quantity,
                                'price' => [
                                    $price[0],
                                    $price[1],
                                ],
                                'payment' => $item_order->payment,
                                'state' => $item_order->state,
                            ];

                            $rs_ = [
                                "order" => $rs_order,
                                "product" => $rs_product_,
                            ];

                            array_push($rs, $rs_);
                        }

                        $rs_success = [
                            "message" => "load order list: success",
                            "result" => $rs,
                        ];

                        return response()->json([
                            'order' => $rs_success,
                        ], 200);
                    }
                }
                else {
                    return response()->json([
                        'protect' => 'miss',
                    ], 400);
                }
            }
        }
    }


    public function create_order(Request $request){
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs= [];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {

                    $account = $request->account;
                    $product = $request->product;

                    $account_query = DB::table('users')->where('id', $account)->first();
                    $product_query = DB::table('product')->where('id', $product)->first();

                    if ($account_query == null ){
                        return response()->json([
                            "order" => 'post order detail: account is not existed',
                        ], 200);
                    }

                    if ($product_query == null ){
                        return response()->json([
                            "order" => 'post order detail: product is not existed',
                        ], 200);
                    }

                    $order_request  = $request->all();

                    $prices = json_encode($order_request['orderPrice']);


                    $order_create = [
                        "account_id" => $order_request['account'],
                        "product_id" => $order_request['product'],
                        "time" => $order_request['orderTime'],
                        "name" => $order_request['orderName'],
                        "phone" => $order_request['orderPhone'],
                        "payment" => $order_request['orderPaymentOption'],
                        "quantity" => $order_request['orderQuantity'],
                        "option" => $order_request['orderOption'],
                        "prices" => $prices ,
                        "state" => "waiting",
                        "created_at" => now(),
                    ];

                    $order_get_id = DB::table('order')->insertGetId($order_create);

                    $order_lo = [
                        "order_id" => $order_get_id,
                        "country" => $order_request['orderAddress'][0],
                        "city" => $order_request['orderAddress'][1],
                        "address" => $order_request['orderAddress'][2],
                    ];

                    DB::table('order_location')->insertGetId($order_lo);

                    return response()->json([
                        "order" => 'post order detail: success',
                    ], 200);

                }
                else{
                    return response()->json([
                        'protect' => "miss",
                    ], 200);
                }
            }
        }

    }

    public function confirm_order(Request $request){
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs = [];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {

                    $order_id = $request->order;

                    $product_order = DB::table('order')->where('id', $order_id)->first();
                    $product_id = $product_order->product_id;

                    $product = DB::table('product')->where('id', $product_id)->first();

                    $store_product = $product->store_id;

                    $account_product = DB::table('store')->where('id', $store_product)->first();
                    $account_product = $account_product->account_id;



                    if($account_product == $user_id){
                        $order = DB::table('order')->where('id', $order_id)->update(['state' => 'confirmed']);
                        return response()->json([
                            "order" => 'confirm order: success',
                        ], 200);
                    }
                    else{
                        return response()->json([
                            "order" => 'confirm order: order is not existed',
                        ], 200);
                    }

                }
                else{
                    return response()->json([
                        'protect' => "miss",
                    ], 200);
                }
            }
        }
    }

    public function paid_order(Request $request)
    {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $rs = [];

        if ($access_token == null and $user_id == null) {
            return response()->json([
                'protect' => 'miss',
            ], 400);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $data = $redis->get($user_id);
            if ($data == null) {
                return response()->json([
                    'protect' => 'miss',
                ], 400);
            } else {
                $data = json_decode($data, true);
                if ($data['access_token'] == $access_token) {

                    $order_id = $request->order;

                    $order = DB::table('order')->where('id', $order_id)->first();

                    if($order->state == 'confirmed'){
                        $order = DB::table('order')->where('id', $order_id)->update(['payment' => 'paid']);
                        return response()->json([
                            "order" => 'paid order: success',
                        ], 200);
                    }
                    else{
                        return response()->json([
                            "order" => 'paid order: order is not existed',
                        ], 200);
                    }

                }
                else{
                    return response()->json([
                        'protect' => "miss",
                    ], 200);
                }
            }
        }
    }



    public
    function check_time($access_token, $refesh_token)
    {
        $token = Token::where('access_token', $access_token)->where('refresh_token', $refesh_token)->first();
        if ($token) {
            $time = $token->expires_in_access_token;
            $time = strtotime($time);
            $now = time();
            if ($now > $time) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function changePassWord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $userId = auth()->user()->id;

        $user = User::where('id', $userId)->update(
            ['password' => bcrypt($request->new_password)]
        );

        return response()->json([
            'message' => 'User successfully changed password',
            'user' => $user,
        ], 201);
    }
}



