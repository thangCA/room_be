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


Route::group([
    'middleware' => ['api', 'cors'],
    'prefix' => '/'
],
    function ($router){
        Route::post('/authentication', [AuthController::class, 'login'])->name('authentication');
        Route::post('/registration', [AuthController::class, 'register'])->name('registration');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::get('/account/manage/info/load', [AuthController::class, 'userProfile']);
        Route::post('/account/manage/info/edit', [AuthController::class, 'updateProfile']);
        Route::post('/account/manage/password/change', [AuthController::class, 'change_password']);
        Route::post('/account/manage/avatar/update', [AuthController::class, 'change_avatar']);
        Route::post('/authentication/password/forgot', [AuthController::class, 'send_email_change_password']);
        Route::post('/authentication/password/reset', [AuthController::class, 'reset_password']);

        //================================================================================================//
        Route::post('/store/create', [AuthController::class, 'create_store']);
        Route::get('/store/manage/info/load', [AuthController::class, 'load_store']);
        Route::post('/store/manage/info/edit', [AuthController::class, 'update_store']);
        Route::post('/store/manage/logo/update', [AuthController::class, 'update_store_logo']);

        //================================================================================================//

        Route::post('/product/detail/post', [AuthController::class, 'create_product']);
        Route::get('/product/detail/load', [AuthController::class, 'load_product']);
        Route::post('/product/manage/detail/edit', [AuthController::class, 'update_product']); //
        Route:post("/product/manage/detail/add", [AuthController::class, 'add_product_item']);


        // Route get connect with param message = checking
        // url = http://localhost:5000/api/?message=checking
        Route::get('/', function (Request $request) {
            $message = $request->query('message');
            if ($message == 'checking') {



                return response()->json([
                    'connection' => 'success',
                    'message' => 'Connection success'
                ]);
            }
        });
    }
);


