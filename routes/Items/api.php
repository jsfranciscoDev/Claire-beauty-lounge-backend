<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\ProductController;
use \App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemTypeController;

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

//Items
Route::post('/store', [ItemController::class, 'store']);
Route::get('/show', [ItemController::class, 'show']);
Route::get('/update', [ItemController::class, 'update']);

// Item Types
Route::post('/type/store', [ItemTypeController::class, 'store']);
Route::get('/type/show', [ItemTypeController::class, 'show']);
Route::get('/type/update', [ItemTypeController::class, 'update']);