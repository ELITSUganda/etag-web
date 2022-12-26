<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use App\Models\District;
use App\Models\Farm;
use App\Models\Location;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Redirect;

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
        $grid->disableBatchActions();
        if (Admin::user()->isRole('farmer')) {


            //$grid->disableCreateButton();

            //$grid->disableActions();
            $grid->model()->where('administrator_id', '=', Admin::user()->id);
        }


        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });




        $grid->filter(function ($filter) {




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
        $grid->column('photo', __('Photo'))
        ->lightbox(['width' => 60, 'height' => 60])
        ->sortable();
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
                return Utils::get_object(Location::class, $id)->name_text;
            })->sortable();
        $grid->column('sub_county_id', __('Sub county'))
            ->display(function ($id) {
                return Utils::get_object(Location::class, $id)->name_text;
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

        $form->saving(function (Form $form) {

            $today = new Carbon();
            $dob = Carbon::parse($form->dob);

            if ($today->lt($dob)) {
                return Redirect::back()->withInput()->withErrors([
                    'dob' => 'Enter valid date of birth.'
                ]);
            }

            /* if (!$dob->lt(Carbon::parse($form->fmd))) { 
                return Redirect::back()->withInput()->withErrors([
                    'fmd' => 'Enter valid fmd date.'
                ]);
            } */
        });

        $items = [];
        foreach (Farm::all() as $key => $f) {
            if (Admin::user()->isRole('farmer')) {
                if ($f->administrator_id == Admin::user()->id) {
                    $items[$f->id] = $f->holding_code;
                }
            } else {
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


        if ($form->isCreating()) {
            $form->text('e_id', __('Electronic ID (E-ID)'))
                ->rules('required|unique:animals');
        } else {

            $form->text('e_id', __('Electronic ID (E-ID)'))
                ->readonly();
        }

        //'required|email|unique:company_users,email_address,NULL,id,company_id,' . $request->company_id


        $form->text('v_id', __('Visual ID (V-ID)'))->required();

        $form->divider();
        $form->date('dob', __('Year of birth'))->attribute('autocomplete', 'false')->default(date('Y-m-d'))->required();
        $form->radio('has_parent', __('Has a parent'))
            ->options([
                '0' => "No",
                '1' => "Yes",
            ])
            ->default(null)
            ->when(1, function ($f) { 
                $u = Admin::user();
                $f->select('parent_id', 'Select parent')
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
                    );
                return $f;
            })
            ->rules('required');


        $form->image('photo', __('Photo'));

        /* 
parent_id
 */
        $form->divider();


        $form->date('fmd', __('Date last FMD vaccination'))->default(null);
        $form->text('status', __('Status'))->readonly()->default("Active");
        $form->text('lhc', __('LHC'))->readonly();

        return $form;
    }
}
