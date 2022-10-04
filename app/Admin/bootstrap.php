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

use App\Models\DrugStockBatchRecord;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;

//Admin::disablePjax();
Admin::css('css.css');
Encore\Admin\Form::forget(['map', 'editor']);


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

Admin::css('/assets/css/market-place.css');