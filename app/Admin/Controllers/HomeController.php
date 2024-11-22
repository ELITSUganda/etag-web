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
use App\Models\Application;
use App\Models\ApplicationType;
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
use App\Models\WholesaleDrugStock;
use App\Models\WholesaleOrder;
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

    public function maaif_dashboard(Content $content)
    {

        $content->row(function ($row) {

            $row->column(3, function (Column $column) {
                $u = Admin::user();
                $conds = ['stage' => 'Pending'];
                if (!$u->isRole('maaif')) {
                    $conds['applicant_id'] = $u->id;
                }
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => strtoupper('Pending for review'),
                    'sub_title' => 'New applications not yet reviewed',
                    'number' => number_format(Application::where($conds)->count()),
                    'link' => admin_url('pending-applications')
                ]));
            });

            $row->column(3, function (Column $column) {
                $u = Admin::user();
                $conds = ['stage' => 'Inspection'];
                if (!$u->isRole('maaif')) {
                    $conds['applicant_id'] = $u->id;
                }
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => strtoupper('Under Inspection'),
                    'sub_title' => 'Applications under inspection',
                    'number' => number_format(Application::where($conds)->count()),
                    'link' => admin_url('inspection-applications')
                ]));
            });
            $row->column(3, function (Column $column) {
                $u = Admin::user();
                $conds = ['stage' => 'Payment'];
                if (!$u->isRole('maaif')) {
                    $conds['applicant_id'] = $u->id;
                }
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => strtoupper('Payment Stage'),
                    'sub_title' => 'Applications pending for payment',
                    'number' => number_format(Application::where($conds)->count()),
                    'link' => admin_url('payment-applications')
                ]));
            });


            $row->column(3, function (Column $column) {
                $u = Admin::user();
                $conds = ['stage' => 'Approved'];
                if (!$u->isRole('maaif')) {
                    $conds['applicant_id'] = $u->id;
                }
                $column->append(view('widgets.box-5', [
                    'is_dark' => true,
                    'title' => strtoupper('Approved Applications'),
                    'sub_title' => 'Completed applications.',
                    'number' => number_format(Application::where($conds)->count()),
                    'link' => admin_url('approved-applications')
                ]));
            });
        });
        return $content; 
        Admin::js('/vendor/laravel-admin-ext/chartjs/Chart.bundle.min.js');
        $content->title('Main Dashboard');

        $content->row(function (Row $row) {
            $u = Admin::user();
            $row->column(6, Dashboard::to_districts($u));
            $row->column(6, Dashboard::animals_by_farms($u));
        });

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
        return $content;
    }
    public function index(Content $content)
    {
        $u = Admin::user();
        $isApplicant = false;
        $isAdmin = false;
        if (Utils::is_maaif()) {

            return $this->maaif_dashboard($content);
            if ($u->isRole('applicant')) {
                $isApplicant = true;
            } else {
                //assign applicant role
                Utils::assign_role($u->id, 'applicant');
            }

            $forms = ApplicationType::where([])->orderBy('name', 'asc')->get();



            if (!$u->isRole('maaif')) {
                $isAdmin = true;

                $myApplciations = Application::where([
                    'applicant_id' => $u->id
                ])->get();

                if ($myApplciations->count() > 0) {
                    $content
                        ->title('My Applications');
                    $content
                        ->view("widgets.maaf-application-dashboard", [
                            'applications' => $myApplciations
                        ]);
                    return $content;
                } else {
                    $content
                        ->title('MAAIF APPLICATION FORMS');
                    $content
                        ->view("widgets.maaf-application-forms", [
                            'forms' => $forms
                        ]);
                }

                return $content;
            }
        }

        $content
            ->title('U-LITS - Dashboard')
            /* ->description('Hello ' . $u->name . "!" . " - " . Carbon::now()->format('l jS \\of F Y h:i:s A')) */;;


        if ($u->isRole('drugs-wholesaler')) {
            $content->row(function (Row $row) {

                $row->column(3, function (Column $column) {
                    $counts = WholesaleOrder::where(
                        'status',
                        '!=',
                        'Completed'
                    )->where(
                        'status',
                        '!=',
                        'Canceled'
                    )
                        ->where('supplier_id', Auth::user()->id)
                        ->count();
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'New Orders',
                        'sub_title' => 'Recently placed orders',
                        'number' => number_format($counts),
                        'link' => admin_url('wholesale-orders')
                    ]));
                });
                $row->column(3, function (Column $column) {
                    $counts = WholesaleOrder::where(
                        'status',
                        'Completed'
                    )
                        ->where('supplier_id', Auth::user()->id)
                        ->count();
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'My Sales',
                        'sub_title' => 'Orders completed successfully.',
                        'number' => number_format($counts),
                        'link' => admin_url('wholesale-orders')
                    ]));
                });
                $row->column(3, function (Column $column) {

                    $counts = WholesaleDrugStock::where('status', 'Pending')
                        ->where('administrator_id', Auth::user()->id)
                        ->count();
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'Stock under review',
                        'sub_title' => 'Drugs under review by NDA',
                        'number' => number_format($counts),
                        'link' => admin_url('wholesale-drug-stocks')
                    ]));
                });
                $row->column(3, function (Column $column) {

                    $counts = WholesaleDrugStock::where('status', 'Pending')
                        ->where('current_quantity', '>', 0)
                        ->where('status', 'Approved')
                        ->where('administrator_id', Auth::user()->id)
                        ->count();
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => 'My Stock',
                        'sub_title' => 'Drugs in stock',
                        'number' => number_format($counts),
                        'link' => admin_url('wholesale-drug-stocks')
                    ]));
                });


                /*  $row->column(3, function (Column $column) {
                    $counts = SlaughterRecord::where([])->count();
                    $column->append(view('widgets.box-5', [
                        'is_dark' => true,
                        'title' => 'Slaughter Records',
                        'sub_title' => 'Create & manage Slaughter records',
                        'number' => number_format($counts),
                        'link' => admin_url('slaughter-records')
                    ]));
                }); */
            });
        }



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

        $u = Admin::user();
        $r = AdminRoleUser::where([
            'user_id' => $u->id,
            'role_id' => 7,
            'role_type' => 'dvo'
        ])->first();
        $dis = null;
        if ($r != null) {
            $dis = Location::find($r->type_id);
        }

        if ($u->isRole('dvo') && ($r != null) && ($dis != null)) {


            if ($r == null) {
                return 'District role not found.';
            }

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
                /* ->description('Hello ' . $u->name . "!" . " - " . Carbon::now()->format('l jS \\of F Y h:i:s A')) */;

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
            Admin::user()->isRole('data-viewer') ||
            Admin::user()->isRole('admin')
        ) {
            $content->row(function ($row) {
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::maaif_permits_widget());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::maaif_livestock_permits_widget());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::maaif_fams_permits_widget());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::maaif_users_widget());
                });
            });
            Admin::js('/vendor/laravel-admin-ext/chartjs/Chart.bundle.min.js');
            $content->title('Main Dashboard');

            $content->row(function (Row $row) {
                $u = Admin::user();
                $row->column(6, Dashboard::to_districts($u));
                $row->column(6, Dashboard::animals_by_farms($u));
            });

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

        return $content;
    }


    public function maaf_application_forms(Content $content)
    {
        $u = Admin::user();
        $forms = ApplicationType::where([])->orderBy('name', 'asc')->get();
        $content
            ->title('MAAIF APPLICATION FORMS');
        $content
            ->view("widgets.maaf-application-forms", [
                'forms' => $forms
            ]);
        return $content;

        $content->row(function ($row) {


            $row->$row->column(3, function (Column $column) {
                // global access for $form
                $forms = ApplicationType::where([])->orderBy('name', 'asc')->get();
                foreach ($forms as $key => $app_form) {
                    $column->append(view('widgets.box-5', [
                        'is_dark' => false,
                        'title' => $app_form->name,
                        'sub_title' => 'Total number of cattle vaccinated in last 6 months ago.',
                        'number' => number_format(Animal::where([
                            'type' => 'Cattle',
                            'genetic_donor' => 'Not Vaccinated'
                        ])->count()),
                        'link' => admin_url('vaccination-stats')
                    ]));
                }
            });
        });
        return $content;
    }

    public function vaccination(Content $content)
    {
        $u = Admin::user();
        $content
            ->title('U-LITS - Dashboard');



        $content->row(function ($row) {
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Vaccinated Cattle',
                    'sub_title' => 'Total number of cattle vaccinated in last 6 months ago.',
                    'number' => number_format(Animal::where([
                        'type' => 'Cattle',
                        'genetic_donor' => 'Not Vaccinated'
                    ])->count()),
                    'link' => admin_url('vaccination-stats')
                ]));
            });

            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Pending For Vaccination',
                    'sub_title' => 'Total number of cattle pending for vaccination.',
                    'number' => number_format(Animal::where([
                        'type' => 'Cattle',
                        'genetic_donor' => 'Pending for vaccination'
                    ])->count()),
                    'link' => admin_url('vaccination-stats')
                ]));
            });

            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Vaccinated Cattle',
                    'sub_title' => 'Total number of cattle pending for vaccination.',
                    'number' => number_format(Animal::where([
                        'type' => 'Cattle',
                        'genetic_donor' => 'Vaccinated'
                    ])->count()),
                    'link' => admin_url('vaccination-stats')
                ]));
            });

            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => true,
                    'title' => 'Registered Cattle',
                    'sub_title' => 'Total number of all cattle registered.',
                    'number' => number_format(Animal::where([
                        'type' => 'Cattle',
                    ])->count()),
                    'link' => admin_url('vaccination-stats')
                ]));
            });
        });
        return $content;
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
        return $content;
    }
}
