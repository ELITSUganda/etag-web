<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use App\Models\District;
use App\Models\Farm;
use App\Models\Parish;
use App\Models\SubCounty;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AnimalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Livestock Data Register - Animals';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {  

        /*for ($i=0; $i < 500; $i++) {  
            $faker = \Faker\Factory::create();
            $a = new Animal();
            $types = ['Cattle','Goat','Sheep'];
            shuffle($types);
            $a->type = $types[0]; 
            $a->e_id = $faker->numberBetween(100000000000,10000000000000); 
            $a->v_id = $faker->numberBetween(10000,100000); 
            $a->farm_id = $faker->numberBetween(1,100); 
            
            $breeds = Array(
                'Ankole' => "Ankole",
                'Short horn zebu' => "Short horn zebu",
                'Holstein' => "Holstein",
                'Other' => "Other"
            );
            shuffle($breeds);
            $a->breed = $breeds[0];

            
            $sexs = ['Male','Female'];
            shuffle($sexs);
            $a->sex = $sexs[0];
            $a->dob = '2021-1-1';
             
            $colors = ['Black','Brown','Black and white','Brown and white','Mixed'];
            shuffle($colors);
            $a->color = $colors[0];
            $a->save();
            
        }*/

        $grid = new Grid(new Animal());

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

            $filter->equal('administrator_id', "Owner")->select($admins);
            $filter->equal('sex', "Sex")->select([
                'Male' => 'Male',
                'Female' => 'Female', 
            ]);
            $filter->equal('type', "Animal type")->select(Array(
                'Cattle' => "Cattle",
                'Goat' => "Goat",
                'Sheep' => "Sheep"
            ));

            $filter->equal('district_id', "District")->select($districts);
            $filter->equal('sub_county_id', "Sub county")->select($sub_counties);
            $filter->equal('parish_id', "Parish")->select($parishes);
            $filter->equal('e_id', "E-ID")->select($parishes);
            $filter->equal('v_id', "V-ID")->select($parishes);
        
        });
        

 
        $grid->column('created_at', __('Created'))
        ->display(function ($f) {
            return Carbon::parse($f)->toFormattedDateString();
        })->sortable();


        $grid->column('e_id', __('E id'))->sortable();
        $grid->column('lhc', __('LHC'))->sortable();

        $grid->column('type', __('Type'))->sortable();
        $grid->column('sex', __('Sex'))->sortable(); 
        $grid->column('status', __('Status'))->sortable();

        $grid->column('administrator_id', __('Owner'))
        ->display(function ($id) {
            $u = Administrator::find($id);
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
        $show = new Show(Animal::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('parish_id', __('Parish id'));
        $show->field('status', __('Status'));
        $show->field('type', __('Type'));
        $show->field('e_id', __('E id'));
        $show->field('v_id', __('V id'));
        $show->field('lhc', __('Lhc'));
        $show->field('breed', __('Breed'));
        $show->field('sex', __('Sex'));
        $show->field('dob', __('Dob'));
        $show->field('color', __('Color'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Animal());
        //$form->setWidth(8, 4);

        $items = [];
        foreach (Farm::all() as $key => $f) {
            $items[$f->id] = $f->holding_code . " - By ".$f->owner()->name ;
        }

        $form->hidden('administrator_id', __('Administrator id'))->default(1);
        $form->hidden('district_id', __('District id'))->default(1);
        $form->hidden('sub_county_id', __('Sub county id'))->default(1);
        $form->hidden('parish_id', __('Parish id'))->default(1);

        $form->select('farm_id', __('Farm'))
        ->options($items)
        ->required();
          
        $form->select('type', __('Animal Type'))
        ->options(Array(
            'Cow' => "Cow",
            'Goat' => "Goat",
            'Sheep' => "Sheep"
        ))
        ->required();

        $form->radio('sex', __('Sex'))
        ->options(Array(
            'Male' => "Male",
            'Female' => "Female", 
        ))
        ->required();
 
        $form->select('breed', __('Breed'))
        ->options(Array(
            'Ankole' => "Ankole",
            'Short horn zebu' => "Short horn zebu",
            'Holstein' => "Holstein",
            'Other' => "Other"
        ))
        ->required();
 
        $form->select('color', __('Color'))
        ->options(Array(
            'Black' => "Black",
            'Brown' => "Brown",
            'Black and white' => "Black and white",
            'Brown and white' => "Brown and white",
            'Mixed' => "Mixed",
        ))
        ->required();
 

        $form->text('e_id', __('Electronic id'))->required();
        $form->text('v_id', __('Tag id'))->required();
        
        $form->date('dob', __('Date of birth'))->default(date('Y-m-d'))->required();
        $form->text('status', __('Status'))->readonly()->default("Live");
        $form->text('lhc', __('LHC'))->readonly();

        return $form;
    }
}
