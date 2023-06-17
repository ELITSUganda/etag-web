<?php

use App\Http\Controllers\MainController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\PrintController2;
use App\Http\Controllers\WebController;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\Animal;
use App\Models\Event;
use App\Models\Gen;
use App\Models\Utils;
use Carbon\Carbon;
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

Route::get('/gen', function () {
    die(Gen::find($_GET['id'])->do_get());
})->name("gen");


Route::get('demo', function () {
    $not_found = [];
    $ans = Animal::where([
        'administrator_id' => 777,
        'type' => 'Cattle'
    ])
        ->orderBy('updated_at', 'desc')
        ->get();
    foreach ($ans as $key => $v) {
        $event = Event::where([
            'animal_id' => $v->id,
        ])
            ->orderBy('id', 'desc')
            ->first();
        if ($event == null) {
            $not_found[] = $v;
            continue;
        }

        $d1 = Carbon::now();
        $d2 = Carbon::parse($event->created_at);
        $dif = $d1->diffInDays($d2);
        if ($dif > 100) {
            $not_found[] = $v;
            continue;
        }

        $event = Event::where([
            'animal_id' => $v->id,
            'type' => 'Weight check',
        ])
            ->orderBy('id', 'desc')
            ->first();
        if ($event == null) {
            $not_found[] = $v;
            continue;
        }


        $d1 = Carbon::now();
        $d2 = Carbon::parse($event->created_at);
        $dif = $d1->diffInDays($d2);
        if ($dif > 10) {
            $not_found[] = $v;
            continue;
        }
    }
    $i = 0;
    foreach ($not_found as $key => $an) {
        $i++;
        echo $i . ".<br><b>VID</b>: {$an->v_id} <br> <b>WEI:</b> {$an->weight_text}<br><br>";
    }
    die("romina");
    return '<h2>DVO Lyantonde: <code>+256775679511</code></h2>' .
        '<h2>DVO Checkpoint officer: <code>+256706638491</code></h2>';
});
Route::get('generate-variables', [MarketController::class, 'generate_variables']);
Route::get('market', [MarketController::class, 'index'])->name('market');
Route::get('market/register', [MarketController::class, 'register'])->name('m-register');
Route::get('market/account-orders', [MarketController::class, 'account_orders'])->name('account-orders');
Route::get('market/account-logout', [MarketController::class, 'account_logout'])->name('account-logout');
Route::get('buy-now/{id}', [MarketController::class, 'buy_now'])->name('buy-now');
Route::post('buy-now/{id}', [MarketController::class, 'buy_now_post'])->name('buy-now-post');

Route::post('market/register', [MarketController::class, 'register_post'])
    ->middleware(RedirectIfAuthenticated::class)->name('m-register-post');


Route::match(['get', 'post'], '/process_thumbnails', [PrintController::class, 'prepareThumbnails']);
Route::match(['get', 'post'], '/print2', [PrintController::class, 'index']);
Route::match(['get', 'post'], '/print', [PrintController::class, 'index']);


Route::match(['get'], '/register', [MainController::class, 'create_account_page']);
Route::get('process-photos', [MainController::class, 'process_photos']);
Route::match(['post'], '/register', [MainController::class, 'create_account_save']);


Route::get('/{slug}', [MarketController::class, 'product'])->name('product');
