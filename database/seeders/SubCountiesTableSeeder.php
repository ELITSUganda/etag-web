<?php

namespace Database\Seeders;

use App\Models\Parish;
use App\Models\SubCounty;
use Illuminate\Database\Seeder;

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

        //Parish::truncate();
        foreach ($names as $key => $value) {
            Parish::create([
                'name' => $value,
                'sub_county_id' => 10,
                'detail' => $faker->paragraph,
            ]);
        }
    }
}

