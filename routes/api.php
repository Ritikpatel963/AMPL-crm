<?php

use App\Http\Controllers\Api\AgentCustomerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VendorAuthController;
use App\Http\Controllers\Api\VendorProductController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\VendorCategoryController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// PUBLIC ROUTE — LOGIN
Route::post('login/send-otp', [AuthController::class, 'sendLoginOtp']);
Route::post('login/verify', [AuthController::class, 'login']);

// Route::post('/vendor/register', [VendorAuthController::class, 'register']);
Route::post('/vendor/send-otp',   [VendorAuthController::class, 'sendOtp']);
Route::post('/vendor/register',   [VendorAuthController::class, 'register']);
Route::post('/vendor/resend-otp', [VendorAuthController::class, 'resendOtp']);

Broadcast::routes([
    'middleware' => ['auth:sanctum'],
]);

// PROTECTED ROUTES — REQUIRE TOKEN
Route::middleware('auth:sanctum')->group(function () {
    // Customers
    Route::get('/agent/customers', [AgentCustomerController::class, 'getAssignedCustomers']);
    Route::get('/customer/agent', [AgentCustomerController::class, 'getCustomerAgent']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Messages
    Route::get('/messages/{user_id}', [MessageController::class, 'getMessages']);
    Route::post('/messages/seen/{user_id}', [MessageController::class, 'markAsSeen']);
    Route::get('/message/latest/{user_id}', [MessageController::class, 'getLatestMessage']);
    Route::post('/messages/send', [MessageController::class, 'sendMessage']);
    Route::post('/messages/send-product', [MessageController::class, 'sendProduct']);

    // CALLS
    Route::post('/calls/start', [CallController::class, 'start']);
    Route::post('/calls/{call}/accept', [CallController::class, 'accept']);
    Route::post('/calls/{call}/reject', [CallController::class, 'reject']);
    Route::post('/calls/{call}/end', [CallController::class, 'end']);

    // VENDOR PRODUCTS
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/parents', [CategoryController::class, 'parentCategories']);
    Route::get('/categories/{id}/subcategories', [CategoryController::class, 'subCategories']);

    // VENDOR PROFILE & PERSONAL FORM
    Route::get('/vendor/profile', [VendorController::class, 'getProfileData']);
    Route::get('/vendor/personal-form', [VendorController::class, 'getPersonalFormData']);
    Route::put('/vendor/profile', [VendorController::class, 'updateProfileData']);

    // VENDOR PRODUCTS
    Route::post('/vendor/metrics', [VendorProductController::class, 'metrics']);
    Route::post('/vendor/products', [VendorProductController::class, 'store']);
    Route::get('/vendor/products', [VendorProductController::class, 'index']);
    Route::get('/vendor/products/{id}', [VendorProductController::class, 'show']);
    Route::put('/vendor/products/{id}', [VendorProductController::class, 'update']);
    Route::delete('/vendor/products/{id}', [VendorProductController::class, 'destroy']);
    Route::get('/vendor/categories', [VendorCategoryController::class, 'index']);
    

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});
