<?php

use App\Http\Controllers\MainController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\PrintController2;
use App\Http\Controllers\WebController;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\Animal;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Gen;
use App\Models\Image;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Grid\Tools\Header;
use Illuminate\Support\Facades\Route;

use function PHPUnit\Framework\fileExists;

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

Route::get('/process', function () {

    //set_time_limit(0);
    set_time_limit(-1);
    //ini_set('memory_limit', '1024M');
    ini_set('memory_limit', '-1');

    $folderPath = base_path('public/thumbs/');
    $folderPath = base_path('public/storage/images/');

    $biggest = 0;
    $tot = 0;
    // Check if the folder exists
    if (is_dir($folderPath)) {

        // Get the list of items in the folder
        $items = scandir($folderPath);

        $i = 0;

        $imgs = [];
        // Loop through the items
        foreach ($items as $item) {


            // Exclude the current directory (.) and parent directory (..)
            if ($item != '.' && $item != '..') {


                $ext = pathinfo($item, PATHINFO_EXTENSION);
                if ($ext == null) {
                    return null;
                }
                $ext = strtolower($ext);


                if (!in_array($ext, [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                ])) { 
                    continue;
                }
                
                $source = $folderPath . "" . $item;
                $thumb = $folderPath . "thumb_" . $item;

                if(file_exists($thumb)){
                    continue;
                }
                
                $source = $folderPath . "" . $item;
                $target = $folderPath . "thumb_" . $item;
                Utils::create_thumbail([
                    'source' => $source,
                    'target' => $target
                ]);

                $target_file_size = filesize($target);
                $target_file_size = $target_file_size / (1024 * 1024);
                $target_file_size = round($target_file_size, 2);
                $target_file_size = $target_file_size . " MB";

                $thumb_size = filesize($target);
                $thumb_size = $thumb_size / (1024 * 1024);
                $thumb_size = round($thumb_size, 2);
                $thumb_size = $thumb_size . " MB";
                
                echo "Original Size: " . $item . "<br>";
                echo "Original Size: " . $target_file_size . "<br>";
                echo "Thumb Size: " . $thumb_size . "<br>";
                echo "<hr>";

                die("compress");

                $id = (int)$item;
                if (20142 < 1000) {
                    die("ID is less than 1000 => $id");
                }

                $an = Animal::where([
                    'v_id' => $id
                ])->first();
                if ($an == null) {
                    continue;
                    die("Animal not exists => $id");
                }

                $img_id = time() . '-' . rand(10000, 99999) . "." . $ext;

                $source = $folderPath . "/" . $item;
                $target = $folderPath . "/done/" . $img_id;
                rename($source, $target);
                $img = new Image();
                $img->administrator_id = $an->administrator_id;
                $img->src = $img_id;
                $img->thumbnail = 'thumb_' . $img_id;
                $img->parent_id = $an->id;
                $img->product_id = null;
                $img->size  = 0;
                $img->type  = 'Animal';
                $img->parent_endpoint  = 'Animal';
                $img->note  = 'First Photo';
                $img->save();

                echo $i . ". " . $item . "<br>";
                continue;

                $imgs[] = $id;

                $target = $folderPath . "/" . $item;
                $target_file_size = filesize($target);

                if ($target_file_size > $biggest) {
                    $biggest = $target_file_size;
                }
                $tot += $target_file_size;


                //echo $i.". ".$item . "<br>";
                $i++;
                continue;

                $i++;
                print_r($i . "<br>");



                $fileSize = filesize($folderPath . "/" . $item);
                $fileSize = $fileSize / (1024 * 1024);
                $fileSize = round($fileSize, 2);
                $fileSize = $fileSize . " MB";
                $url = "http://localhost:8888/ham/public/temp/pics-1/" . $item;

                $source = $folderPath . "/" . $item;
                $target = $folderPath . "/thumb/" . $item;
                Utils::create_thumbail([
                    'source' => $source,
                    'target' => $target
                ]);

                echo "<img src='$url' alt='$item' width='550'/>";
                $target_file_size = filesize($target);
                $target_file_size = $target_file_size / (1024 * 1024);
                $target_file_size = round($target_file_size, 2);
                $target_file_size = $target_file_size . " MB";
                $url_2 = "http://localhost:8888/ham/public/temp/pics-1/thumb/" . $item;
                echo "<img src='$url_2' alt='$item' width='550' />";


                // Print the item's name
                echo "<b>" . $fileSize . "<==>" . $target_file_size . "<b><br>";
            }
        }
    } else {
        echo "The specified folder does not exist.";
    }

    die("<hr>");
    $i = 0;
    $farm = Farm::find(309);
    foreach ($imgs as $key => $v_id) {
        $an = Animal::where([
            'v_id' => $v_id
        ])->first();
        if ($an != null) {
            continue;
        }
        $an = new Animal();
        $an->administrator_id = $farm->administrator_id;
        $an->district_id = $farm->district_id;
        $an->sub_county_id = $farm->sub_county_id;
        $an->parish_id = $farm->parish_id;
        $an->status = 'Active';
        $an->type = 'Cattle';
        $an->e_id = '8000000000' . $v_id;
        $an->v_id = $v_id;
        $an->farm_id = $farm->id;
        $an->lhc = $farm->holding_code;
        $an->breed = 'Ankole';
        $an->sex = 'Female';
        $an->dob = Carbon::now()->subYears(2);
        $an->weight = 0;
        $an->group_id = 263;
        $an->save();
        $i++;
        echo $i . ". " . $v_id . "<br>";
    }
    die("done");

    dd($imgs);
    //8000000
    $biggest = $biggest / (1024 * 1024);
    $biggest = round($biggest, 2);
    $biggest = $biggest . " MB";
    $tot = $tot / (1024 * 1024);
    $tot_gb = $tot / 1024;
    $tot = round($tot, 2);
    echo "<hr>";
    $tot = $tot . " MB<br>";
    $tot = $tot_gb . " GB<br>";
    echo "Biggest: " . $biggest . "<br>";
    echo "Total: " . $tot . "<br>";
    echo "Count: " . $i . "<br>";
    die("done");
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



Route::get('/compress', function () {

    foreach (Image::where([
        'administrator_id' => 873,
    ])->get() as $key => $img) {
        $img->create_thumbail();
        echo ($img->thumbnail . "<br>");
        die("done");
    }

    dd('dine');

    $directory = 'public/temp_pics/DONE/';
    // Get the list of files in the directory
    $done = scandir($directory . '/done');
    $files = array_diff($done, array('.', '..'));
    $uniques = [];

    $i = 0;


    foreach ($files as $key => $pic) {


        set_time_limit(-1);
        ini_set('memory_limit', '-1');

        $img = Image::where([
            'src' => trim($pic)
        ])->first();
        if ($img != null) {
            continue;
        }

        $pics = explode('-', $pic);
        if (!isset($pics[0])) {
            die("nott found");
        }
        $_pic = $pics[0];

        $an = Animal::where([
            'v_id' => $_pic
        ])->first();
        if ($an == null) {
            die('Animal not found');
        }

        $img = new Image();
        $img->administrator_id = 873;
        $img->src = trim($pic);
        $img->thumbnail = null;
        $img->parent_id = $an->id;
        $img->product_id = $an->id;
        $img->type = 'animal';
        $img->parent_endpoint = 'animal';
        $img->note = 'New Photo';
        $img->save();
        if (str_contains(strtolower($pic), 'm')) {
            $an->photo = 'storage/images/' . $pic;
            $an->save();
        }
        echo ("$i. DONE => " . $img->src . "<br>");
        $i++;
    }
    die("as");

    die('done');

    dd($f2);
    foreach ($files as $key => $pic) {
        $pics = explode(' ', $pic);
        if (!isset($pics[0])) {
            die("nott found");
        }
        $_pic = $pics[0];

        if (in_array($_pic, $uniques)) {
            continue;
        }
        $uniques[] = $_pic;

        $an = Animal::where([
            'v_id' => $_pic
        ])->first();
        if ($an != null) {
            continue;
        }

        $an = new Animal();
        $an->administrator_id = 873;
        $an->status = 'Active';
        $an->type = 'Cattle';
        $an->e_id = '8000000000' . $_pic;
        $an->v_id = $_pic;
        $an->farm_id = 309;
        $an->breed = 'Ankole';
        $an->sex = 'Female';
        $an->dob = Carbon::now()->subYears(4);
        $an->fmd = Carbon::now()->subYears(4);
        $an->save();
        echo $i . ". " . $pic . "<br>";

        $i++;
    }

    die();

    $files = scandir($directory);

    set_time_limit(-1);
    ini_set('memory_limit', '-1');
    // Remove . and .. from the list
    $files = array_diff($files, array('.', '..'));
    foreach ($files as $src) {
        if (in_array($src, $done)) {
            echo $src . "<===done <br>";
            continue;
        }
        $i++;
        try {
            $thumb = Utils::create_thumbail([
                'source' => $directory . $src,
                'target' => 'public/temp_pics/DONE/done/' . $src,
                'quality' => 40,

            ]);
        } catch (\Throwable $th) {
            echo "FAILED ==> $src<br>";
            continue;
        }
        echo "<h2>$i. $src ===> " . round(filesize('public/temp_pics/DONE/' . $src) / (1024 * 1024), 2) . "MBs => " . round(filesize('public/temp_pics/DONE/done/' . $src) / (1024 * 1024), 2) . " MBs</h2>";

        echo '<img width="500" src="temp_pics/DONE/' . $src . '" >';
        echo '<img width="500" src="temp_pics/DONE/done/' . $src . '" >';
        echo "<hr>";

        //        unlink('public/temp_pics/DONE/' . $src);

    }
})->name("gen");

Route::get('/{slug}', [MarketController::class, 'product'])->name('product');
