<?php

use App\Http\Controllers\MainController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\PrintController2;
use App\Http\Controllers\WebController;
use App\Models\Event;
use App\Models\Utils;
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

//Route::get('/', [WebController::class, 'index']);
Route::get('/', function () {
    header('Location: ' . admin_url());
    die();
});

Route::get('market', [MarketController::class, 'index'])->name('market');


Route::match(['get', 'post'], '/process_thumbnails', [PrintController::class, 'prepareThumbnails']);
Route::match(['get', 'post'], '/print2', [PrintController::class, 'index']);
Route::match(['get', 'post'], '/print', [PrintController2::class, 'index']);
Route::match(['get'], '/register', [MainController::class, 'create_account_page']);
Route::match(['post'], '/register', [MainController::class, 'create_account_save']);
Route::get('/{slug}', [MarketController::class, 'product'])->name('product');
