<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;
use App\Models\AdminRoleUser;
use App\Models\Animal;
use App\Models\Farm;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Box;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        Admin::js('/vendor/laravel-admin-ext/chartjs/Chart.bundle.min.js');
        $content->title('Main Dashboard');

        $content->row(function ($row) {
            $box = new Box('Animal types', view('admin.dashboard.chart-animal-types'));
            $box->removable();
            $box->collapsable();
            $box->style('success');
            $box->solid();
            $row->column(6, $box);

            $box = new Box('Animal status', view('admin.dashboard.chart-animal-status'));
            $box->removable();
            $box->collapsable();
            $box->style('success');
            $box->solid();
            $row->column(6, $box);

        });

         

        $content->row(function ($row) {
            $row->column(4, new InfoBox(
                ''
                    . AdminRoleUser::where('role_id', 1)->count() . " Admins, "
                    . AdminRoleUser::where('role_id', 2)->count() . " Veterinaries, "
                    . AdminRoleUser::where('role_id', 3)->count() . " Farmers ",
                'All users',
                'green',
                admin_url('/auth/users'),
                Administrator::count() . " - Users"
            ));
            $row->column(4, new InfoBox(
                ''
                    . number_format(Farm::where('farm_type', 'Diary')->count()) . " Diary, "
                    . number_format(Farm::where('farm_type', 'Beef')->count()) . " Beef, "
                    . number_format(Farm::where('farm_type', 'Mixed')->count()) . " Mixed, ",
                'All farms',
                'green',
                admin_url('/farms'),
                Farm::count() . " - Farms"
            ));
            $row->column(4, new InfoBox(
                ''
                    . number_format(Farm::where('farm_type', 'Diary')->count()) . " Diary, "
                    . number_format(Farm::where('farm_type', 'Beef')->count()) . " Beef, "
                    . number_format(Farm::where('farm_type', 'Mixed')->count()) . " Mixed, ",
                'All animals',
                'green',
                admin_url('/animals'),
                number_format(Animal::count()) . " - Animals"
            ));
        });



        return $content;
    }
}
