<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Carbon;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleListingController;
use App\Http\Controllers\VehicleFeatureController;
use App\Http\Controllers\VehicleRequestController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\UserOrderController;
use App\Http\Controllers\UserAccountController;
use App\Http\Controllers\ContactController;


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

    Route::post('google', [AuthController::class, 'googleAuth']);

    Route::post('{provider}/redirect', [SocialAuthController::class, 'redirect']);
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback']);
});


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);
});

Route::get('product-categories/{slug}', [ProductCategoryController::class, 'showBySlug']);
Route::apiResource('product-categories', ProductCategoryController::class)->only([
    'index'
]);

Route::apiResource('vehicle-features', VehicleFeatureController::class)->only([
    'index', 'show'
]);

Route::get('products/{slug}', [ProductController::class, 'showBySlug']);
Route::apiResource('products', ProductController::class)->only([
    'index'
]);
Route::get('categories/{slug}/products', [ProductController::class, 'byCategory']);

Route::get('vehicle-listings/{slug}', [VehicleListingController::class, 'showBySlug']);
Route::apiResource('vehicle-listings', VehicleListingController::class)->only([
    'index'
]);
Route::get('vehicle-listings/search', [VehicleListingController::class, 'search']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class)->only([
        'store', 'update', 'destroy'
    ]);
    Route::post('products/{product}/images', [ProductController::class, 'uploadImages']);
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage']);

    Route::apiResource('vehicle-listings', VehicleListingController::class)->only([
        'store', 'update', 'destroy'
    ]);
    Route::post('vehicle-listings/{vehicle}/images', [VehicleListingController::class, 'uploadImages']);
    Route::delete('vehicle-listings/{vehicle}/images/{image}', [VehicleListingController::class, 'deleteImage']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function(){
    Route::apiResource('product-categories', ProductCategoryController::class)->only([
        'store', 'update', 'destroy'
    ]);

    Route::apiResource('vehicle-features', VehicleFeatureController::class)->only([
        'store', 'update', 'destroy'
    ]);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/suspended', [UserController::class, 'suspended']);
    Route::get('/deleted', [UserController::class, 'deleted']);
    Route::get('/{user}', [UserController::class, 'show']);

    Route::patch('/{user}/suspend', [UserController::class, 'suspend']); 
    Route::patch('/{user}/unsuspend', [UserController::class, 'unsuspend']);

    Route::delete('/{user}', [UserController::class, 'destroy']);
    Route::patch('/{user}/restore', [UserController::class, 'restore']);
});

Route::apiResource('vehicle-requests', VehicleRequestController::class);

Route::get('/cart', [CartController::class, 'getCart']);
Route::post('/cart', [CartController::class, 'addToCart']);
Route::patch('/cart/update/{productId}', [CartController::class, 'updateQuantity']);
Route::delete('/cart/remove/{productId}', [CartController::class, 'removeFromCart']);

Route::prefix('payments')->group(function () {
    Route::post('/initialize', [PaymentController::class, 'initialize']);
    Route::post('/verify', [PaymentController::class, 'verify']);
});

Route::middleware('api')->group(function () {
    Route::post('/checkout', [OrderController::class, 'checkout'])->name('order.checkout');
});

Route::post('/payment/webhook/{provider}', [PaymentWebhookController::class, 'handle']);

Route::middleware(['auth:sanctum', 'check.token.expiration'])->prefix('seller')->group(function () {
    Route::post('/register', [SellerController::class, 'register']);
    Route::put('/seller/{id}', [SellerController::class, 'update']);
    Route::delete('/seller/{id}', [SellerController::class, 'destroy']);
    Route::get('/profile', [SellerController::class, 'profile']);

    // Orders
    Route::get('/orders', [UserOrderController::class, 'index']);
    Route::get('/orders/{order}', [UserOrderController::class, 'show']);
    Route::put('/orders/{order}', [UserOrderController::class, 'update']);
    Route::delete('/orders/{order}', [UserOrderController::class, 'destroy']);

    // Account
    Route::get('/account', [UserAccountController::class, 'show']);
    Route::put('/account', [UserAccountController::class, 'update']);
    Route::delete('/account', [UserAccountController::class, 'destroy']);
});

Route::post('/contact', [ContactController::class, 'store']);