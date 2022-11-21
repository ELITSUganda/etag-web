<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            $financeAcc = FinanceCategory::find($m->finance_category_id);
            $financeAcc->balance = Transaction::where('finance_category_id', $m->finance_category_id)->sum('amount');
            $financeAcc->save();
        });
        self::creating(function ($m) {

            $f = Farm::find($m->farm_id);
            $financeAcc = FinanceCategory::find($m->finance_category_id);
            if ($f == null ) {
                die("Farm not found.");
            }
            if ($financeAcc == null) {
                die("Finance Acc not found.");
            }

            $m->administrator_id = $f->administrator_id;
            $m->district_id = $f->district_id;
            $m->sub_county_id = $f->sub_county_id;
            $m->farm_id = $f->id;

            $amount = (int)($m->amount);
            if ($amount < 0) {
                $amount = ((-2) * ($amount));
            }
            if ($m->is_income != 1) {
                $amount = (-1) * ($amount);
            }
            $m->amount = $amount;
        });
    }
}
