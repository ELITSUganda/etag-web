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
use App\Models\Image as ModelsImage;
use App\Models\ImageModel;
use App\Models\Location;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Grid\Tools\Header;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Milon\Barcode\DNS1D;

use function PHPUnit\Framework\fileExists;

Route::get('/process-sub-counties', function () {


    //sete max execution time to unlimited
    set_time_limit(0); 
    foreach (Location::where([])->get() as $key => $loc) {

        if($loc->type != 'Sub-County'){
            continue;
        }

         
        Location::update_counts($loc->id); 
        $sub = Location::find($loc->id);
        $max = 1;
        if(isset($_GET['max'])){
            $max = $_GET['max'];
        }
        if($sub->farm_count < $max){
            continue;
        }

   
        echo "$loc->farm_count => $loc->name - Done <br>";
        die();
 

    }
    die("done");

    $hasPorcessed = Location::where('processed', 'Yes')
        ->where('type', 'Sub-County')
        ->count();
    if ($hasPorcessed < 30) {
        //set where type != District to be Sub-county
        $sql = "UPDATE locations SET type = 'Sub-County' WHERE type != 'District'";
        DB::select($sql);

        //set all sub counties to not processed
        $sql = "UPDATE locations SET processed = 'No' WHERE type = 'Sub-County'";
        DB::select($sql);
        //set all code be null
        $sql = "UPDATE locations SET code = null WHERE type = 'Sub-County'";
        DB::select($sql);
        //die('reset');
    }

    /* $s = Location::find(1000017);
    $dis = Location::find($s->parent);
    $code = Location::generate_sub_county_code($dis->id);
    $s->code = $code;
    dd($s->code);
    dd($s);
 */
    //Location::where('type', 'Sub-County')->update(['processed' => 'No']); 
    $locs = Location::where('processed', '!=', 'District')->where('processed', '!=', 'Yes')
        ->orderBy('id', 'asc')
        ->get();

    $i = 0;
    foreach ($locs as $key => $loc) {
        if ($loc->type == 'District') {
            continue;
        }
        $loc->processed = 'Yes';
        //check if name contains word Default
        if (str_contains(strtolower($loc->name), 'default')) {
            $loc->processed = 'FAILED';
            $loc->save();
            echo "<br>$i. $loc->name => $loc->code - Failed <br>";
            continue;
        }


        $i++;
        if ($i > 4000) {
            break;
        }
        $loc->type == 'Sub-County';

        if (((int)($loc->parent)) == 0) {
            if ($loc->processed != 'FAILED') {
                $loc->processed = 'FAILED';
                $loc->save();
            }
            echo "<br>$i. $loc->name => $loc->code - Failed <br>";
            continue;
        }
        $dis = Location::where([
            'id' => $loc->parent,
            'type' => 'District',
        ])->first();
        if ($dis == null) {
            echo "<br>$i. District not found for <code>$loc->name</code><br>";
            continue;
        }

        //check if district name is default
        if (str_contains(strtolower($dis->name), 'default')) {
            $loc->processed = 'FAILED';
            $loc->save();
            echo "<br>$i. $loc->name => $loc->code - Failed <br>";
            continue;
        }

        $loc->detail = 'Sub-County';
        $code = Location::generate_sub_county_code($dis->id);
        $loc->code = $code;
        $loc->save();

        $loc = Location::find($loc->id);
        echo "<br>$loc->id. $loc->name => $dis->name - CODE: $loc->code success<br>";
        //die(); 
    }
    die("done");
});



Route::get('/process-locations', function () {
    $locs = Location::where('type', 'District')->get();
    $i = 0;
    $cods = [
        'UG-314' =>    'Abim',
        'UG-301' =>    'Adjumani',
        'UG-322' =>    'Agago',
        'UG-323' =>    'Alebtong',
        'UG-315' =>    'Amolatar',
        'UG-324' =>    'Amudat',
        'UG-216' =>    'Amuria',
        'UG-316' =>    'Amuru',
        'UG-302' =>    'Apac',
        'UG-303' =>    'Arua',
        'UG-217' =>    'Budaka',
        'UG-218' =>    'Bududa',
        'UG-201' =>    'Bugiri',
        'UG-235' =>    'Bugweri',
        'UG-420' =>    'Buhweju',
        'UG-117' =>    'Buikwe',
        'UG-219' =>    'Bukedea',
        'UG-118' =>    'Bukomansimbi',
        'UG-220' =>    'Bukwo',
        'UG-225' =>    'Bulambuli',
        'UG-416' =>    'Buliisa',
        'UG-401' =>    'Bundibugyo',
        'UG-430' =>    'Bunyangabu',
        'UG-402' =>    'Bushenyi',
        'UG-202' =>    'Busia',
        'UG-221' =>    'Butaleja',
        'UG-119' =>    'Butambala',
        'UG-233' =>    'Butebo',
        'UG-120' =>    'Buvuma',
        'UG-226' =>    'Buyende',
        'UG-317' =>    'Dokolo',
        'UG-121' =>    'Gomba',
        'UG-304' =>    'Gulu',
        'UG-403' =>    'Hoima',
        'UG-417' =>    'Ibanda',
        'UG-203' =>    'Iganga',
        'UG-418' =>    'Isingiro',
        'UG-204' =>    'Jinja',
        'UG-318' =>    'Kaabong',
        'UG-404' =>    'Kabale',
        'UG-405' =>    'Kabarole',
        'UG-213' =>    'Kaberamaido',
        'UG-427' =>    'Kagadi',
        'UG-428' =>    'Kakumiro',
        'UG-237' =>    'Kalaki',
        'UG-101' =>    'Kalangala',
        'UG-222' =>    'Kaliro',
        'UG-122' =>    'Kalungu',
        'UG-102' =>    'Kampala',
        'UG-205' =>    'Kamuli',
        'UG-413' =>    'Kamwenge',
        'UG-414' =>    'Kanungu',
        'UG-206' =>    'Kapchorwa',
        'UG-236' =>    'Kapelebyong',
        'UG-335' =>    'Karenga',
        'UG-126' =>    'Kassanda',
        'UG-406' =>    'Kasese',
        'UG-207' =>    'Katakwi',
        'UG-112' =>    'Kayunga',
        'UG-433' =>    'Kazo',
        'UG-407' =>    'Kibaale',
        'UG-103' =>    'Kiboga',
        'UG-227' =>    'Kibuku',
        'UG-432' =>    'Kikuube',
        'UG-419' =>    'Kiruhura',
        'UG-421' =>    'Kiryandongo',
        'UG-408' =>    'Kisoro',
        'UG-434' =>    'Kitagwenda',
        'UG-305' =>    'Kitgum',
        'UG-319' =>    'Koboko',
        'UG-325' =>    'Kole',
        'UG-306' =>    'Kotido',
        'UG-208' =>    'Kumi',
        'UG-333' =>    'Kwania',
        'UG-228' =>    'Kween',
        'UG-123' =>    'Kyankwanzi',
        'UG-422' =>    'Kyegegwa',
        'UG-415' =>    'Kyenjojo',
        'UG-125' =>    'Kyotera',
        'UG-326' =>    'Lamwo',
        'UG-307' =>    'Lira',
        'UG-229' =>    'Luuka',
        'UG-104' =>    'Luwero',
        'UG-124' =>    'Lwengo',
        'UG-114' =>    'Lyantonde',
        'UG-336' =>    'Madi-Okollo',
        'UG-223' =>    'Manafwa',
        'UG-320' =>    'Maracha',
        'UG-105' =>    'Masaka',
        'UG-409' =>    'Masindi',
        'UG-214' =>    'Mayuge',
        'UG-209' =>    'Mbale',
        'UG-410' =>    'Mbarara',
        'UG-423' =>    'Mitooma',
        'UG-115' =>    'Mityana',
        'UG-308' =>    'Moroto',
        'UG-309' =>    'Moyo',
        'UG-106' =>    'Mpigi',
        'UG-107' =>    'Mubende',
        'UG-108' =>    'Mukono',
        'UG-334' =>    'Nabilatuk',
        'UG-311' =>    'Nakapiripirit',
        'UG-116' =>    'Nakaseke',
        'UG-109' =>    'Nakasongola',
        'UG-230' =>    'Namayingo',
        'UG-234' =>    'Namisindwa',
        'UG-224' =>    'Namutumba',
        'UG-327' =>    'Napak',
        'UG-310' =>    'Nebbi',
        'UG-231' =>    'Ngora',
        'UG-424' =>    'Ntoroko',
        'UG-411' =>    'Ntungamo',
        'UG-328' =>    'Nwoya',
        'UG-337' =>    'Obongi',
        'UG-331' =>    'Omoro',
        'UG-329' =>    'Otuke',
        'UG-321' =>    'Oyam',
        'UG-312' =>    'Pader',
        'UG-332' =>    'Pakwach',
        'UG-210' =>    'Pallisa',
        'UG-110' =>    'Rakai',
        'UG-429' =>    'Rubanda',
        'UG-425' =>    'Rubirizi',
        'UG-431' =>    'Rukiga',
        'UG-412' =>    'Rukungiri',
        'UG-435' =>    'Rwampara',
        'UG-111' =>    'Sembabule',
        'UG-232' =>    'Serere',
        'UG-426' =>    'Sheema',
        'UG-215' =>    'Sironko',
        'UG-211' =>    'Soroti',
        'UG-212' =>    'Tororo',
        'UG-113' =>    'Wakiso',
        'UG-313' =>    'Yumbe',
        'UG-330' =>    'Zombo',
        'Terego' =>    'Terego',
        'Bwaka' =>    'Bwaka',
        'Test' =>    'Test',
        'Test Location 3' =>    'Test Location 3',
        'Test Location 2' =>    'Test Location 2',
        'Test Location' =>    'Test Location',
        'Other locations' =>    'Other locations',
        'Default location' =>    'Default location',
        'Default subcounty' =>    'Default location',
    ];
    foreach ($locs as $key => $loc) {
        /* if (
            $loc->id == 0 ||
            $loc->name == 'Test Location' ||
            $loc->name == 'Test Location 2'
        ) {
            continue;
        } */
        /* $loc->processed = 'Yes';
       $loc->save();
       continue;  */
        $code_found = false;
        foreach ($cods as $code => $name) {
            if ($loc->name == 'Luweero') {
                $loc->name = 'Luwero';
            }
            if ($loc->name == 'Default subcounty') {
                $loc->name = 'Default subcounty';
                $loc->parent = 0;
                $loc->type = 'Sub-County';
                $loc->save();
                continue;
            }
            if ($loc->name == 'Kibuube') {
                $loc->name = 'Kikuube';
            }
            if ($loc->name == 'Ssembabule') {
                $loc->name = 'Sembabule';
            }
            if ($loc->name == 'Kabong') {
                $loc->name = 'Kaabong';
            }
            if ($loc->name == 'Kitegwenda') {
                $loc->name = 'Kitagwenda';
            }
            if (strtolower($loc->name) == strtolower($name)) {
                $i++;
                $code_found = true;
                $loc->name = $name;
                if ($loc->code != $code) {
                    $loc->code = $code;
                    $loc->save();
                    echo "$i. $loc->name => $loc->code - Done <br>";
                    continue;
                }
                echo "$i. $loc->name => $loc->code - Processed <br>";
                break;
            }
        }
        if (!$code_found) {
            echo "$i. $loc->name => $loc->code - Not Found <br>";
            die();
        }
    }
    die("done");
});
Route::get('/process-vaccination-status', function () {
    $SQL = 'SELECT * FROM `animals` WHERE `type` = "Cattle"';
    $cattle = DB::select($SQL);
    $now = Carbon::now();
    //SET max_execution_time to unlimited
    set_time_limit(0);

    foreach ($cattle as $key => $cow) {

        if ($cow->genetic_donor == 'Pending for vaccination') {
            continue;
        }

        $vaccinated = false;
        if ($cow->fmd != null) {
            if (strlen($cow->fmd) > 3) {
                $t = null;
                try {
                    $t = Carbon::parse($cow->fmd);
                } catch (\Throwable $th) {
                    $t = null;
                }
                if ($t != null) {
                    $diff = $now->diffInMonths($t);
                    if ($diff < 6) {
                        $vaccinated = true;
                    } else {
                        $vaccinated = false;
                    }
                }
            }
        }
        $status = 'Not Vaccinated';
        if ($vaccinated) {
            $status = 'Vaccinated';
        }
        $SQL = "UPDATE `animals` SET `genetic_donor` = '$status' WHERE `id` = $cow->id";
        DB::select($SQL);
        echo "$key. $cow->v_id => $status <br>";
    }
    exit;
});
Route::get('/test', function () {
    //echo DNS1D::getBarcodeSVG('4445645656', 'PHARMA2T');
    //echo DNS1D::getBarcodeHTML('4445645656', 'PHARMA2T');
    //echo '<img src="data:image/png,' . DNS1D::getBarcodePNG('4', 'C39+') . '" alt="barcode"   />';
    //echo DNS1D::getBarcodePNGPath('4445645656', 'PHARMA2T');
    //echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG('4', 'C39+') . '" alt="barcode"   />';

    $data = 'VID: 4445645656\n';
    $data .= "SLAUGHTER DATE: \n";
    $data .= "4445645656\n";
    $data .= "4445645656\n";
    $data .= '4445645656\n';
    $data .= '4445645656\n';
    $multiplier = 3;
    $link = DNS2D::getBarcodePNGPath($data, 'QRCODE', 3 * $multiplier, 3 * $multiplier, array(0, 0, 0), true);
    $url = url($link);
    echo $url;

    $img_size = getimagesize($url);

    //to mb
    $size = $img_size[0] * $img_size[1] * 8 / 1024 / 1024;

    echo '<img  width="400" src="' . $url . '" alt="barcode"   />';
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "Size: $size MB";

    die();


    echo DNS1D::getBarcodeSVG('4445645656', 'C39');
    echo DNS2D::getBarcodeHTML('4445645656', 'QRCODE');
    echo DNS2D::getBarcodePNGPath('4445645656', 'PDF417');
    echo DNS2D::getBarcodeSVG('4445645656', 'DATAMATRIX');
    echo '<img src="data:image/png;base64,' . DNS2D::getBarcodePNG('4', 'PDF417') . '" alt="barcode"   />';
    die();

    $multiplier = 1.5;
    $link = DNS1D::getBarcodePNGPath('4445561', 'C128', 3 * $multiplier, 44 * $multiplier, array(0, 0, 0), true);
    $url = url($link);

    $img_size = getimagesize($url);

    //to mb
    $size = $img_size[0] * $img_size[1] * 8 / 1024 / 1024;

    echo '<img  width="400" src="' . $url . '" alt="barcode"   />';
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "Size: $size MB";



    die();
});


//Route::get('/', [WebController::class, 'index']);
Route::get('/', function () {
    header('Location: ' . admin_url());
    die();
});

Route::get('/process-profile-photos', function () {
    $aniamls = Animal::all();
    $i = 0;
    foreach ($aniamls as $key => $an) {
        if ($an->photo != null) {
            if (strlen($an->photo) > 3) {
                continue;
            }
        }
        $i++;
        $img = Image::where([
            'parent_id' => $an->id,
            'type' => 'Animal',
            'parent_endpoint' => 'Animal',
        ])->first();
    }
    echo "Done: $i";
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
Route::get('vaccination/{id}', [PrintController::class, 'print_vaccination']);


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
