<?php

namespace App\Http\Controllers;
use App\Mail\MyEmail;
use App\Mail\ThangDuc;
use App\Models\store;
use App\Models\Token;
use DateTime;
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
    public function login(Request $request)

    {
        $user = User::where('accountEmail', $request->accountEmail)->first();
        if ($user == null) {
            return response()->json([
                'authentication' => "handle authentication: wrong email or password",
            ], 401);
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
            ], 401);
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
            ], 400);
        }
        $user_check = User::where('accountEmail', $request->accountEmail)->first();
        if ($user_check != null) {
            return response()->json([
                'register' => 'handle register: invalid credentials',
                'errors' => 'email already exists'
            ], 400);
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
                                foreach ($result as $key => $value) {
                                    if ($value == null) {
                                        unset($result[$key]);
                                    }else{
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
                            'info' => 'create product: success',
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
                        $comment_array = [];
                        $product_category_array = [];
                        $product_option_array = [];
                        $product_file_array = [];
                        foreach ($product_category as $value) {
                            $category = DB::table('category')->where('id', $value->category_id)->first();
                            array_push($product_category_array, $category);
                        }
                        foreach ($product_option as $value) {
                            array_push($product_option_array, $value);
                        }
                        foreach ($product_file as $value) {
                            array_push($product_file_array, $value);
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
                        ];


                        $product_array = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'quantity' => $product->quantity,
                            'description' => $product->description,
                            'category' => $product_category_array,
                            'option' => $product_option_array,
                            'file' => $product_file_array,
                            'location' => $product_location,
                        ];
                        $rs = [
                            'message' => 'load product: success',
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
                    if(count($product_request['prodct_category']) >3 ){
                        return response()->json([
                            'info' => 'update product: category is not existed',
                        ], 400);
                    }
                    $product = DB::table('product')->where('id', $product_request['id'])->first();
                    if ($product) {
                        $product_category = DB::table('product_category')->where('product_id', $product_request['id'])->get();
                        $product_option = DB::table('product_option')->where('product_id', $product_request['id'])->get();
                        $product_file = DB::table('product_file')->where('product_id', $product_request['id'])->get();
                        $product_location = DB::table('product_location')->where('product_id', $product_request['id'])->first();
                        $product_category_array = [];
                        $product_option_array = [];
                        $product_file_array = [];
                        foreach ($product_category as $value) {
                            $category = DB::table('category')->where('id', $value->category_id)->first();
                            array_push($product_category_array, $category);
                        }
                        foreach ($product_option as $value) {
                            array_push($product_option_array, $value);
                        }
                        foreach ($product_file as $value) {
                            array_push($product_file_array, $value);
                        }
                        $product_array = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'quantity' => $product->quantity,
                            'description' => $product->description,
                            'category' => $product_category_array,
                            'option' => $product_option_array,
                            'file' => $product_file_array,
                            'location' => $product_location,
                        ];
                        $rs = [
                            'message' => 'load product: success',
                            'detail' => $product_array,
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
                        $call = common::call_function_auto($field,$productData,$product->id);
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
                    delete_function_auto($field,intval($where),$product);
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
                        DB::table('product_category')->where('product_id', $product->id)->delete();
                        DB::table('product_option')->where('product_id', $product->id)->delete();
                        DB::table('product_file')->where('product_id', $product->id)->delete();
                        DB::table('product')->where('id', $product->id)->delete();
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
                        $r = [
                            '_id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'description' => $product->description,
                            'file' => $rs_file,
                            'location' => $addr,
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



