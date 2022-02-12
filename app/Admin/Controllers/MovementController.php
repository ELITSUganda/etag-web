<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use App\Models\District;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\MovementAnimal;
use App\Models\MovementHasMovementAnimal;
use App\Models\SubCounty;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Form\NestedForm;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovementController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'LM LMPR - (Permits)';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        /* $d =  Movement::find(16);
        $d->status = "Approveds";
        $d->save();  
        dd("time => "$d->status);*/


        $grid = new Grid(new Movement());

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        
        if (Admin::user()->isRole('slaughter')) {
            $grid->model()->where('destination_slaughter_house', '=', Admin::user()->id)
                ->where('status', '=', 'Approved');
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit(); 
            });
        } else if (Admin::user()->isRole('administrator') ||
         Admin::user()->isRole('veterinary') ||
         Admin::user()->isRole('livestock-officer')
        
        ) {
        } else {
            $grid->model()->where('administrator_id', '=', Admin::user()->id);
            $grid->actions(function ($actions) {
                $status = ((($actions->row['status'])));
                if (
                    $status == 'Pending' ||
                    $status == 'Halted'
                ) {
                } else {
                    $actions->disableDelete();
                    $actions->disableEdit();
                }
            });
        }





        if (Admin::user()->isRole('trader') || Admin::user()->isRole('farmer')) {
        } else {
            $grid->disableCreateButton();
        }
        //admin_toastr('Message...', 'success');



        if (Admin::user()->isRole('administrator')) {

            $grid->header(function ($query) {
                $move = Movement::where('status', 'pending')->first();
                if ($move != null) {

                    $content  = '';
                    $content  = 'There is a movement permit application that is prending for verification.';
                    $content .= '<p><a href="' . admin_url('movements/' . $move->id . '/edit') . '" class="btn btn-success  ">Review Application</a></p>';

                    if ($content != "") {
                        $box = new Box('Confirm pending movement - form #' . $move->id, $content);
                        $box->style('danger');
                        $box->solid();
                        return $box;
                    }
                }
            });
        }

        $grid->column('created_at', __('Date'))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable(); 
        $grid->column('destination', __('Destination'));
 

        $grid->column('trader_name', __('Applicant'));
        $grid->column('transporter_name', __('Transporter'));
        $grid->column('vehicle', __('Vehicle Reg. No.'));

        $grid->column('sub_county_to', __('To subcounty'))->display(function ($user) {
            $s = SubCounty::find($user);
            if (!$s) {
                return "-";
            }
            return $s->name;
        })->sortable();

        $grid->column('status', __('Permit status'))->display(function ($s) {
            if ($s == null) {
                return $s;
            } else if ($s == "Pending") {
                return '<span class="badge badge-warning" style="background-color: #f9c803; font-size: 18px; color: black; padding: 2px; border-radius: 8px;">Pending</span>';
            } else if ($s == "Approved") {
                return '<span class="badge badge-danger" style="background-color: #a2fc79; font-size: 18px; color: black; padding: 2px; border-radius: 8px;">Approved</span>';
            } else if ($s == "Halted") {
                return '<a class="badge bg-danger" style="background-color: #ff7777; font-size: 18px; color: black; padding: 2px; border-radius: 8px;"   border-radius: 8px;">Halted</span>';
            } else if ($s == "Rejected") {
                return '<span class="badge badge-danger" style="background-color: #ff7777; font-size: 18px; color: black; padding: 2px; border-radius: 8px;">Rejected</span>';
            }
            return $s;
        });
        $grid->column('id', __('Print'))
            ->display(function ($f) {
                return '<a target="_blank" href="' . url("print?id=" . $this->id) . '">Print permit using template #1 (MAIFF)</a>'
                .'<br><a target="_blank" href="' . url("print2?id=" . $this->id) . '">Print permit using template #2 (U-LITS)</a>';
            });



        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Movement::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created'));
        $show->field('administrator_id', __('Approved by'))->as(function ($user) {
            $_user = Administrator::find($user);
            if (!$_user) {
                return "-";
            }
            return $_user->name;
        })->sortable();
        $show->field('from', __('From'))->as(function ($id) {
            $item = Farm::find($id);
            if (!$item) {
                return "FARM ID: " . $id;
            }
            return $item->holding_code;
        })->sortable();

        $show->field('to', __('To'))->as(function ($id) {
            $item = Farm::find($id);
            if (!$item) {
                return "FARM ID: " . $id;
            }
            return $item->holding_code;
        })->sortable();
        $show->field('vehicle', __('Vehicle'));
        $show->field('reason', __('Reason'));
        /*
"id" => 2
"created_at" => "2022-01-14 22:02:51"
"updated_at" => "2022-01-14 22:02:51"
"administrator_id" => 196
"district_id" => 85
"sub_county_id" => 3
"parish_id" => 15
"status" => "Sick"
"type" => "Sheep"
"e_id" => "4626314747"
"v_id" => "31073"
"lhc" => "5193"
"breed" => "Ankole"
"sex" => "Male"
"dob" => "2001-11-18"
"color" => "Mixed"
"farm_id" => 93

admin_menu
Expand/CollapseStructureadmin_operation_log
Expand/CollapseStructureadmin_permissions
Expand/CollapseStructureadmin_roles
Expand/CollapseStructureadmin_role_menu
Expand/CollapseStructureadmin_role_permissions
Expand/CollapseStructureadmin_role_users
Expand/CollapseStructureadmin_users
Expand/CollapseStructureadmin_user_permissions
Expand/CollapseStructureanimals
Expand/CollapseStructurearchived_animals
Expand/CollapseStructurediseases
Expand/CollapseStructuredistricts
Expand/CollapseStructureevents
Expand/CollapseStructurefailed_jobs
Expand/CollapseStructurefarms
Expand/CollapseStructuremedicines
Expand/CollapseStructuremigrations
Expand/CollapseStructuremovements
Expand/CollapseStructuremovement_animals
Expand/CollapseStructuremovement_has_movement_animals
Expand/CollapseStructurepassword_resets
Expand/CollapseStructurepersonal_access_tokens
Expand/CollapseStructureslaughter_records
Expand/CollapseStructuresub_counties
Expand/CollapseStructureusers
Expand/CollapseStructurevaccines

*/




        $show->field('animals', __('Animals'))->unescape()->as(function ($a) {
            $headers = ['Id', 'E-ID', 'V-ID', 'Breed', 'SEX', 'Color', 'Details'];
            $rows = [];


            foreach ($this->movement_has_movement_animals as $key => $an) {
                $row = [];
                $row[] = $an->animal->id;
                $row[] = $an->animal->e_id;
                $row[] = $an->animal->v_id;
                $row[] = $an->animal->breed;
                $row[] = $an->animal->sex;
                $row[] = $an->animal->color;
                $row[] = '<a href="' . admin_url("animals/" . $an->animal->id) . '">Read More</a>';
                //dd($an->animal);
                $rows[] = $row;
            }
            $table = new Table($headers, $rows);
            return $table;
        });


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        /*

        $faker = \Faker\Factory::create();
        $m = new Movement();
        $m->administrator_id = 10;
        $m->reason = $faker->sentence(40);
        $m->village_from = $faker->word;
        $m->village_to = $faker->word;
        $m->transportation_route = $faker->sentence(5);
        $m->trader_nin = $faker->numberBetween(10000000000,1000000000000);
        $m->transporter_nin = $faker->numberBetween(10000000000,1000000000000);
        $m->vehicle = "UAB-".$faker->numberBetween(100,999);
        $m->trader_phone = "0782".$faker->numberBetween(99999,999999);
        $m->transporter_Phone = "0702".$faker->numberBetween(99999,999999);
        $m->sub_county_from = 2;
        $m->sub_county_to = 4;
        $m->trader_name = $faker->name;
        $m->save();*/
        /*

district_from
district_to
permit_Number
status
        */


        $form = new Form(new Movement());


        $u =   Admin::user();

        $form->setTitle("Applying for Livestock Movement Permit [LMP]");
        $form->hidden('<h4 style="padding: 0px!important; margin: 0px!important;">Teader\'s info.</h4>')->readonly();
        $form->hidden('administrator_id')->default(Admin::user()->id)->required();
        $form->text('trader_name', __('Trader \'s name'))->default($u->name)->readonly()->required();
        $form->text('trader_nin', __('Trader\'s NIN'))->default($u->nin)->readonly()->required();
        $form->text('trader_phone', __('Trader\'s Phone no.'))->default($u->phone_number)->readonly();



        $form->divider();


        if (
            Admin::user()->isRole('trader') ||
            Admin::user()->isRole('farmer')
        ) {

            $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Animals\' departure info. <b>(FROM)</b></h4>');
            $items = [];
            foreach (SubCounty::all() as $key => $f) {
                $items[$f->id] = $f->name . ", " . $f->district->name;
            }
            $form->select('sub_county_from', __('Subcounty from'))
                ->options($items)
                ->required();
            $form->text('village_from', __('Village from'))->required();
            $form->divider();
            $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Animals\' destination info. <b>(TO)</b></h4>');
            $form->radio('destination', __('Destination of movement'))
                ->options(array(
                    'To farm' => 'To another farm',
                    'To slaughter' => 'To slaughter house',
                    'Other' => 'Other',

                ))
                ->required()
                ->when('To farm', function (Form $form) {
                    $farms = [];
                    foreach (Farm::all() as $key => $f) {
                        $farms[$f->id] = $f->holding_code . "";
                    }
                    $form->select('destination_farm', __('Select Farm'))
                        ->options($farms);
                })
                ->when('To slaughter', function (Form $form) {
                    $farms = Administrator::all();
                    $_farms = [];
                    foreach ($farms as $key => $f) {
                        if (!$f->isRole("slaughter")) {
                            continue;
                        }
                        $_farms[$f->id] = $f->name . " - " . $f->id;
                    }

                    $form->select('destination_slaughter_house', __('Select slaughter house'))
                        ->options($_farms)
                        ->help('Please select slaughter house');
                })
                ->when('Other', function (Form $form) {
                    $items = [];
                    foreach (SubCounty::all() as $key => $f) {
                        $items[$f->id] = $f->name . ", " . $f->district->name;
                    }
                    $form->text('reason', __('Specify purpose of movement'));
                    $form->select('sub_county_to', __('Subcounty to'))
                        ->options($items);
                    $form->text('village_to', __('Village to'));
                });
            $form->text('transportation_route', __('Transportation route'))->required();


            $form->divider();
            $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Transpotation info.</h4>');
            $form->text('vehicle', __('Vehicle\'s reg\' no. '))->required();
            $form->text('transporter_name', __('Transporter name'))->required();
            $form->text('transporter_nin', __('Transporter\'s NIN'))->required();
            $form->text('transporter_Phone', __('transporter\'s Phone'));
            $form->divider();
            $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Payment info.</h4>');

            $form->radio('is_paid', __('Payment status'))
                ->options(array(
                    'Paid' => 'Paid',
                    'Not paid' => 'Not paid',
                ))
                ->required()
                ->when('Paid', function (Form $form) {
                    $form->radio('paid_method', __('Payment method'))
                        ->options(array(
                            'Mobile money' => 'Mobile money',
                            'Bank' => 'Bank',
                            'Cash' => 'Cash',
                        ));
                    $form->text('paid_id', __('Transaction ID'));
                });
            $form->divider();


            $form->textarea('details', __('Movement details'));


            $form->disableEditingCheck();
            $form->disableViewCheck();
            $form->disableCreatingCheck();


            $form->html('<h3>Click on "New" to Add animals to move.</h3>');
        } else if (Admin::user()->isRole('administrator')) {

            $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Animals\' departure info. <b>(FROM)</b></h4>');
            $items = [];
            foreach (SubCounty::all() as $key => $f) {
                $items[$f->id] = $f->name . ", " . $f->district->name;
            }
            $form->select('sub_county_from', __('Subcounty from'))
                ->options($items)
                ->readOnly();
            $form->text('village_from', __('Village from'))
                ->readOnly();
            $form->divider();
            $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Animals\' destination info. <b>(TO)</b></h4>');
            $form->radio('destination', __('Destination of movement'))
                ->options(array(
                    'To farm' => 'To another farm',
                    'To slaughter' => 'To slaughter house',
                    'Other' => 'Other',

                ))
                ->readOnly()
                ->when('To farm', function (Form $form) {
                    $farms = [];
                    foreach (Farm::all() as $key => $f) {
                        $farms[$f->id] = $f->holding_code . "";
                    }
                    $form->select('destination_farm', __('Select Farm'))
                        ->options($farms)
                        ->readOnly()
                        ->readOnly();
                })
                ->when('To slaughter', function (Form $form) {
                    $farms = Administrator::all();
                    $_farms = [];
                    foreach ($farms as $key => $f) {
                        if (!$f->isRole("slaughter")) {
                            continue;
                        }
                        $_farms[$f->id] = $f->name . " - " . $f->id;
                    }

                    $form->select('destination_slaughter_house', __('Select slaughter house'))
                        ->options($_farms)
                        ->help('Please select slaughter house')
                        ->readOnly()
                        ->readOnly();
                })
                ->when('Other', function (Form $form) {
                    $items = [];
                    foreach (SubCounty::all() as $key => $f) {
                        $items[$f->id] = $f->name . ", " . $f->district->name;
                    }
                    $form->text('reason', __('Specify purpose of movement'))
                        ->readOnly()
                        ->readOnly();
                    $form->select('sub_county_to', __('Subcounty to'))
                        ->options($items)
                        ->readOnly()
                        ->readOnly();
                    $form->text('village_to', __('Village to'))
                        ->readOnly()
                        ->readOnly();
                });
            $form->text('transportation_route', __('Transportation route'))
                ->readOnly()
                ->readOnly();


            $form->divider();
            $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Transpotation info.</h4>');
            $form->text('vehicle', __('Vehicle\'s reg\' no. '))
                ->readOnly()
                ->readOnly();
            $form->text('transporter_name', __('Transporter name'))
                ->readOnly()
                ->readOnly();
            $form->text('transporter_nin', __('Transporter\'s NIN'))
                ->readOnly()
                ->readOnly();
            $form->text('transporter_Phone', __('transporter\'s Phone'));
            $form->divider();
            $form->textarea('details', __('Movement details'))
                ->readOnly()
                ->readOnly();
            $form->html('<h3>Animals</h3>');
        }

        $form->hasMany('movement_has_movement_animals', null, function (NestedForm $form) {
            $_items = [];

            if (
                Admin::user()->isRole('administrator') ||
                Admin::user()->isRole('livestock-officer') 
            
            ) {
                foreach (Animal::all()  as $key => $item) {
                    $_items[$item->id] = $item->e_id . " - " . $item->v_id;
                }
            } else if (Admin::user()->isRole('trader')) {
                foreach (Animal::where('trader', '=', Admin::user()->id)->get()  as $key => $item) {
                    $_items[$item->id] = $item->e_id . " - " . $item->v_id;
                }
            } else if (Admin::user()->isRole('farmer')) {
                foreach (Animal::where('administrator_id', '=', Admin::user()->id)
                    ->where('status', '!=', 'sold')
                    ->where('trader', '<', 1)
                    ->get()  as $key => $item) {
                    $_items[$item->id] = $item->e_id . " - " . $item->v_id;
                }
            }

            $form->select('movement_animal_id', 'Select animal')->options($_items)
                ->required();
        });



        if (Admin::user()->isRole('administrator')) {
            $form->divider();
            $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Payment info.</h4>');

            $form->radio('is_paid', __('Payment status'))
                ->options(array(
                    'Paid' => 'Paid',
                    'Not paid' => 'Not paid',
                ))
                ->required()
                ->when('Paid', function (Form $form) {
                    $form->radio('paid_method', __('Payment method'))
                        ->options(array(
                            'Mobile money' => 'Mobile money',
                            'Bank' => 'Bank',
                            'Cash' => 'Cash',
                        ))
                        ->required();
                    $form->text('paid_id', __('Transaction ID'))->required();
                });

            $form->divider();
            $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Review permit.</h4>');

            $form->radio('status', __('Review permit'))
                ->options(array(
                    'Approved' => 'Approve',
                    'Halted' => 'Halt',
                    'Rejected' => 'Reject',

                ))
                ->required()
                ->when('Halted', function (Form $form) {
                    $form->textarea('details', __('Reason for halt'));
                })
                ->when('Rejected', function (Form $form) {
                    $form->textarea('details', __('Reason for rejection'));
                })
                ->when('Approved', function (Form $form) {

                    $form->date('valid_from_Date', __('Valid from Date'));
                    $form->date('valid_to_Date', __('Valid until'));
                });
        }



        return $form;
    }
}
