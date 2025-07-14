<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Carbon;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use Laravel\Socialite\Facades\Socialite;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum', 'verified');


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('email/verify', [AuthController::class, 'verifyEmail']);
    Route::post('email/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::post('{provider}/redirect', [SocialAuthController::class, 'redirect']);
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback']);
});


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);
});


Route::apiResource('products', ProductController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('product-categories', ProductCategoryController::class);

    Route::apiResource('products', ProductController::class)->only(['store', 'update', 'destroy']);;
    Route::post('products/{product}/images', [ProductController::class, 'uploadImages']);
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage']);
});