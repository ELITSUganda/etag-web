<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceCategory extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $name = trim($m->name);
            $administrator_id = ((int)(trim($m->administrator_id)));
            $resp = FinanceCategory::where([
                'name' => $name,
                'administrator_id' => $administrator_id,
            ])->first();
            if ($resp != null) {
                throw new Exception('You alreafy have a financial account with same name.');
            }
        });
    }

    public function getBalanceAttribute()
    {
        return Transaction::where([
            'finance_category_id' => $this->id
        ])->sum('amount');
    }

    public function getBalanceTextAttribute()
    {
        return "UGX " . number_format($this->balance);
    }
    public function getTotalIncomeAttribute()
    {
        $tot = Transaction::where([
            'finance_category_id' => $this->id
        ])->where(
            'amount',
            '>',
            0
        )->sum('amount');

        return "UGX " . number_format($tot);
    }
    public function getTotalExpenceAttribute()
    {
        $tot = Transaction::where([
            'finance_category_id' => $this->id
        ])->where(
            'amount',
            '<',
            0
        )->sum('amount');

        return "UGX " . number_format($tot);
    }
    protected $appends = [
        'balance_text',
        'total_income',
        'total_expence',
    ];
}
