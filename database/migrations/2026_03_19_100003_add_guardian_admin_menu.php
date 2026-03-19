<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddGuardianAdminMenu extends Migration
{
    public function up()
    {
        DB::table('admin_menu')->insert([
            'parent_id'  => 0,
            'order'      => 999,
            'title'      => 'System Guardian',
            'icon'       => 'fa-shield',
            'uri'        => 'guardian',
            'permission' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('admin_menu')->where('uri', 'guardian')->delete();// Adjust the condition as needed to ensure only the intended menu item is removed
    }
}
