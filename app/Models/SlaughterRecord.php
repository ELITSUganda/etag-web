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
}
