<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $faker = \Faker\Factory::create();
        for ($i = 0; $i < 50; $i++) {
            District::create([
                'name' => $faker->word,
                'detail' => $faker->paragraph,
            ]);
        }
    }
}
