<?php

use App\Http\Controllers\API\Admin\AuthController;
use App\Http\Controllers\API\Admin\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::group(['prefix' => 'category'], function() {
        Route::post('/', [CategoryController::class, 'create']);
    });
});

Route::post('/login', [AuthController::class, 'login']);