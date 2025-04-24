<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistrictTagDistributionBatch extends Model
{
    use HasFactory;

    //belongs to district
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $rec = CentralTagBatch::find($model->central_tag_batch_id);
            if ($rec == null) {
                throw new Exception("Central tag batch not found", 1);
            }
            $model->batch_name = $rec->batch_name;
            $model->batch_description = $rec->batch_description;

            $distict = Location::find($model->district_id);
            if ($distict == null) {
                throw new Exception("District batch not found", 1);
            }
            $model->district_name = $distict->name;
            $model->district_code = $distict->code;
            $model->available_quantity = $model->total_distributed_quantity;
        });

        static::created(function ($model) {
            self::do_finalize($model);
        });

        static::updated(function ($model) {
            self::do_finalize($model);
        });
    }

    //do update parent
    public static function do_finalize($model)
    {
        $central_tag_batch = CentralTagBatch::find($model->central_tag_batch_id);
        if ($central_tag_batch == null) {
            throw new Exception("Central tag batch not found.", 1);
        }
        $total_suplied_amount = DistrictTagDistributionBatch::where([
            'central_tag_batch_id' => $model->central_tag_batch_id
        ])->sum('total_distributed_quantity');
        $balance  = $central_tag_batch->total_purchase_quantity - $total_suplied_amount;
        $central_tag_batch->available_quantity = $balance;
        $central_tag_batch->save();
    }
}
