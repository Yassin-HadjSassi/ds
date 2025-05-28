<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\EmplacementController;
use App\Http\Controllers\FormeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderLinesController;
use App\Http\Controllers\StockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StocklineController;
use App\Http\Controllers\UserController;

// CORS handling middleware
Route::middleware(function ($request, $next) {
    $response = $next($request);
    // Add CORS headers
    $response->headers->set('Access-Control-Allow-Origin', 'https://ehk-4l9g16qj7-yassins-projects-ec722a08.vercel.app');
    $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
    $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Auth-Token, Authorization, Origin');
    
    return $response;
})->group(function () {

    Route::group([
        'middleware' => 'api',
        'prefix' => 'users'
    ], function ($router) {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refreshToken', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
    });
    
    Route::get('users/verify-email', [AuthController::class, 'verifyEmail'])->name('verify.email');
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
    
    Route::middleware('api')->group(function () {
        Route::resource('articles', ArticleController::class);
    });

    Route::group(['middleware' => ['auth:api']], function () {
        Route::resource('categories', CategorieController::class);
        Route::resource('formes', FormeController::class);
        Route::get('/articles/categorie/{idcat}', [ArticleController::class,'showArticlesByCAT']);
        Route::get('/articles/forme/{idfor}', [ArticleController::class,'showArticlesByFOR']);
        Route::get('/articles/categorie/{idcat}/forme/{idfor}', [ArticleController::class,'showArticlesByCATAndFOR']);
        Route::get('/articlespaginate', [ArticleController::class,'articlesPaginate']);
        Route::get('/stocks/checkorder/{id}', [StockController::class,'checkOrder']);
        Route::post('/stocks/processorder', [StockController::class,'processOrder']);
        Route::get('/stocks/manque/{num}', [StockController::class,'manque']);
        Route::resource('stocks', StockController::class);
        Route::resource('stocklines', StocklineController::class);
        Route::resource('emplacements', EmplacementController::class);
        Route::resource('orders', OrderController::class);
        Route::resource('orderlines', OrderLinesController::class);
        Route::resource('users', UserController::class);
    });

});