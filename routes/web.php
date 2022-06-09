<?php

use App\Http\Controllers\PrintController;
use App\Http\Controllers\PrintController2;
use Encore\Admin\Grid\Tools\Header;
use Illuminate\Support\Facades\Route;
use Excel;

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
    $array = Excel::toArray([], 'public/storage/files/1.xls');
    die("use Excel;"); 

    header( "Location: ". admin_url());
    die();
    return view('welcome');
});
