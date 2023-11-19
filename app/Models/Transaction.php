<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
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

            $financeAcc = FinanceCategory::find($m->finance_category_id);

            if ($financeAcc == null) {
                throw new Exception("Finance Acc not found.");
            }

            $f = Farm::where('administrator_id', $financeAcc->administrator_id)->first();
            if ($f == null) {
                throw new Exception('Farm not found.');
            }

            if (isset($m->created_at) && $m->created_at != null && $m->created_at != "") {
                $m->created_at = Carbon::parse($m->created_at);
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


    public function getAmountTextAttribute()
    {
        return "UGX " . number_format($this->amount);
    }

    public function getTransactionDateAttribute($d)
    {
        if ($d == null || $d == "") {
            return $this->created_at;
        }
        return $d;
    }

    protected $appends = [
        'transaction_date',
        'amount_text',
    ];
}
