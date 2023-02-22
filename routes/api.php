<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('/')->group(
    function ($router) {
        Route::post('/authentication', [AuthController::class, 'login'])->name('authentication');
        Route::post('/registration', [AuthController::class, 'register'])->name('registration');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
        Route::post('/change-pass', [AuthController::class, 'changePassWord']);


        // Route get connect with param message = checking
        // url = http://localhost:5000/api/?message=checking
        $url = url('/') . '?' . http_build_query(['message' => 'checking']);
        Route::get('/', function (Request $request) use ($url) {
            $message = $request->message;
            if ($message == 'checking') {
                return response()->json([
                    'connection' => 'success',
                ]);
            } else {
                return response()->json([
                    'connection' => 'failed',
                ]);
            }
        })->name('check_connection');

    }
);


