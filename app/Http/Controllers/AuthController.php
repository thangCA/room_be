<?php

namespace App\Http\Controllers;
use App\Models\Token;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Str;
use mysql_xdevapi\Exception;
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
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);

    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'gender' => 'required|integer',
            'phone_number' => 'required|string|between:2,100',
            'date_of_bird' => 'required|date',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $phone_number = (new \App\Common\common)->validatePhone($request->phone_number);
        if(!$phone_number) {
            return response()->json(['error' => 'Phone number is not valid'], 401);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        return response()->json([
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
        $refresh_token = Cookie::get('refresh_token');
        $user_id = Cookie::get('user_id');

        $token = Token::where('access_token', $access_token)->where('user_id', $user_id)->first();
        if($token) {
            $token->delete();
            try {

                Cookie::queue(Cookie::forget('access_token'));
                Cookie::queue(Cookie::forget('refresh_token'));
                Cookie::queue(Cookie::forget('user_id'));

                return response()->json([
                    'message' => 'User successfully signed out'
                ]);
            }
            catch(Exception $e) {
                return response()->json([
                    'message' => 'User not found'
                ]);
            }

        }else {
            return response()->json([
                'message' => 'User not found'
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

    protected function createNewToken($token){
        $access_token = $token;
        $refresh_token = hash('sha256', Str::random(80));

        setcookie('refresh_token', $refresh_token, time() + 86400, "/"); // 86400 = 1 day
        setcookie('access_token', $access_token, time() + 1800, "/"); // 1800 = 30 minutes
        setcookie('user_id', auth()->user()->id, time() + 86400, "/"); // 86400 = 1 day

        $token_create = Token::create([
            'user_id' => auth()->user()->id,
            'name' => 'token',
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_in_access_token' => now()->addMinutes(30),
            'expires_in_refresh_token' => now()->addDay(1), // 1 day
        ]);
        return response()->json([
            'access_token' => $token_create->access_token,
            'refresh_token' => $token_create->refresh_token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
        ]);
    }


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
