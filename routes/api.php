<?php

use App\Http\Controllers\ApiLoginController;
use App\Http\Controllers\DistrictController;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('districts', [DistrictController::class, 'index'] );
Route::get('districts/{id}', [DistrictController::class, 'show']);
Route::post('districts', [DistrictController::class, 'store'] );
Route::put('districts/{id}', [DistrictController::class, 'update'] );
Route::delete('districts/{id}',[DistrictController::class, 'delete'] );

// ========== lofin starts ============== //
Route::post('login', [ApiLoginController::class, 'index']);
Route::post('login/create-account', [ApiLoginController::class, 'create_account']);
// ========== lofin ends ============== //


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});