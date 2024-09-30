<?php

use App\Http\Controllers\API\Admin\AuthController;
use App\Http\Controllers\API\Admin\CategoryController;
use App\Http\Controllers\API\Admin\SubcategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::group(['prefix' => 'category'], function () {
        Route::post('/', [CategoryController::class, 'create']);
        Route::get('/', [CategoryController::class, 'getCategory']);
        Route::get('/non-sort', [CategoryController::class, 'getCategoryNonSort']);
        Route::get('/{id}', [CategoryController::class, 'getSingleCategory']);
        Route::patch('/{id}', [CategoryController::class, 'editCategory']);
        Route::patch('/', [CategoryController::class, 'massUpdateCategory']);
        Route::delete('/{id}', [CategoryController::class, 'deleteCategory']);
        Route::patch('/restore/{id}', [CategoryController::class, 'restoreCategory']);
        Route::delete('/destroy/{id}', [CategoryController::class, 'destroyCategory']);
        Route::get('/trash/data', [CategoryController::class, 'getTrashCategory']);
    });

    Route::group(['prefix' => 'subcategory'], function () {
        Route::get('/', [SubcategoryController::class, 'getSubcategories']);
        Route::get('/non-sort', [SubcategoryController::class, 'getSubcategoriesNonSort']);
        Route::post('/', [SubcategoryController::class, 'createSubcategory']);
        Route::get('/{id}', [SubcategoryController::class, 'getSubcategory']);
        Route::patch('/{id}', [SubcategoryController::class, 'updateSubcategory']);
        Route::patch('/', [SubcategoryController::class, 'massUpdateSubcategory']);
        Route::delete('/{id}', [SubcategoryController::class, 'deleteSubcategory']);
        Route::get('/trash/data', [SubcategoryController::class, 'getTrashSubcategories']);
    });
});
Route::post('/login', [AuthController::class, 'login']);