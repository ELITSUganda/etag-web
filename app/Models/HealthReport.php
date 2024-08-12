<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HealthReport extends Model
{
    protected $user_id;

    //getter for monthly_borns
    public function getMonthlyBornsAttribute()
    {
        $now = Carbon::now();
        for ($i = 0; $i <= 12; $i++) {
            //past 12 months born animals per month
            $month_start = $now->copy()->subMonths($i)->startOfMonth();
            $month_end = $now->copy()->subMonths($i)->endOfMonth();
            $count = Animal::where([
                'administrator_id' => $this->user_id,
                'has_parent' => 'Yes',
            ])->whereBetween('dob', [$month_start, $month_end])
                ->count();
            $d['count'] = $count;
            $d['month'] = $month_start->format('F-Y');
            $counts[] = $d;
        }
        return $counts;
    }

    //getter for newly_born_animals
    public function getNewlyBornAnimalsAttribute()
    {
        $new_animals = Animal::where([
            'administrator_id' => $this->user_id,
            'has_parent' => 'Yes',
        ])->orderBy('dob', 'desc')
            ->limit(10)
            ->get();
        $animals = [];
        foreach ($new_animals as $animal) {
            $parent = Animal::find($animal->parent_id);
            $parent_id = 'N/A';
            $parent_vid = 'N/A';
            if ($parent != null) {
                $parent_id = $parent->id;
                $parent_vid = $parent->v_id;
            }
            $a['name'] = $animal->v_id;
            $a['photo'] = $animal->photo;
            $a['id'] = $animal->id;
            $a['parent_id'] = $parent_id;
            $a['parent_vid'] = $parent_vid;
            $a['dob'] = $animal->dob;
            $animals[] = $a;
        }
        return $animals;
    }

    //getter for frequently_pregnant_animals
    public function getFrequentlyPregnantAnimalsAttribute()
    {
        $top_animal_ids = PregnantAnimal::where([
            'administrator_id' => $this->user_id,
            'current_status' => 'Pregnant',
        ])->groupBy('animal_id')
            ->selectRaw('count(*) as count, animal_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->pluck('animal_id');
        $animals = [];
        foreach ($top_animal_ids as $animal_id) {
            $animal = Animal::find($animal_id);
            if ($animal) {
                $count = PregnantAnimal::where([
                    'administrator_id' => $this->user_id,
                    'current_status' => 'Pregnant',
                    'animal_id' => $animal_id
                ])->count();
                $a['name'] = $animal->v_id;
                $a['photo'] = $animal->photo;
                $a['count'] = $count;
                $a['id'] = $animal->id;
                $animals[] = $a;
            }
        }
        return $animals;
    }

    //getter for number_of_pregnant_animals
    public function getNumberOfPregnantAnimalsAttribute()
    {
        return PregnantAnimal::where([
            'administrator_id' => $this->user_id,
            'current_status' => 'Pregnant',
        ])->count();
    }

    //list of top 10 most sick animals by cost
    public function getMostSickAnimalsByCostAttribute()
    {
        //first get animals ids group by total cost of treatment in events
        $animal_ids = Event::where([
            'administrator_id' => $this->user_id,
            'type' => 'Treatment',
        ])->groupBy('animal_id')
            ->selectRaw('sum(drug_worth) as cost, animal_id')
            ->orderBy('cost', 'desc')
            ->limit(10)
            ->get()
            ->pluck('animal_id');
        //get animals details
        $animals = [];
        foreach ($animal_ids as $animal_id) {
            $animal = Animal::find($animal_id);
            if ($animal) {
                $cost = Event::where([
                    'administrator_id' => $this->user_id,
                    'type' => 'Treatment',
                    'animal_id' => $animal_id
                ])->sum('drug_worth');
                $cost += Event::where([
                    'administrator_id' => $this->user_id,
                    'type' => 'Batch Treatment',
                    'animal_id' => $animal_id
                ])->sum('drug_worth');

                $a['name'] = $animal->v_id;
                $a['photo'] = $animal->photo;
                $a['cost'] = $cost;
                $a['id'] = $animal->id;

                $animals[] = $a;
            }
        }
        // sort by cost
        usort($animals, function ($a, $b) {
            return $b['cost'] <=> $a['cost'];
        });
        return $animals;
    }

    //getter for last_drugs_cost
    public function getLastDrugsCostAttribute()
    {
        $cost = Event::where([
            'administrator_id' => $this->user_id,
            'type' => 'Treatment',
        ])->orderBy('created_at', 'desc')
            ->limit(1)
            ->sum('drug_worth');
        $cost += Event::where([
            'administrator_id' => $this->user_id,
            'type' => 'Batch Treatment',
        ])->orderBy('created_at', 'desc')
            ->limit(1)
            ->sum('drug_worth');
        $data['cost'] = $cost;
        $data['year'] = Carbon::now()->format('Y');
        return $data;
    }

    //this_year_drugs_cost
    public function getThisYearDrugsCostAttribute()
    {
        $now = Carbon::now();
        $year_start = $now->copy()->startOfYear();
        $year_end = $now->copy()->endOfYear();
        $cost = Event::where([
            'administrator_id' => $this->user_id,
            'type' => 'Treatment',
        ])->whereBetween('created_at', [$year_start, $year_end])
            ->sum('drug_worth');
        $cost += Event::where([
            'administrator_id' => $this->user_id,
            'type' => 'Batch Treatment',
        ])->whereBetween('created_at', [$year_start, $year_end])
            ->sum('drug_worth');
        $data['year'] = $year_start->format('Y');
        $data['cost'] = $cost;
        return $data;
    }

    //getter for monthly_drugs_cost
    public function getMonthlyDrugsCostAttribute()
    {
        $now = Carbon::now();
        for ($i = 0; $i <= 12; $i++) {
            //pasth 12 months drug_worth total per month
            $month_start = $now->copy()->subMonths($i)->startOfMonth();
            $month_end = $now->copy()->subMonths($i)->endOfMonth();
            $cost = Event::where([
                'administrator_id' => $this->user_id,
                'type' => 'Treatment',
            ])->whereBetween('created_at', [$month_start, $month_end])
                ->sum('drug_worth');
            $cost += Event::where([
                'administrator_id' => $this->user_id,
                'type' => 'Batch Treatment',
            ])->whereBetween('created_at', [$month_start, $month_end])
                ->sum('drug_worth');
            $d['cost'] = $cost;
            $d['month'] = $month_start->format('F-Y');
            $costs[] = $d;
        }
        return $costs;
    }

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

    //getter for heaviest_animal
    public function getHeaviestAnimalAttribute()
    {
        $animal = Animal::where('administrator_id', $this->user_id)
            ->orderBy('weight', 'desc')
            ->first();
        if ($animal) {
            $data['name'] = $animal->v_id;
            $data['weight'] = $animal->weight;
            $data['photo'] = $animal->photo;
            $data['id'] = $animal->id;
            return $data;
        } else {
            $data['name'] = 'N/A';
            $data['weight'] = 'N/A';
            $data['photo'] = 'N/A';
            $data['id'] = 'N/A';
            return $data;
        }
        return null;
    }

    //getter for heaviest_animals
    public function getHeaviestAnimalsAttribute()
    {
        $animals = Animal::where('administrator_id', $this->user_id)
            ->orderBy('weight', 'desc')
            ->limit(10)
            ->get();
        $data = [];
        foreach ($animals as $animal) {
            $a['name'] = $animal->v_id;
            $a['weight'] = $animal->weight;
            $a['photo'] = $animal->photo;
            $a['id'] = $animal->id;
            $data[] = $a;
        }
        return $data;
    }

    //getter for lightest_animals
    public function getLightestAnimalsAttribute()
    {
        //weight not null and not zero
        $animals = Animal::where('administrator_id', $this->user_id)
            ->whereNotNull('weight')
            ->where('weight', '>', 0)
            ->orderBy('weight', 'asc')
            ->limit(10)
            ->get();

        $data = [];
        foreach ($animals as $animal) {
            $a['name'] = $animal->v_id;
            $a['weight'] = $animal->weight;
            $a['photo'] = $animal->photo;
            $a['id'] = $animal->id;
            $data[] = $a;
        }
        return $data;
    }

    //getter for animals_that_increase_weight
    public function getAnimalsThatIncreaseWeightAttribute()
    {
        $animals = Animal::where('administrator_id', $this->user_id)
            ->whereNotNull('weight_change')
            ->where('weight_change', '>', 0)
            ->orderBy('weight_change', 'desc')
            ->limit(10)
            ->get();
        $data = [];
        foreach ($animals as $animal) {
            $a['name'] = $animal->v_id;
            $a['weight_change'] = $animal->weight_change;
            $a['photo'] = $animal->photo;
            $a['id'] = $animal->id;
            $data[] = $a;
        }
        return $data;
    }

    //getter for animals_that_reduced_weight
    public function getAnimalsThatReducedWeightAttribute()
    {
        $animals = Animal::where('administrator_id', $this->user_id)
            ->whereNotNull('weight_change')
            ->where('weight_change', '<', 0)
            ->orderBy('weight_change', 'asc')
            ->limit(10)
            ->get();
        $data = [];
        foreach ($animals as $animal) {
            $a['name'] = $animal->v_id;
            $a['weight_change'] = $animal->weight_change;
            $a['photo'] = $animal->photo;
            $a['id'] = $animal->id;
            $data[] = $a;
        }
        return $data;
    }

    //getter for weight_ranges
    public function getWeightRangesAttribute()
    {
        $ranges = [];
        /* $ranges[] = $this->getWeightRange(0, 50);
        $ranges[] = $this->getWeightRange(50, 100);
        $ranges[] = $this->getWeightRange(100, 150);
        $ranges[] = $this->getWeightRange(150, 200);
        $ranges[] = $this->getWeightRange(200, 250);
        $ranges[] = $this->getWeightRange(250, 300);
        $ranges[] = $this->getWeightRange(300, 350);
        $ranges[] = $this->getWeightRange(350, 400);
        $ranges[] = $this->getWeightRange(400, 450);
        $ranges[] = $this->getWeightRange(450, 500); */

        for ($i = 0; $i <= 1000; $i += 50) {
            $r['name'] = $i . '-' . ($i + 50);
            $r['count'] = Animal::where('administrator_id', $this->user_id)
                ->whereNotNull('weight')
                ->where('weight', '>', 0)
                ->whereBetween('weight', [$i, $i + 50])
                ->count();
            $ranges[] = $r;
        }

        return $ranges;
    }

    //appends admin_id 
    protected $appends = [
        'weight_ranges',
        'animals_that_increase_weight',
        'animals_that_reduced_weight',
        'lightest_animals',
        'heaviest_animals',
        'heaviest_animal',
        'monthly_borns',
        'newly_born_animals',
        'frequently_pregnant_animals',
        'number_of_pregnant_animals',
        'most_sick_animals_by_cost',
        'last_drugs_cost',
        'this_year_drugs_cost',
        'monthly_drugs_cost',
        'admin_id',
        'number_of_sick_animals',
        'top_diseases',
    ];
}
