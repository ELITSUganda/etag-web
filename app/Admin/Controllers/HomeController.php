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
use App\Models\BatchSession;
use App\Models\Event;
use App\Models\Farm;
use App\Models\FormDrugSeller;
use App\Models\Movement;
use App\Models\MyFaker;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Box;



class HomeController extends Controller
{
    public function become_farmer(Content $content)
    {

        AdminRoleUser::where([
            'role_id' => 12,
            'user_id' => Admin::user()->id,
        ])->delete();


        AdminRoleUser::where([
            'role_id' => 3,
            'user_id' => Admin::user()->id,
        ])->delete();

        $role = new AdminRoleUser();
        $role->role_id = 3;
        $role->user_id = Admin::user()->id;
        $role->save();
        return redirect(admin_url('/'));
    }
    public function index(Content $content)
    {
        /*
        foreach (BatchSession::all() as $b) {
            Event::where([
                'session_id' => $b->id,
            ])
            ->where('id','>','3831')
            ->delete(); 
            $b->delete();
            
            # code...
        }
         \OneSignal::setParam('android_channel_id', 'f3469729-c2b4-4fce-89da-78550d5a2dd1')->sendNotificationToExternalUser(
            "Some Message",
            '777',
            $url = null,
            $data = null,
            $buttons = null,
            $schedule = null
        );
 */
  
        //MyFaker::makeEvents(3000);
        //die("as");  
        //MyFaker::makeAnimals(1000);

        $u = Admin::user();
        if ($u->isRole('farmer')) {

            $content
                ->title('U-LITS - Dashboard')
                ->description('Hello ' . $u->name . "!");

            $content->row(function (Row $row) {
                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::farmerSummary());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::farmerEvents());
                });
                $row->column(5, function (Column $column) {
                    $column->append(Dashboard::milkCollection());
                });
            });

            return $content;
        }

        if ($u->isRole('farmer')) {
            if (count($u->farms) < 1) {
                $content->row(function ($row) {
                    $row->column(
                        3,
                        view('admin.dashboard.component-wizard', [
                            'step' => "1",
                            'text' => "CREATE YOUR FIRST FARM",
                            'link' => admin_url('farms/create'),
                        ])
                    );
                });
                return $content;
            }
        }

        $f = FormDrugSeller::where('applicant_id', Admin::user()->id)->first();
        if ($f != null) {
            if ($f->status != 1) {
                return redirect(admin_url('form-drug-sellers'));
            }
        }

        if (Admin::user()->isRole('default')) {
            $content->row(function ($row) {
                $user =  Admin::user();
                $row->column(6, new Box(
                    null,
                    view('admin.dashboard.default-user')
                ));
            });
        }

        if (
            Admin::user()->isRole('administrator') ||
            Admin::user()->isRole('admin')
        ) {
            Admin::js('/vendor/laravel-admin-ext/chartjs/Chart.bundle.min.js');
            $content->title('Main Dashboard');

            $content->row(function ($row) {
                $box = new Box('Livestock Species', view('admin.dashboard.chart-animal-types'));
                $box->removable();
                $box->collapsable();
                $box->style('success');
                $box->solid();
                $row->column(6, $box);

                $box = new Box('Events', view('admin.dashboard.chart-animal-status'));
                $box->removable();
                $box->collapsable();
                $box->style('success');
                $box->solid();
                $row->column(6, $box);
            });
            $content->row(function ($row) {
                $admins = Administrator::all();

                $farmers_count = 0;
                $trader_count = 0;
                $administrator_count = 0;
                $veterinary_count = 0;
                $trader_count = 0;
                $slaughter_count = 0;
                $livestock_count = 0;
                foreach ($admins as $key => $_ad) {
                    if ($_ad->isRole('farmer')) {
                        $farmers_count++;
                    }
                    if ($_ad->isRole('trader')) {
                        $trader_count++;
                    }
                    if ($_ad->isRole('administrator')) {
                        $administrator_count++;
                    }
                    if ($_ad->isRole('veterinary')) {
                        $veterinary_count++;
                    }
                    if ($_ad->isRole('trader')) {
                        $trader_count++;
                    }
                    if ($_ad->isRole('slaughter')) {
                        $slaughter_count++;
                    }
                    if ($_ad->isRole('livestock-officer	')) {
                        $livestock_count++;
                    }
                }
                $row->column(4, new InfoBox(
                    ''
                        . "{$administrator_count} Admins, "
                        . "{$trader_count} veterinarians, "
                        . "{$trader_count} traders, "
                        . "{$slaughter_count} Slaughter houses, "
                        . "{$livestock_count} Livestock officers, "
                        . "{$farmers_count} Farmers.",
                    'All users',
                    'green',
                    admin_url('/auth/users'),
                    Administrator::count() . " - Users"
                ));
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Farm::where('farm_type', 'Dairy')->count()) . "Dairy,"
                        . number_format(Farm::where('farm_type', 'Beef')->count()) . " Beef,"
                        . number_format(Farm::where('farm_type', 'Mixed')->count()) . " Mixed, ",
                    'All farms',
                    'green',
                    admin_url('/farms'),
                    Farm::count() . " - Holdings"
                ));
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Farm::where('farm_type', 'Dairy')->count()) . " Dairy, "
                        . number_format(Farm::where('farm_type', 'Beef')->count()) . " Beef, "
                        . number_format(Farm::where('farm_type', 'Mixed')->count()) . " Mixed, ",
                    'All Livestock',
                    'green',
                    admin_url('/animals'),
                    number_format(Animal::count()) . " - Livestock"
                ));
            });
        }


        //Farmer
        if (Admin::user()->isRole('farmer')) {
            Admin::js('/vendor/laravel-admin-ext/chartjs/Chart.bundle.min.js');


            $content->title('Main Dashboard');




            $content->row(function ($row) {
                $user =  Admin::user();
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Farm::where('farm_type', 'Dairy')->where('administrator_id', $user->id)->count()) . " Dairy, "
                        . number_format(Farm::where('farm_type', 'Beef')->where('administrator_id', $user->id)->count()) . " Beef, "
                        . number_format(Farm::where('farm_type', 'Mixed')->where('administrator_id', $user->id)->count()) . " Mixed, ",
                    'All farms',
                    'green',
                    admin_url('/farms'),
                    Farm::where('administrator_id', $user->id)->count() . " - Farms"
                ));
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Animal::where('type', 'Cattle')->where('administrator_id', $user->id)->count()) . " Cattle, "
                        . number_format(Animal::where('type', 'Goat')->where('administrator_id', $user->id)->count()) . " Goat, "
                        . number_format(Animal::where('type', 'Sheep')->where('administrator_id', $user->id)->count()) . " Sheep, ",
                    'All animals',
                    'green',
                    admin_url('/animals'),
                    number_format(Animal::where('administrator_id', $user->id)->count()) . " - Animals"
                ));
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Movement::where('status', 'Pending')->where('administrator_id', $user->id)->count()) . " Pending, "
                        . number_format(Movement::where('status', 'Halted')->where('administrator_id', $user->id)->count()) . " Halted, "
                        . number_format(Movement::where('status', 'Rejected')->where('administrator_id', $user->id)->count()) . " Rejected ",
                    'All animals',
                    'green',
                    admin_url('/animals'),
                    number_format(Movement::where('administrator_id', $user->id)->count()) . " - Movement permits"
                ));
            });
        }


        if (Admin::user()->isRole('trader')) {

            Admin::js('/vendor/laravel-admin-ext/chartjs/Chart.bundle.min.js');
            $content->title('Main Dashboard');


            $content->row(function ($row) {
                $user =  Admin::user();
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Animal::where('type', 'Cattle')->where('trader', $user->id)->count()) . " Cattle, "
                        . number_format(Animal::where('type', 'Goat')->where('trader', $user->id)->count()) . " Goats, "
                        . number_format(Animal::where('type', 'Sheep')->where('trader', $user->id)->count()) . " Sheep, ",
                    'All animals',
                    'green',
                    admin_url('/sales'),
                    number_format(Animal::where('trader', $user->id)->count()) . " - Animals in stock"
                ));
                $row->column(4, new InfoBox(
                    ''
                        . number_format(Movement::where('status', 'Pending')->where('administrator_id', $user->id)->count()) . " Pending, "
                        . number_format(Movement::where('status', 'Halted')->where('administrator_id', $user->id)->count()) . " Halted, "
                        . number_format(Movement::where('status', 'Rejected')->where('administrator_id', $user->id)->count()) . " Rejected ",
                    'All animals',
                    'green',
                    admin_url('/movements'),
                    number_format(Movement::where('administrator_id', $user->id)->count()) . " - Movement permits"
                ));
            });
        }
        /*
	
id
created_at
updated_at

vehicle
reason
status
trader_nin
trader_name
trader_phone Ascending 1
transporter_name
transporter_nin
transporter_Phone
district_from
sub_county_from
village_from
district_to
sub_county_to
village_to
transportation_route
permit_Number
valid_from_Date
valid_to_Date
status_comment
destination
destination_slaughter_house
details
destination_farm
is_paid
paid_id
paid_method
*/

        return $content;
    }
}
