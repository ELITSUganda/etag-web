<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use App\Models\Animal;
use App\Models\CheckPoint;
use App\Models\CheckPointRecord;
use App\Models\DrugStockBatchRecord;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Location;
use App\Models\Movement;
use App\Models\SlaughterHouse;
use App\Models\SubCounty;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


/* foreach (Event::where([])->orderBy('id','desc')->get() as $key => $f) { 
    $f->updated_at  = now();
    $f->save();
    dd($f);  
}
die("romina");
 */
/* $checkpoints = CheckPoint::where([])->get();
$i = 0;
foreach ($checkpoints as $key => $v) {
    echo "*CHEKCPOINT:* ".$v->name."<br>";
    $exp = 0;
    $found = 0;
    foreach ($v->sessions as $ses) {
        $exp += $ses->animals_expected;
        $found += $ses->animals_checked;
    }
    echo "*TOTAL EXPECTED ANIMALS:* ".$exp."<br>";
    echo "*TOTAL ANIMALS CHECKED:* ".$found."<br><br>";
}
die(); */
// $chpts = CheckPointRecord::where([])->get();
// foreach ($chpts as $key => $v) {
//     $v->checkpoint_id = $v->checkpoint->id;
//     $v->save();
// }
//Admin::disablePjax();
/* 
$mvs = Movement::where([])->orderBy('id', 'desc')->get();

$ans = 0;
foreach ($mvs as $key => $v) {
    if ($v->destination != 'To slaughter') {
        continue;
    }
    $s = SlaughterHouse::where([
        'id' => $v->destination_slaughter_house
    ])->first();
    if ($s == null) {
        continue;
    }
    if ($v->animals == null) {
        continue;
    }
    if (count($v->animals) < 1) {
        continue;
    }

    foreach ($v->animals as $key => $a) {
        if ($a == null) {
            continue;
        }
        $ans++;
    }
}

$i = 0;
echo "_*U-LITS MOVEMENT PERMITS - Records as on ".Utils::my_date_2(now())."*_<br><br>";
echo "<b>summary</b><br>";
echo "*Total Permits:* " . count($mvs) . "<br>";
echo "*Total Animals:* " . $ans . "<br>";

echo "<br><br><b>Details</b><br>";

foreach ($mvs as $key => $v) {
    if ($v->destination != 'To slaughter') {
        continue;
    }
    $s = SlaughterHouse::where([
        'id' => $v->destination_slaughter_house
    ])->first();
    if ($s == null) {
        continue;
    }
    if ($v->animals == null) {
        continue;
    }
    if (count($v->animals) < 1) {
        continue;
    }
    $i++;
    echo "*{$i}* DATE: " . Utils::my_date_2($v->created_at) . "<br>";
    echo "*PERMIT NO.* {$v->permit_Number}<br>";
    echo "*APPLICANT:* {$v->trader_name} - {$v->trader_phone}<br>";
    echo "*DESTINATION:* {$s->name} - Slaugter House<br>";
    echo "*No. of animals:* " . count($v->animals) . "<br>";
    echo "<br>*ANIMALS:*<br>";
    foreach ($v->animals as $key => $a) {
        if ($a == null) {
            continue;
        }
        echo ($key + 1) . ". - *{$a->e_id}* - {$a->v_id}<br>";
    }
    echo "<br>----------------------<br><br>";
}

die(''); */

Admin::css('css.css');
Encore\Admin\Form::forget(['map', 'editor']);


Utils::systemBoot(Auth::user());
/*
foreach (SubCounty::all() as $key => $s) {
    $l = Location::where([
        'name' => $s->name
    ])->first();

    if ($l == null) {
        continue;
    }
     $l->code = $s->code;
    $l->save();

    foreach (Farm::where([
        'sub_county_id' => $s->id
    ])->get() as $key => $f) {
        $f->sub_county_id = $l->save();
        $f->save();
    } 

    foreach (Animal::where([
        'sub_county_id' => $s->id
    ])->get() as $key => $f) {
        $f->sub_county_id = $l->save();
        $f->save();
    }
}
*/
/*

id	
created_at	
updated_at	
administrator_id	
district_id	
	
farm_type	
holding_code	
size	
latitude	
longitude	
dfm	
name	
village	
animals_count	
sheep_count	
goats_count	
cattle_count	
	
 
foreach (User::all() as $key => $v) {
    $v->phone_number = str_replace(' ', '', $v->phone_number);
    $v->phone_number = str_replace('_', '', $v->phone_number);
    $v->phone_number = Utils::prepare_phone_number($v->phone_number);
    if (Utils::phone_number_is_valid($v->phone_number)) {
        $v->username = $v->phone_number;
        $v->status = 1;
        continue;
    }
    $v->status = 0;
    $v->save();

    echo $v->phone_number . "<hr>";
}*/

$sql_1 = 'SELECT user_id FROM admin_role_users ';
$sql = "SELECT id FROM admin_users WHERE id NOT IN ($sql_1)";
$recs = DB::select($sql);
foreach ($recs as $v) {
    DB::table('admin_role_users')->insert([
        'role_id' => 3,
        'user_id' => $v->id
    ]);
}

foreach (DrugStockBatchRecord::where(['batch_number' => NULL])->get() as $x) {
    $x->batch_number = $x->batch->batch_number;
    $x->save();
}

Admin::css('assets/css/jquery-confirm.min.css');
Admin::js('assets/js/charts.js');


/* Admin::css('/assets/css/market-place.css'); */
#Admin::css(url('/assets/css/bootstrap.min.css')); 
Admin::css(url('/assets/bootstrap.css'));
Admin::css('/assets/styles.css');
