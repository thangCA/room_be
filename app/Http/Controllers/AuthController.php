<?php

namespace App\Http\Controllers;
use App\Mail\MyEmail;
use App\Mail\ThangDuc;
use App\Models\store;
use App\Models\Token;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use mysql_xdevapi\Exception;
use Redis;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;
use Illuminate\Support\Facades\Cookie;





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
//    public function login(Request $request){
//
//        $validator = Validator::make($request->all(), [
//            'accountEmail' => 'required|string|email',
//            'accountPassword' => 'required|string',
//            'remember_me' => 'boolean'
//        ]);
//        if($validator->fails()){
//            return response()->json($validator->errors()->toJson(), 400);
//        }
//        $credentials = request(['accountEmail', 'accountPassword']);
//        $check = User::where('accountEmail', $request->accountEmail)->first();
//        if(!$check){
//            return response()->json([
//                'error' => 'Email does not exist',
//            ], 401);
//        }
//        $password = $check->accountPassword;
//        if ($check->accountPassword != $request->accountPassword){
//            return response()->json([
//                'error' => 'Password is incorrect',
//            ], 401);
//        }
//        $token = Auth::attempt($credentials);
//        //why undefined variable $user_id ?
//        // answer: because you have to declare it first
//        $user = User::where('accountEmail', $request->accountEmail)->first();
//        $user_id = $user->id;
//        $access_token = $token;
//        $refresh_token = Str::random(60);
//        Token::create([
//            'user_id' => $user_id,
//            'access_token' => $access_token,
//            'refresh_token' => $refresh_token,
//        ]);
//        $cookie = cookie('access_token', $access_token, 60);
//        $cookie1 = cookie('refresh_token', $refresh_token, 60);
//        $cookie2 = cookie('user_id', $user_id, 60);
//        return response()->json([
//            'login' => 'handle login: success',
//            'access_token' => $access_token,
//            'refresh_token' => $refresh_token,
//            'user_id' => $user_id,
//        ])->withCookie($cookie)->withCookie($cookie1)->withCookie($cookie2);
//    }
    public function login(Request $request)

    {
        $user = User::where('accountEmail', $request->accountEmail)->first();
        if (password_verify($request->accountPassword, $user->accountPassword)) {
            $store_id = DB::table('store')->where('account_id', $user->id)->first();
            if ($store_id != null) {
                $store_id = $store_id->id;
            } else {
                $store_id = '';
            }
            $resp = [
                'message' => 'success',
                'accountId' => $user->accountEmail,
                'storeId' => $store_id,
            ];
            $token = JWTAuth::fromUser($user);

            $this->set_cookie($token, $user->id);
            return response()->json([
                "authentication" => $resp
            ]);
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
        setcookie('access_token', $access_token, $expires, '/');
        setcookie('user_id', $user_id, $expires, '/');
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $data = [
            'access_token' => $access_token,
            'user_id' => $user_id,
            'expires' => $expires,
        ];
        $redis->set($user_id, json_encode($data), $expires);
        $redis->close();
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
        $validator = Validator::make($request->all(), [
            'accountName' => 'required|string|between:2,100',
            'accountEmail' => 'required|string|email|max:100|unique:users',
            'accountPassword' => 'required|string|confirmed|min:6',
            'accountPhone' => 'required|string|between:10,11',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'register' => 'handle register: invalid credentials',
                'errors' => $validator->errors()->toJson()
            ], 400);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['accountPassword' => bcrypt($request->accountPassword)]
        ));
        return response()->json([
            'register' => 'handle register: success',
            'message' => 'User successfully registered',
            'user' => $user
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
                        return response()->json([
                            '_id' => $user->id,
                            'avatar' => $user->accountAvatar ? $user->accountAvatar : '',
                            'cart' => DB::table('cart')->where('account_id', $user->id)->get() ? DB::table('cart')->where('account_id', $user->id)->get() : [],
                            'email' => $user->accountEmail,
                            'name' => $user->accountName,
                            'order' => DB::table('order')->where('account_id', $user->id)->get() ? DB::table('order')->where('account_id', $user->id)->get() : [],
                            'phone' => $user->accountPhone,
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
                        $user->accountName = $request->accountName;
                        $user->accountPhone = $request->accountPhone;
                        $email = User::query()->where('accountEmail', $request->accountEmail)->first();
                        if ($email) {
                            return response()->json([
                                'account' => 'edit account info: email is existed',
                            ], 400);
                        } else {
                            $user->accountEmail = $request->accountEmail;
                            $user->save();
                            return response()->json([
                                'account' => 'edit account info: success',
                            ]);
                        }
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
                        if (password_verify($old_password, $user->accountPassword)) {
                            $user->accountPassword = bcrypt($new_password);
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
            print_r(strval($code));
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
                $user->accountPassword = bcrypt($password);
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
                                'phone' => $request->storePhone,
                                'email' => $request->storeEmail,
                                'logo' => $request->storeLogo,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            return response()->json([
                                'account' => 'create store: success',
                            ]);
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
                    if ($store) {
                        $resp = [
                            '_id' => $store->id,
                            'name' => $store->name,
                            'address' => $store->address,
                            'phone' => $store->phone ? $store->phone : '',
                            'email' => $store->email,
                            'logo' => $store->logo,
                        ];
                        return response()->json([
                            'message' => 'load store manage info: success',
                            'result' => $store,
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



