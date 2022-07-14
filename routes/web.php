<?php

use App\Http\Controllers\PrintController;
use App\Http\Controllers\PrintController2;
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
   /*  $ev = new Event(); 
    $ev->animal_id = 16;
    $ev->administrator_id = 1;
    $ev->district_id = 1;
    $ev->sub_county_id = 1;
    $ev->parish_id = 1;
    $ev->farm_id = 1; 
    $ev->type = 'Stolen';
    $ev->approved_by = 1;
    $ev->detail = 1;
    $ev->animal_type = 1;
    $ev->disease_id = 1;
    $ev->vaccine_id = 1;
    $ev->medicine_id = 1;
    $ev->is_batch_import = 1;
    $ev->time_stamp = 1;
    $ev->import_file = 1; */
/* 
    $ev = Event::all()->first();
    $ev->type = 'Stolen';
    $ev->medicine_id = rand(10000,100000);
    $ev->save();
    die("ROmina"); */


    header("Location: " . admin_url());
    die();

    return view('welcome');
});
