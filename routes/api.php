<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\ProductController;
use \App\Http\Controllers\AuthController;
use \App\Http\Controllers\StaffController;
use \App\Http\Controllers\FilesController;
use \App\Http\Controllers\UserController;
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

//public routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

//protected routes
Route::middleware('auth:sanctum')->group( function () {
    Route::post('/logout',[AuthController::class, 'logout']);
    Route::get('product', [ProductController::class, 'show'])->name('product');
    Route::get('/user-role', [AuthController::class, 'roles'])->name('roles');
    Route::post('/create-staff', [StaffController::class, 'createStaff']);
    Route::get('/get-staff', [StaffController::class, 'getUserStaff']);
    Route::delete('/remove-staff/{id}', [StaffController::class, 'removeStaff']);
    Route::get('/get-user', [UserController::class, 'getUser']);
    Route::post('/upload-photo', [FilesController::class, 'uploadPhoto']);

    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::post('/update-user', [UserController::class, 'updateUser']);
});


Route::get('/get-staff', [StaffController::class, 'getStaffDetails']);