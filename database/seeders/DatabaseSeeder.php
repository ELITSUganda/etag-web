<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        // Seed admin menu entries for Butchery module
        if (class_exists(\Database\Seeders\AdminButcheryMenuSeeder::class)) {
            $this->call(\Database\Seeders\AdminButcheryMenuSeeder::class);
        }
    }
}
