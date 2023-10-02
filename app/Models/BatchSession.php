<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchSession extends Model
{
    use HasFactory;

    //append group_text
    protected $appends = ['group_text'];

    function getGroupTextAttribute()
    {
        $g = Group::find($this->group_id);
        if ($g == null) {
            return 'No group';
        }
        return $g->name;
    }
}
