<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthReport extends Model
{
    protected $user_id;

    //getter for top_diseases
    public function getTopDiseasesAttribute()
    {
        $disease_ids = Event::where([
            'administrator_id' => $this->user_id,
            'type' => 'Disease test',
        ])->groupBy('disease_id')
            ->selectRaw('count(*) as count, disease_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->pluck('disease_id');
        $diseases = [];
        foreach ($disease_ids as $disease_id) {
            $disease = Disease::find($disease_id);
            if ($disease) {
                $count = Event::where([
                    'administrator_id' => $this->user_id,
                    'type' => 'Disease test',
                    'disease_id' => $disease_id
                ])->count();
                $d['name'] = $disease->name;
                $d['count'] = $count;
                $d['id'] = $disease->id;
                $diseases[] = $d;
            }
        }
        return $diseases;
    }

    //getter for number_of_sick_animals
    public function getNumberOfSickAnimalsAttribute()
    {
        return Event::where([
            'administrator_id' => $this->user_id,
            'type' => 'Disease test',
            'status' => 'Positive'
        ])->count();
    }

    //setter for user_id
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }


    public function getAdminIdAttribute()
    {
        return $this->user_id;
    }

    //appends admin_id 
    protected $appends = [
        'admin_id',
        'number_of_sick_animals',
        'top_diseases',
    ];
}
