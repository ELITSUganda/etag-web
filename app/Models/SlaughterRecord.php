<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaughterRecord extends Model
{
    use HasFactory;

    protected $appends = ['v_text'];

    //getter for v_id_text
    public function getVTextAttribute()
    {
        return  SlaughterDistributionRecord::where('source_id', $this->id)->count();
    }

    /* //getter for available_weight 
    public function getAvailableWeightAttribute()
    {
        $sum = SlaughterDistributionRecord::where('source_id', $this->id)->sum('original_weight');
        return  $this->original_weight - $sum;
    } */
    
    // Relationships
    public function distributions()
    {
        return $this->hasMany(SlaughterDistributionRecord::class, 'source_id');
    }

    public function packagingRecords()
    {
        return $this->hasMany(PackagingRecord::class, 'slaughter_record_id');
    }
}
