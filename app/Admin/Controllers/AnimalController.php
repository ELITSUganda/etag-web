<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use App\Models\District;
use App\Models\Farm;
use App\Models\SubCounty;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
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

        /*Animal::truncate();
        for ($i=0; $i < 200; $i++) {  
            $faker = \Faker\Factory::create();
            $a = new Animal();
            $types = ['Cattle','Goat','Sheep'];
            shuffle($types);
            $a->type = $types[0]; 
            $a->e_id = $faker->numberBetween(1000000000,100000000000); 
            $a->v_id = $faker->numberBetween(10000,100000); 
            $a->farm_id = $faker->numberBetween(1,400); 
            
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
            $a->fmd = '2021-2-1';
            $a->save();
        }*/

        $grid = new Grid(new Animal());
        if (Admin::user()->isRole('farmer')) {


            //$grid->disableCreateButton();

            $grid->disableActions();
            $grid->model()->where('administrator_id', '=', Admin::user()->id);
        }

        
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });
        



        $grid->filter(function ($filter) {



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
            $filter->equal('type', "Livestock species")->select(array(
                'Cattle' => "Cattle",
                'Goat' => "Goat",
                'Sheep' => "Sheep"
            ));

            $filter->equal('district_id', "District")->select($districts);
            $filter->equal('sub_county_id', "Sub county")->select($sub_counties);
            $filter->equal('status', "Status")->select(array(
                'Diagonized' => 'Diagonized',
                'Healed' => 'Healed',
                'Vaccinated' => 'Vaccinated',
                'Died' => 'Died',
                'Slautered' => 'Slautered',
                'Stolen' => 'Stolen',
                'Other' => 'Other',
            ));
            $filter->equal('e_id', "E-ID");
            $filter->equal('v_id', "V-ID");
        });

        $grid->model()->orderBy('id', 'DESC');
        $grid->column('e_id', __('E-ID'))->sortable();
        $grid->column('v_id', __('V-ID'))->sortable();
        $grid->column('lhc', __('LHC'))->sortable();
        $grid->column('type', __('Species'))->sortable();
        $grid->column('breed', __('Breed'))->sortable();
        $grid->column('sex', __('Sex'))->sortable();
        $grid->column('dob', __('Year born'))->sortable();
        $grid->column('fmd', __('Last FMD'))->sortable();
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
        $show->field('status', __('Status'));
        $show->field('type', __('Type'));
        $show->field('e_id', __('E id'));
        $show->field('v_id', __('V id'));
        $show->field('lhc', __('Lhc'));
        $show->field('breed', __('Breed'));
        $show->field('sex', __('Sex'));
        $show->field('dob', __('Year of birth'));

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
            if (Admin::user()->isRole('farmer')) {
                if($f->administrator_id == Admin::user()->id){
                    $items[$f->id] = $f->holding_code;
                }
            }else{
                $items[$f->id] = $f->holding_code . " - By " . $f->owner()->name;
            }
        }

        $form->hidden('administrator_id', __('Administrator id'))->default(1);
        $form->hidden('district_id', __('District id'))->default(1);
        $form->hidden('sub_county_id', __('Subcounty'))->default(1);

        $form->select('farm_id', __('Farm'))
            ->options($items)
            ->required();

        $form->select('type', __('Livestock species'))
            ->options(array(
                'Cattle' => "Cattle",
                'Goat' => "Goat",
                'Sheep' => "Sheep"
            ))
            ->required();

        $form->radio('sex', __('Sex'))
            ->options(array(
                'Male' => "Male",
                'Female' => "Female",
            ))
            ->required();

        $form->select('breed', __('Breed'))
            ->options(array(
                'Ankole' => "Ankole",
                'Short horn zebu' => "Short horn zebu",
                'Holstein' => "Holstein",
                'Other' => "Other"
            ))
            ->required();



        $form->text('e_id', __('Electronic id'))->required();
        $form->text('v_id', __('Tag id'))->required();

        $form->year('dob', __('Year of birth'))->attribute('autocomplete', 'false')->default(date('Y-m-d'))->required();
        $form->date('fmd', __('Date last FMD vaccination'))->default(date('Y-m-d'))->required();
        $form->text('status', __('Status'))->readonly()->default("Live");
        $form->text('lhc', __('LHC'))->readonly();

        return $form;
    }
}
