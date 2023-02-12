<?php

use App\Http\Controllers\AuthController;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use mysql_xdevapi\Exception;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(
    [
        'middleware' => 'api',
        'prefix' => 'auth'
    ],
    function ($router) {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/logout',
            function (Request $request) {
                $token = Cookie::get('access_token');
                $refresh_token = Cookie::get('refresh_token');
                $user_id = Cookie::get('user_id');

                $token = Token::where('access_token', $token)->where('refresh_token', $refresh_token)->where('user_id', $user_id)->first();
                if ($token) {
                    $token->delete();
                    try {

                        Cookie::queue(Cookie::forget('access_token'));
                        Cookie::queue(Cookie::forget('refresh_token'));
                        Cookie::queue(Cookie::forget('user_id'));

                        return response()->json([
                            'message' => 'User successfully signed out'
                        ]);
                    } catch (Exception $e) {
                        return response()->json([
                            'message' => 'User not found'
                        ]);
                    }

                } else {
                    return response()->json([
                        'message' => 'User not found'
                    ]);
                }
            })->name('logout');
    }
);
