<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Encore\Admin\Auth\Database\Menu;

class AdminButcheryMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $prefix = config('admin.route.prefix', 'admin');

        $parent = Menu::firstOrCreate(
            ['title' => 'Butchery'],
            [
                'parent_id' => 0,
                'icon' => 'fa-cut',
                'uri' => '',
                'order' => 200,
            ]
        );

        Menu::firstOrCreate(
            ['title' => 'Dashboard', 'uri' => 'butchery-dashboard'],
            ['parent_id' => $parent->id, 'icon' => 'fa-dashboard', 'order' => 0]
        );

        Menu::firstOrCreate(
            ['title' => 'Slaughter Records', 'uri' => 'slaughter-records'],
            ['parent_id' => $parent->id, 'icon' => 'fa-slash', 'order' => 1]
        );

        Menu::firstOrCreate(
            ['title' => 'Slaughter Distributions', 'uri' => 'slaughter-distributions'],
            ['parent_id' => $parent->id, 'icon' => 'fa-paw', 'order' => 2]
        );

        Menu::firstOrCreate(
            ['title' => 'Slaughter Houses', 'uri' => 'slaughter-houses'],
            ['parent_id' => $parent->id, 'icon' => 'fa-building', 'order' => 3]
        );

        Menu::firstOrCreate(
            ['title' => 'Butcher Records', 'uri' => 'butcher-records'],
            ['parent_id' => $parent->id, 'icon' => 'fa-cut', 'order' => 4]
        );

        Menu::firstOrCreate(
            ['title' => 'Prime Cuts', 'uri' => 'butcher-records?cut_type=Prime Cut'],
            ['parent_id' => $parent->id, 'icon' => 'fa-certificate', 'order' => 5]
        );

        Menu::firstOrCreate(
            ['title' => 'Offal Cuts', 'uri' => 'butcher-records?cut_type=Offal Cut'],
            ['parent_id' => $parent->id, 'icon' => 'fa-heart', 'order' => 6]
        );
    }
}
