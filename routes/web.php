<?php

use App\Http\Controllers\PrintController;
use App\Http\Controllers\PrintController2;
use App\Models\Animal;
use App\Models\Event;
use Encore\Admin\Grid\Tools\Header;
use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::match(['get', 'post'], '/print2', [PrintController::class, 'index']);
Route::match(['get', 'post'], '/print', [PrintController2::class, 'index']);

Route::get('/', function () {

 
    header("Location: " . admin_url());
    die();
    return view('welcome');
});
