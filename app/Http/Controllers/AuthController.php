<?php

namespace App\Http\Controllers;
use App\Models\Token;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
            $resp = [
                'message' => 'success',
                'accountId' => $user->accountEmail,
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
    public function set_cookie($token, $user_id){
        $access_token = $token;
        $expires = time() + 60 * 60 * 24 ;
        setcookie('access_token', $access_token, $expires, '/');
        setcookie('user_id', $user_id, $expires, '/');
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $data = [
            'access_token' => $access_token,
            'user_id' => $user_id,
            'expires' => $expires,
        ];
        $redis->set( $user_id, json_encode($data), $expires);
        $redis->close();
    }

    public function delete_cookie($user_id){
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null ){
            print_r("miss cookie");
            return response()->json([
                'protect' => 'miss',
            ], 400);
        }else{

            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $redis->del($user_id);
            $redis->close();
            $cookie = cookie('access_token', $access_token, -1);
            $cookie1 = cookie('user_id', $user_id, -1);


            //delete cookie
            return response()->json([
                'logout' => 'handle logout: success',
            ])->withCookie($cookie )->withCookie($cookie1);
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'accountName' => 'required|string|between:2,100',
            'accountEmail' => 'required|string|email|max:100|unique:users',
            'accountPassword' => 'required|string|confirmed|min:6',
            'accountPhone' => 'required|string|between:10,11',

        ]);
        if($validator->fails()){
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

    public function logout(Request $request) {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');

        if ($access_token == null ){
            return response()->json([
                'protect' => 'miss',
            ], 400);
        }else{

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
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        $access_token = Cookie::get('access_token');
        $user_id = Cookie::get('user_id');
        $token = Token::where('access_token', $access_token)->where('user_id', $user_id)->first();
        $time = $this->check_time($token->access_token, $token->refresh_token);
        if($time) {
            return response()->json(User::where('id', $user_id)->first());
        }else {
            $refresh_token = Cookie::get('refresh_token');
            $token = Token::where('refresh_token', $refresh_token)->first();
            $token->delete();

            Cookie::queue(Cookie::forget('access_token'));
            Cookie::queue(Cookie::forget('refresh_token'));
            Cookie::queue(Cookie::forget('user_id'));

            $this->createNewToken($token->refresh_token);
            return response()->json(User::where('id', $user_id)->first());
        }
    }

    public function check_time($access_token, $refesh_token) {
        $token = Token::where('access_token', $access_token)->where('refresh_token', $refesh_token)->first();
        if($token) {
            $time = $token->expires_in_access_token;
            $time = strtotime($time);
            $now = time();
            if($now > $time) {
                return false;
            }else {
                return true;
            }
        }else {
            return false;
        }
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassWord(Request $request) {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
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
