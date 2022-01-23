<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use App\Models\Disease;
use App\Models\District;
use App\Models\Event;
use App\Models\Medicine;
use App\Models\Parish;
use App\Models\SubCounty;
use App\Models\Vaccine;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class EventController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Events';

    /**
     * Make a grid builder.
    
     administrator_id
     
district_id
sub_county_id
parish_id
farm_id
type

detail

animal_type


     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Event());
        /*$faker = \Faker\Factory::create();
        
        $types = Array(
            'Sick' => 'Sick',
            'Healed' => 'Healed',
            'Vaccinated' => 'Vaccinated',
            'Gave birth' => 'Gave birth',
            'Sold' => 'Sold',
            'Died' => 'Died',
            'Slautered' => 'Slautered',
            'Stolen' => 'Stolen',
        );
        Event::truncate();
        for($i = 0;$i<300;$i++){
            $e = new Event();
            shuffle($types); 
            $e->type = $types[0];
            $e->detail = $faker->sentence();
            $e->approved_by = 1;
            $e->animal_id = rand(1,400);
            $e->save();
        }
        dd("Done");*/


        $grid->filter(function ($filter) {

            $parishes = [];
            foreach (Parish::all() as $key => $p) {
                $parishes[$p->id] = $p->name . ", " .
                    $p->sub_county->name . ", " .
                    $p->sub_county->district->name . ".";
            }

            $sub_counties = [];
            foreach (SubCounty::all() as $key => $p) {
                $sub_counties[$p->id] = $p->name . ", " .
                    $p->district->name . ".";
            }

            $districts = [];
            foreach (District::all() as $key => $p) {
                $districts[$p->id] = $p->name . "m  ";
            }

            $admins = [];
            foreach (Administrator::all() as $key => $v) {
                if (!$v->isRole('farmer')) {
                    continue;
                }
                $admins[$v->id] = $v->name . " - " . $v->id;
            }

            $animals = [];
            foreach (Animal::all() as $key => $v) {
                $animals[$v->id] = $v->e_id . " - " . $v->v_id;
            }

            $filter->equal('administrator_id', "Owner")->select($admins);

            $filter->equal('type', "Animal type")->select(array(
                'Cattle' => "Cattle",
                'Goat' => "Goat",
                'Sheep' => "Sheep"
            ));

            $filter->equal('district_id', "District")->select($districts);
            $filter->equal('sub_county_id', "Sub county")->select($sub_counties);
            $filter->equal('parish_id', "Parish")->select($parishes);
            $filter->like('animal_id', "Animal")->select($animals);
            $filter->equal('type', "Event type")->select(array(
                'Disease' => 'Disease',
                'Drug' => 'Teatment',
                'Vaccination' => 'Vaccination',
                'Death' => 'Death',
                'Slaughter' => 'Slaughter',
                'Other' => 'Other',

            ));
            $filter->equal('disease_id', "Event type")->select(
                Disease::all()->pluck('name', 'id')
            );
            $filter->equal('medicine_id', "Drug")->select(
                Medicine::all()->pluck('name', 'id')
            );
            $filter->equal('vaccine_id', "Vaccine")->select(
                Vaccine::all()->pluck('name', 'id')
            );
        });


        $grid->column('created_at', __('Created'))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable();

        $grid->column('animal_id', __('Animal'))
            ->display(function ($id) {
                $u = Animal::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->e_id . " - " . $u->v_id;
            })->sortable();


        $grid->column('animal_type', __('Animal type'))->sortable();
        $grid->column('type', __('Event Type'))->sortable();


        $grid->column('vaccine_id', __('Vaccine'))
            ->display(function ($id) {
                $u = Vaccine::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();

        $grid->column('disease_id', __('Disease'))
            ->display(function ($id) {
                $u = Disease::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();

        $grid->column('medicine_id', __('Drug'))
            ->display(function ($id) {
                $u = Medicine::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();



        $grid->column('district_id', __('District'))
            ->display(function ($id) {
                $u = District::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();
        $grid->column('sub_county_id', __('Sub county'))
            ->display(function ($id) {
                $u = SubCounty::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();
        $grid->column('parish_id', __('Parish'))
            ->display(function ($id) {
                $u = Parish::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();

        $grid->column('administrator_id', __('Animal owner'))
            ->display(function ($id) {
                $u = Administrator::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();




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

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('parish_id', __('Parish id'));
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
        $form = new Form(new Event());

        $form->hidden('administrator_id', __('Administrator id'))->default(1);
        $form->hidden('district_id', __('District id'))->default(1);
        $form->hidden('sub_county_id', __('Sub county id'))->default(1);
        $form->hidden('parish_id', __('Parish id'))->default(1);
        $form->hidden('farm_id', __('Farm id'))->default(1);

        $animals = [];
        foreach (Animal::all() as $key => $v) {
            $animals[$v->id] = $v->e_id . " - " . $v->v_id;
        }

        $form->select('animal_id', __('Select Animal'))
            ->options($animals)
            ->required();

        $form->radio('type', __('Event type'))
            ->options(array(
                'Disease' => 'Disease',
                'Drug' => 'Teatment',
                'Vaccination' => 'Vaccination',
                'Death' => 'Death',
                'Slaughter' => 'Slaughter',
                'Other' => 'Other',

            ))
            ->required()
            ->when('Disease', function (Form $form) {
                $form->select('disease_id', __('Select disease'))
                    ->options(Disease::all()->pluck('name', 'id'))
                    ->help('Please select disease')
                    ->rules('required');
            })
            ->when('Drug', function (Form $form) {
                $form->select('medicine_id', __('Please select Drug'))
                    ->options(Medicine::all()->pluck('name', 'id'))
                    ->rules('required');
            })
            ->when('Vaccination', function (Form $form) {
                $form->select('vaccine_id', __('Please select Vaccine'))
                    ->options(Vaccine::all()->pluck('name', 'id'))
                    ->rules('required');
            });

        $form->text('detail', __('Detail'))->required()
            ->help("Specify the event and be as brief as possible. For example, if Sick, only enter the name of disease in
        this detail field.");

        $user = Auth::user();
        $form->select('approved_by', __('Approved by'))
            ->options(array(
                $user->id => $user->name
            ))
            ->default($user->id)
            ->value($user->id)
            ->readonly()
            ->required();

        return $form;
    }
}
