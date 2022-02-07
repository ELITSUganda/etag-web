<?php

use App\Admin\Controllers\FarmController;
use App\Http\Controllers\ApiAnimalController;
use App\Http\Controllers\ApiEventController;
use App\Http\Controllers\ApiFarmController;
use App\Http\Controllers\ApiLoginController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\UtilsController;
use App\Http\Controllers\ApiUserController; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('districts', [DistrictController::class, 'index'] );
Route::get('districts/{id}', [DistrictController::class, 'show']);
Route::post('districts', [DistrictController::class, 'store'] );
Route::put('districts/{id}', [DistrictController::class, 'update'] );
Route::delete('districts/{id}',[DistrictController::class, 'delete'] );

// sub_counties
Route::get('sub_counties', [UtilsController::class, 'sub_counties']);
//Utils


// animal //
Route::post('animals', [ApiAnimalController::class, 'create']); 
Route::get('animals', [ApiAnimalController::class, 'index']);
Route::get('animals/{id}', [ApiAnimalController::class, 'show']);

Route::get('events', [ApiEventController::class, 'index']);
Route::get('events/{id}', [ApiEventController::class, 'show']);
// Animal controler //

// ========== users starts ============== //
Route::get('users', [ApiUserController::class, 'index']);
Route::get('farms/{id}', [ApiFarmController::class, 'show']);
Route::post('farms', [ApiFarmController::class, 'create']); 
Route::post('users', [ApiUserController::class, 'store']); 
Route::get('farms', [ApiFarmController::class, 'index']);
// ========== users ends ============== //


// ========== lofin starts ============== //
Route::post('login', [ApiLoginController::class, 'index']);
Route::post('login/create-account', [ApiLoginController::class, 'create_account']);
// ========== lofin ends ============== //


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

