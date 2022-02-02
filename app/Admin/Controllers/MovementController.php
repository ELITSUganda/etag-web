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
        if (isset($_GET['accept'])) {
            $delete = (int)(trim($_GET['accept']));
            if ($delete > 0) {
                $move = Movement::where('status', 'pending')->first();
                if ($move != null) {
                    foreach ($move->movement_has_movement_animals as $key => $ani) {

                        $e = new Event();
                        $e->administrator_id = $move->to_farm->administrator_id;
                        $e->district_id = $move->to_farm->district_id;
                        $e->sub_county_id = $move->to_farm->sub_county_id;
                        $e->parish_id = $move->to_farm->parish_id;
                        $e->farm_id = $move->to_farm->id;
                        $e->animal_id = $ani->movement_animal_id;
                        $e->type = "Moved";
                        $e->approved_by = $move->administrator_id;
                        $e->detail = "Animal moved from form  " . $move->to_farm->holding_code . " TO farm " . $move->from_farm->holding_code;

                        $new_animal = Animal::find($ani->movement_animal_id)->first();
                        if ($new_animal != null) {
                            $new_animal->administrator_id = $move->to_farm->administrator_id;
                            $new_animal->district_id = $move->to_farm->district_id;
                            $new_animal->sub_county_id = $move->to_farm->sub_county_id;
                            $new_animal->parish_id = $move->to_farm->parish_id;
                            $new_animal->farm_id = $move->to_farm->id;

                            $new_animal->save();
                        }
                    }

                    $move->status = "Completed";
                    $e->save();
                    $move->save();
                }
            }
            return redirect(admin_url('movements'));
        }

        if (isset($_GET['delete'])) {
            $delete = (int)(trim($_GET['delete']));
            if ($delete > 0) {
                $move = Movement::where('status', 'pending')->first();
                if ($move != null) {
                    $move->delete();
                }
            }
            return redirect(admin_url('movements'));
        }
        $grid = new Grid(new Movement());
        //admin_toastr('Message...', 'success');

        if (!Admin::user()->isRole('farmer')) {

            $grid->header(function ($query) {
                $move = Movement::where('status', 'pending')->first();
                if ($move != null) {

                    $content  = '';
                    if ($move->to == $move->from) {
                        $content = '<p>Invalid movement. You cannot move animals from one farm to same farm. You Either need to rectify this form or delete it.</p>';
                        $content .= '<p><a href="' . admin_url('movements/' . $move->id . '/edit') . '" class="btn btn-success  ">Rectify form</a></p>';
                        $content .= '<p><a href="' . admin_url('movements?delete=' . $move->id . '') . '" class="btn btn-danger  ">Delete form</a></p>';
                    } else {
                        if ($move->movement_has_movement_animals != null) {
                            $found = [];
                            $_found = [];
                            foreach ($move->movement_has_movement_animals as $key => $m) {
                                if (in_array($m->movement_animal_id, $found)) {
                                    $m->delete();
                                } else {
                                    $found[] = $m->movement_animal_id;
                                    $_found[] = $m;
                                }
                            }
                        }

                        $content .= "Following animals are going to be transfered from farm <b>" .
                            $move->from_farm->holding_code . " - By " . $move->from_farm->owner()->name . "</b> TO farm <b>" .
                            $move->to_farm->holding_code . " - By " . $move->to_farm->owner()->name . "</b>";
                        $content .= "<h4><u>Animals</u></h4><ol>";
                        foreach ($_found as $key => $animal) {
                            $content .= '<li> TAG ID: ' . $animal->animal->v_id . ', E-ID: ' . $animal->animal->e_id . '</li>';
                        }
                        $content .= "</ol>";

                        $content .= '<p><a href="' . admin_url('movements/' . $move->id . '/edit') . '" class="btn btn-primary  ">EDIT MOVEMENT</a></p>';
                        $content .= '<p><a href="' . admin_url('movements?delete=' . $move->id . '') . '" class="btn btn-danger  ">CACNEL MOVEMENT</a></p>';
                        $content .= '<p><a href="' . admin_url('movements?accept=' . $move->id . '') . '" class="btn btn-success  ">CONFIRM MOVEMENT</a></p>';
                    }

                    if ($content != "") {
                        $box = new Box('Confirm pending movement - form #' . $move->id, $content);
                        $box->style('danger');
                        $box->solid();
                        return $box;
                    }
                }
            });
        }

        $grid->column('created_at', __('Created'))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable();

        $grid->column('administrator_id', __('Applied By'))->display(function ($user) {
            $_user = Administrator::find($user);
            if (!$_user) {
                return "-";
            }
            return $_user->name;
        })->sortable();

        $grid->column('trader_name', __('Trader name'));
        $grid->column('trader_nin', __('Trader NIN'));
        $grid->column('transporter_name', __('Transporter name'));
        $grid->column('transporter_nin', __('Transporter NIN'));
        $grid->column('vehicle', __('Vehicle Reg. No.'));
        $grid->column('district_from', __('From district'))->display(function ($user) {
            $s = District::find($user);
            if (!$s) {
                return "-";
            }
            return $s->name;
        })->sortable();
        $grid->column('district_from', __('From district'))->display(function ($user) {
            $s = District::find($user);
            if (!$s) {
                return "-";
            }
            return $s->name;
        })->sortable();
        $grid->column('sub_county_from', __('From subcounty'))->display(function ($user) {
            $s = SubCounty::find($user);
            if (!$s) {
                return "-";
            }
            return $s->name;
        })->sortable();

        $grid->column('district_to', __('To district'))->display(function ($user) {
            $s = District::find($user);
            if (!$s) {
                return "-";
            }
            return $s->name;
        })->sortable();
        $grid->column('sub_county_to', __('To subcounty'))->display(function ($user) {
            $s = SubCounty::find($user);
            if (!$s) {
                return "-";
            }
            return $s->name;
        })->sortable();


        $grid->column('reason', __('Purpose.'));
        $grid->column('valid_to_Date', __('Valid until'));
        $grid->column('permit_Number', __('Permit Number.'));
        $grid->column('status', __('Permit status'));
        $grid->column('id', __('Print'))
            ->display(function ($f) {
                return '<a href="javascript:;">Print permit</a>';
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
        $form->setTitle("Applying for Livestock Movement Permit [LMP]");
        $form->hidden('<h4 style="padding: 0px!important; margin: 0px!important;">Teader\'s info.</h4>')->readonly();
        $form->hidden('administrator_id')->default(Admin::user()->id)->required();
        $form->text('trader_name', __('Trader \'s name'))->required();
        $form->text('trader_nin', __('Trader\'s NIN'))->required();
        $form->text('trader_phone', __('Trader\'s Phone no.'));
        $form->divider();
        $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Teader\'s desitination info.</h4>');
        $items = [];
        foreach (SubCounty::all() as $key => $f) {
            $items[$f->id] = $f->name . ", " . $f->district->name;
        }
        $form->select('sub_county_from', __('Subcounty from'))
            ->options($items)
            ->required();
        $form->text('village_from', __('Village from'))->required();
        $form->select('sub_county_to', __('Subcounty to'))
            ->options($items)
            ->required();
        $form->text('village_to', __('Village to'))->required();
        $form->text('transportation_route', __('Transportation route'))->required();

        $form->divider();
        $form->html('<h4 style="padding: 0px!important; margin: 0px!important;">Transpotation info.</h4>');
        $form->text('vehicle', __('Vehicle\'s reg\' no. '))->required();
        $form->text('transporter_name', __('Transporter name'))->required();
        $form->text('transporter_nin', __('Transporter\'s NIN'))->required();
        $form->text('transporter_Phone', __('transporter\'s Phone'));
        $form->divider();
        $form->textarea('reason', __('Movement purpose'))->required();


        $form->html('<h3>Click on "New" to Add animals to move.</h3>');

        $form->hasMany('movement_has_movement_animals', null, function (NestedForm $form) {
            $_items = [];

            if (!Admin::user()->isRole('farmer')) {
                foreach (Animal::where('administrator_id', '=', Admin::user()->id)->get()  as $key => $item) {
                    $_items[$item->id] = $item->e_id . " - " . $item->v_id;
                }
            } else {
                foreach (Animal::all()  as $key => $item) {
                    $_items[$item->id] = $item->e_id . " - " . $item->v_id;
                }
            }

            $form->select('movement_animal_id', 'Select animal')->options($_items)
                ->required();
        });


        return $form;
    }
}
