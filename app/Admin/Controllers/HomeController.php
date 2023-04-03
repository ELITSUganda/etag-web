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
use App\Models\ArchivedAnimal;
use App\Models\BatchSession;
use App\Models\DrugCategory;
use App\Models\DrugForSale;
use App\Models\Event;
use App\Models\Farm;
use App\Models\FormDrugSeller;
use App\Models\Image;
use App\Models\Location;
use App\Models\Movement;
use App\Models\MyFaker;
use App\Models\SlaughterRecord;
use App\Models\SubCounty;
use App\Models\Utils;
use App\Models\VetServiceCategory;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        /* set_time_limit('-1');
        foreach (Animal::where([])->get() as $key => $a) {
            if(count($a->events) < 1){
                continue;
            }
            foreach ($a->events as $ev) {
                $ev->district_id = $a->district_id;
                $ev->sub_county_id = $a->sub_county_id;
                $ev->save();
                echo "==> $ev->id<hr>";
            }
        }
        dd("done"); */

        /* set_time_limit('-1');
        foreach (Event::where([])->get() as $key => $a) {
            if ($a->animal == null) {
                $a->delete();
                echo $a->id."<hr>";
                continue;
            }
            $a->import_file .= '1'; 
            $a->district_id = $a->animal->district_id;
            $a->sub_county_id = $a->animal->sub_county_id;
            echo ($a->e_id . "<hr>");
            $a->save();  
        } 
        die('done'); */
        $u = Admin::user();
        $content
            ->title('U-LITS - Dashboard')
            ->description('Hello ' . $u->name . "!");

        if ($u->isRole('slaughter')) {




            $content->row(function (Row $row) {
                $row->column(3, function (Column $column) {
                    $recs = AdminRoleUser::where(['user_id' => Auth::user()->id, 'role_id' => 5])->get();
                    $ids = [];
                    foreach ($recs as $rec) {
                        $ids[] = $rec->type_id;
                    }
                    $counts = Movement::where('destination_slaughter_house', $ids)->count();
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'Movement permits',
                        'sub_title' => 'Permits destined to this slaughter house',
                        'number' => number_format($counts),
                        'link' => admin_url('movements')
                    ]));
                });

                $row->column(3, function (Column $column) {
                    $recs = AdminRoleUser::where(['user_id' => Auth::user()->id, 'role_id' => 5])->get();
                    $ids = [];
                    foreach ($recs as $rec) {
                        $ids[] = $rec->type_id;
                    }
                    $counts = Animal::where('slaughter_house_id', $ids)->count();
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'Animals',
                        'sub_title' => 'Animals pending for slaughter',
                        'number' => number_format($counts),
                        'link' => admin_url('animals')
                    ]));
                });
                $row->column(3, function (Column $column) {
                    $counts = ArchivedAnimal::where([])->count();
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'Archived Animals',
                        'sub_title' => 'Animals slaughter history',
                        'number' => number_format($counts),
                        'link' => admin_url('slaughter-records')
                    ]));
                });
                $row->column(3, function (Column $column) {
                    $counts = SlaughterRecord::where([])->count();
                    $column->append(view('widgets.box-5', [
                        'is_dark' => true,
                        'title' => 'Slaughter Records',
                        'sub_title' => 'Create & manage Slaughter records',
                        'number' => number_format($counts),
                        'link' => admin_url('slaughter-records')
                    ]));
                });
            });

            return $content;
        }

        if ($u->isRole('dvo')) {


            $u = Auth::user();
            $r = AdminRoleUser::where(['user_id' => $u->id, 'role_id' => 7])->first();

            if ($r == null) {
                return 'District role not found.';
            }
            $dis = Location::find($r->type_id);
            if ($dis == null) {
                return 'District not found.';
            }
            $move = Movement::where(
                [
                    'district_from' => $dis->id,
                    'status' => 'pending'
                ]
            )->first();

            if ($move != null) {
                $content->row(function (Row $row) {
                    $row->column(12, function (Column $column) {

                        $move = Movement::where('status', 'pending')->first();
                        if ($move != null) {

                            $_content  = '';
                            $_content  = 'There is a movement permit application that is prending for verification.';
                            $_content .= '<p><a href="' . admin_url('movements/' . $move->id . '/edit') . '" class="btn btn-success  ">Review Application</a></p>';

                            if ($_content != "") {
                                $box = new Box('Confirm pending movement - form #' . $move->id, $_content);
                                $box->style('danger');
                                $box->solid();
                                $column->append($box);
                            }
                        }
                    });
                });
            }



            $content->row(function (Row $row) {
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::dvo_farms_widget());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::dvo_animals_widget());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::dvo_events_widget());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::dvo_movements_widget());
                });
            });

            $content->row(function (Row $row) {
                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::dvo_recent_animals());
                });

                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::dvo_recent_events());
                });

                $row->column(4, function (Column $column) {
                    $column->append(view('widgets.by-categories', []));
                });
            });

            return $content;
        }


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
            Admin::user()->isRole('maaif') ||
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
                $row->column(5, $box);

                $box = new Box('Events', view('admin.dashboard.chart-animal-status'));
                $box->removable();
                $box->collapsable();
                $box->style('success');
                $box->solid();
                $row->column(5, $box);
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
                    if (
                        $_ad->isRole('administrator') ||
                        $_ad->isRole('maaif') 
                    ) {
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
