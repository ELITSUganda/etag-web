<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ButcherRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'slaughter_distribution_record_id',
        'animal_id',
        'slaughterhouse_id',
        'created_by_id',
        'source_type',
        'source_id',
        'source_name',
        'source_address',
        'source_phone',
        'receiver_type',
        'receiver_id',
        'receiver_name',
        'receiver_address',
        'receiver_phone',
        'lhc',
        'v_id',
        'e_id',
        'animal_owner_id',
        'bar_code',
        'qr_code',
        'post_fat',
        'post_grade',
        'post_animal',
        'post_age',
        'original_weight',
        'current_weight',
        'price',
        'slaughter_date',
        'cut_type',
        'prime_cut_type',
        'offal_cut_type',
        'is_sold',
        'buyer_id',
        'buyer_name',
        'buyer_phone',
        'buyer_address',
        'sold_date',
        'sold_price',
        'notes',
    ];

    // Relationships
    public function slaughterDistributionRecord()
    {
        return $this->belongsTo(SlaughterDistributionRecord::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Administrator::class, 'created_by_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Administrator::class, 'receiver_id');
    }

    public function buyer()
    {
        return $this->belongsTo(Administrator::class, 'buyer_id');
    }

    // Accessors
    public function getIsSoldBoolAttribute()
    {
        return $this->is_sold === 'Yes';
    }

    public function getRemainingWeightAttribute()
    {
        return $this->original_weight - $this->current_weight;
    }
}
