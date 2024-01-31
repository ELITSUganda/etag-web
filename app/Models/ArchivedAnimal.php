<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivedAnimal extends Model
{
    use HasFactory;

    //get archived animal by id
    public static function get_animal($id)
    {
        $all_animals = ArchivedAnimal::all();
        foreach ($all_animals as $animal) {
            $events = [];
            try {
                $events = json_decode($animal->events);
            } catch (\Exception $e) {
                $events = [];
            }
            if ($events != null) {
                foreach ($events as $event) {
                    if ($event->animal_id == $id) {
                        return $animal;
                    }
                }
            }
        }
        return null;
    }
}
