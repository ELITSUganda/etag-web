<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($group) {
            if ($group->is_main_group == 'Yes') {
                throw new Exception("Cannot delete default group.", 1);
            }
            //set animals in this group to default group of this farm owner
            $default_group = Group::where([
                'administrator_id' => $group->administrator_id,
                'is_main_group' => 'Yes'
            ])->first();

            if ($default_group == null) {
                //throw default group not foumd
                throw new Exception("Default group not found.", 1);
            }
            Animal::where('group_id', $group->id)->update(['group_id' => $default_group->id]);
        });
    }


    public function animals()
    {
        return $this->hasMany(Animal::class);
    }

    public function administrator()
    {
        return $this->belongsTo(Administrator::class);
    }

    public function getAnimalCountAttribute()
    {
        return Animal::where('group_id', $this->id)->count();
    }

    public function getGroupTextAttribute()
    {
        return $this->name . ' (' . $this->animal_count . ')';
    }

    protected $appends = ['animal_count', 'group_text'];
}
