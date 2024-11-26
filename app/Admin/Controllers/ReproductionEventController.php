<?php

namespace App\Admin\Controllers;

use App\Models\AdminRoleUser;
use App\Models\Animal;
use App\Models\Disease;
use App\Models\District;
use App\Models\DrugStockBatch;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Location;
use App\Models\Medicine;
use App\Models\SubCounty;
use App\Models\User;
use App\Models\Utils;
use App\Models\Vaccine;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Faker\Factory as Faker;

class ReproductionEventController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Reproduction Events';

    protected function title()
    {
        $t = 'Reproduction Events';
        $segs = request()->segments();
        if (in_array('pregnancy-events', $segs)) {
            $t = 'Pregnancy Events';
        } else if (in_array('abortion-events', $segs)) {
            $t = 'Abortion Events';
        } else if (in_array('calving-events', $segs)) {
            $t = 'Calving Events';
        } else if (in_array('weaning-events', $segs)) {
            $t = 'Weaning Events';
        }
        return $t;
    }

    /**
     * Make a grid builder.
     

     * @return Grid
     */
    protected function grid()
    {



        //Utils::display_alert_message();
        $grid = new Grid(new Event());
        $grid->disableBatchActions();

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $u = Admin::user();
        $conds = [];

        if (!$u->isRole('administrator')) {
            $conds['administrator_id'] = $u->id;
        }
        $segs = request()->segments();

        if (in_array('pregnancy-events', $segs)) {
            $conds['type'] = 'Pregnancy check';
        } else if (in_array('abortion-events', $segs)) {
            $conds['type'] = 'Abortion';
        } else if (in_array('calving-events', $segs)) {
            $conds['type'] = 'Calving';
        } else if (in_array('weaning-events', $segs)) {
            $conds['type'] = 'Weaning';
        }

        $grid->model()
            ->where($conds)
            ->orderBy('id', 'DESC');
        $grid->filter(function ($filter) {

            $admins = [];

            $animals = [];
            $u = Auth::user();
            foreach (
                Animal::where([
                    'administrator_id' => $u->id
                ])->get() as $key => $v
            ) {
                $animals[$v->id] = $v->e_id . " - " . $v->v_id;
            }

            $filter->equal('administrator_id', 'Filter by farm owner')->select(function ($id) {
                $a = User::find($id);
                if ($a) {
                    return [$a->id => $a->name];
                }
            })
                ->ajax(
                    url('api/ajax-users')
                );


            $filter->equal('type', "Animal type")->select(array(
                'Cattle' => "Cattle",
                'Goat' => "Goat",
                'Sheep' => "Sheep"
            ));

            $filter->equal('district_id', 'Filter by district')->select(function ($id) {
                $a = Location::find($id);
                if ($a) {
                    return [$a->id => $a->name_text];
                }
            })
                ->ajax(
                    url('/api/ajax?'
                        . "&search_by_1=name"
                        . "&search_by_2=id"
                        . "&query_parent=0"
                        . "&model=Location")
                );

            $filter->equal('sub_county_id', 'Filter by sub-county')->select(function ($id) {
                $a = Location::find($id);
                if ($a) {
                    return [$a->id => $a->name_text];
                }
            })
                ->ajax(
                    url('/api/sub-counties')
                );

            $filter->like('animal_id', "Animal")->select($animals);
            $filter->equal('type', "Event type")->select(array(
                'Milking' => 'Milking',
                'Disease test' => 'Disease',
                'Drug' => 'Teatment',
                'Vaccination' => 'Vaccination',
                'Pregnancy' => 'Pregnancy test',
                'Death' => 'Death',
                'Slaughter' => 'Slaughter',
                'Other' => 'Other',

            ));
            $filter->equal('disease_id', "Event type")->select(
                Disease::all()->pluck('name', 'name')
            );
            $filter->equal('medicine_id', "Drug")->select(
                Medicine::all()->pluck('name', 'name')
            );
            $filter->equal('vaccine_id', "Vaccine")->select(
                Vaccine::all()->pluck('name', 'name')
            );

            $filter->between('created_at', 'Created between')->date();
        });


        $grid->column('id', __('ID'))->sortable()->hide();
        $grid->column('created_at', __('Date'))
            ->display(function ($f) {
                return Utils::my_date($f);
            })->sortable();
        $grid->column('animal_id', __('Animal (V-ID)'))
            ->display(function ($id) {
                $u = Animal::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->v_id;
            })->sortable();
        $grid->column('type', __('Event'))->sortable();

        /* pregnancy-events */
        if (in_array('weaning-events', $segs)) {
            //wean_date
            $grid->column('wean_date', __('Weaning date'))->sortable()
                ->display(function ($f) {
                    return Utils::my_date($f);
                });
            //wean_weight   
            $grid->column('wean_weight', __('Weaning weight (in KGs)'))->sortable();
            //vaccination
            $grid->column('vaccination', __('Age at weaning (in Days)'))->sortable();
        } elseif (in_array('calving-events', $segs)) {

            //calf_id
            $grid->column('calf_id', __('Calf ID'))
                ->display(function ($id) {
                    $cal = Animal::find($id);
                    if ($cal) {
                        return $cal->v_id;
                    }
                    return $id;
                })->sortable();

            $grid->column('calving_date', __('Calving date'))->sortable()
                ->display(function ($f) {
                    return Utils::my_date($f);
                });
            $grid->column('calf_sex', __('Calf sex'))->sortable();
            //calving_weight
            $grid->column('calf_weight', __('Calving Weight at Birth (in KGs)'))->sortable();
            //wean_milk
            $grid->column('wean_milk', __('Milk quantity (in Litters)'))->sortable();
            //calf_sex
            //calf
        } else
        if (in_array('abortion-events', $segs)) {
            //wean_date
            $grid->column('wean_date', __('Date of abortion'))->sortable()
                ->display(function ($f) {
                    return Utils::my_date($f);
                });
            //wean_weight
            $grid->column('wean_weight', __('Pregnancy  age (in Days)'))->sortable();

            //calf_sex
            $grid->column('calf_sex', __('Aborted fetus sex'))->sortable();
            $grid->column('inseminator', __('Cause for abortion'))->sortable();
        } else if (in_array('pregnancy-events', $segs)) {
            $grid->column('is_present', __('Pregnancy Check Results'))->sortable()
                ->label([
                    'Pregnant' => 'success',
                    'Not Pregnant' => 'danger',
                ]);
            $grid->column('service_type', __('Fertilization method'))->sortable()
                ->dot([
                    'Artificial insemination' => 'success',
                    'Natural breeding' => 'info',
                ]);
            //service_date
            $grid->column('service_date', __('Service date'))->sortable()
                ->display(function ($f) {
                    return Utils::my_date($f);
                });
            //inseminator
            $grid->column('inseminator', __('Inseminator'))
                ->display(function ($f) {
                    if ($f == null || strlen($f) < 1) {
                        return "-";
                    }
                    return $f;
                })->sortable();
            //simen_code
            $grid->column('simen_code', __('Simen code'))
                ->display(function ($f) {
                    if ($f == null || strlen($f) < 1) {
                        return "-";
                    }
                    return $f;
                })->sortable();
            //expected sex
            $grid->column('medicine_name', __('Expected sex'))
                ->display(function ($f) {
                    if ($f == null || strlen($f) < 1) {
                        return "-";
                    }
                    return $f;
                })->sortable();
        }


        $grid->column('detail', __('Notes'))->sortable();
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
        $show = new Show(Event::findOrFail($id));

        $show = new Show(Farm::findOrFail($id));
        if (Admin::user()->isRole('farmer')) {
            $show->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableDelete();
                });
        }


        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('farm_id', __('Farm id'));
        $show->field('animal_id', __('Animal id'));
        $show->field('type', __('Type'));
        $show->field('approved_by', __('Approved by'));
        $show->field('detail', __('Detail'));

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
        die("sone"); */


        /*    $ids = [];
        foreach (Animal::where([  
            'administrator_id' => Auth::user()->id
        ])->get() as $k => $a) {
            $ids[] = $a->id;
        }

        $faker = Faker::create();
        for ($x = 1; $x < 25; $x++) {
            $e = new Event();
            $e->animal_id = $ids[rand(1,3)];
            $e->created_at = $faker->dateTimeBetween('-1 year');
            $e->type = "Milking";
            $e->milk = rand(1, 15);
           try {
            $e->save();
           } catch (\Throwable $th) {
            //throw $th;
           }
            echo $x."<br>";
        }  
        dd("done");
 */
        Utils::display_alert_message();
        $form = new Form(new Event());

        $u = Admin::user();
        $form->hidden('administrator_id', __('Administrator id'))->default($u->id);
        $form->hidden('district_id', __('District id'))->default(1);
        $form->hidden('sub_county_id', __('Sub county id'))->default(1);
        $form->hidden('is_batch_import', __('Sub county id'))->default(0);
        $form->hidden('farm_id', __('Farm id'))->default(1);




        $form->select('animal_id', 'Select Animal')
            ->options(function ($id) {
                $parent = Animal::find($id);
                if ($parent != null) {
                    return [$parent->id =>  $parent->v_id . " - " . $parent->e_id];
                }
            })
            ->rules('required')
            ->ajax(
                url('/api/ajax-animals?'
                    . "&administrator_id={$u->id}")
            )->rules('required');

        $form->divider();
        $form->radio('type', __('Event type'))
            ->options(array(
                'Milking' => 'Milking',
                'Weight check' => 'Weight check',
                'Disease test' => 'Disease test',
                'Treatment' => 'Treatment',
                'Vaccination' => 'Vaccination',
                'Pregnancy check' => 'Pregnancy check',
                'Temperature check' => 'Temperature check',
                'Stolen' => 'Stolen',
                'Home slaughter' => 'Home slaughter',
                'Photo' => 'Photo',
                'Death' => 'Death',


            ))
            ->rules('required')
            ->when('Weight check', function (Form $form) {
                $form->decimal('weight', 'Weight value')
                    ->help('in Killograms (KGs)')
                    ->rules('required');
            })
            ->when('Photo', function (Form $form) {
                $form->image('photo', 'Attach photo');
            })
            ->when('Milking', function (Form $form) {
                $form->decimal('milk', 'Milk quantity')
                    ->help('in litters')
                    ->rules('required');
            })
            ->when('Temperature check', function (Form $form) {
                $form->decimal('temperature', 'Temperature value')
                    ->help('in degrees Celsius (°C)')
                    ->rules('required');
            })
            ->when('Pregnancy check', function (Form $form) {
                $form->radio('pregnancy_check_method', __(
                    'Pregnancy check method used'
                ))
                    ->options(array(
                        'Palpation' => 'Palpation',
                        'Ultrasound' => 'Ultrasound',
                        'Observation' => 'Observation',
                        'Blood' => 'Blood',
                        'Urine' => 'Urine',
                    ))
                    ->rules('required');

                $form->radio('pregnancy_check_results', __(
                    'Pregnancy check results'
                ))
                    ->options(array(
                        'Pregnant' => 'Is pregnant',
                        'Not Pregnant' => 'Is not pregnant',
                    ))
                    ->when('Pregnant', function (Form $form) {
                        $form->radio('pregnancy_fertilization_method', __(
                            'Pregnancy fertilization method used'
                        ))
                            ->options(array(
                                'Artificial insemination' => 'Artificial insemination',
                                'Natural breeding' => 'Natural breeding',
                            ))
                            ->rules('required');
                        $form->radio('pregnancy_expected_sex', __(
                            'Expected calf sex'
                        ))
                            ->options(array(
                                'Male' => 'Bull',
                                'Heifer' => 'Heifer',
                                'Unknown' => 'Unknown',
                            ))
                            ->rules('required');
                    })
                    ->rules('required');
            })
            ->when('Disease test', function (Form $form) {
                $form->select('disease_id', __('Select disease'))
                    ->options(Disease::all()->pluck('name', 'id'))
                    ->rules('required');
                $form->radio('disease_test_results', __(
                    'Disease test results'
                ))
                    ->options(array(
                        'Positive' => 'Positive (Has the disease)',
                        'Negative' => 'Negative (Does not have the disease)',
                    ))
                    ->rules('required');
            })
            ->when('Treatment', function (Form $form) {
                $drugs = [];
                foreach (
                    DrugStockBatch::where([
                        'administrator_id' => Auth::user()->id
                    ])
                        ->where('current_quantity', '>', 0)
                        ->get() as $key => $v
                ) {

                    $unit = "";
                    if ($v->category != null) {
                        $unit = " - {$v->category->unit}";
                    }
                    $drugs[$v->id] = $v->name . " - Available QTY: {$v->current_quantity} {$unit}";
                }


                $form->select('medicine_id', __('Please select Drug'))
                    ->options($drugs)
                    ->rules('required');
                $form->decimal('medicine_quantity', 'Applied quantity')->rules('required');
            })
            ->when('Vaccination', function (Form $form) {
                $form->select('vaccine_id', __('Please select Vaccine'))
                    ->options(Vaccine::all()->pluck('name', 'name'))
                    ->rules('required');
            });
        $form->divider();
        $form->text('detail', __('Event Details'))
            ->help("Specify the event and be as brief as possible. For example, if Death, only enter the cause of death in
        this detail field.");

        $user = Auth::user();
        $form->hidden('approved_by', __('Approved by'))
            ->default($user->id)
            ->value($user->id)
            ->readonly()
            ->required();


        $form->disableEditingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
