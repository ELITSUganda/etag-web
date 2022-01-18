<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\Parish;
use App\Models\SubCounty;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Continue_;

class SubCountiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $faker = \Faker\Factory::create();
        $names = [
            "Kawanda",
            "Kazo Nabweru",
            "Maganjo",
            "Nakyesanja",
            "Nansana",
            "Wamala"
        ];
        $address = [
            "Kasese, Uganda",
            "Kazo Nabweru, Uganda",
            "Maganjo, Uganda",
            "Nakyesanja, Uganda",
            "Nansana, Uganda",
            "Jinja, Uganda",
            "Mbale, Uganda",
            "Gulu, Uganda",
            "Arua, Uganda",
            "Kumi, Uganda",
            "Bushenyi, Uganda",
            "Wamala, Uganda"
        ];

        $status = [
            'Live',
            'Sick',
            'Died',
            'Slaugtered',
            'Slautered',
        ];
        $sex = [
            'Male',
            'Female',
        ];
        $type = [
            'Live',
            'Sick',
            'Died',
            'Slaugtered',
            'Slautered',
        ];
        $breed = Array(
            'Ankole' => "Ankole",
            'Short horn zebu' => "Short horn zebu",
            'Holstein' => "Holstein",
            'Other' => "Other"
        );
        $color = Array(
            'Black' => "Black",
            'Brown' => "Brown",
            'Black and white' => "Black and white",
            'Brown and white' => "Brown and white",
            'Mixed' => "Mixed",
        );
        $type = Array(
            'Cattle' => "Cattle",
            'Goat' => "Goat",
            'Sheep' => "Sheep"
        );
        //Parish::truncate();
        Animal::truncate();
        for ($i = 0; $i<10000;$i++) { 
            shuffle($type);  
            shuffle($type);  
            shuffle($breed); 
            shuffle($breed);
            shuffle($sex);
            shuffle($sex);
            shuffle($color);
            shuffle($status);

            Animal::create([
                'administrator_id' => $faker->numberBetween(7,414),
                'district_id' => $faker->numberBetween(1,112),
                'sub_county_id' => $faker->numberBetween(1,16),
                'parish_id' => $faker->numberBetween(1,22),
                'status' => $status[0],
                'type' => $type[0],
                'breed' => $breed[0],
                'sex' => $sex[0],
                'e_id' => rand(100000000,5100000000),
                'v_id' => rand(1000,51000),
                'lhc' => rand(1000,51000),
                'dob' => $faker->date,
                'color' => $color[0],
                'farm_id' => rand(1,500), 
            ]);
        }
    }
} 



 

