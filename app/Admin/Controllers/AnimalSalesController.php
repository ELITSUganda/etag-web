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

use Encore\Admin\Widgets\Form as WidgetForm;


class AnimalSalesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Animals sales';

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
            $grid->model()->where(
                'administrator_id',
                '=',
                Admin::user()->id,
            )->where('status', '=', 'sold');
            $grid->actions(function ($actions) {
                //$actions->disableDelete();
                //$actions->disableEdit();
            });
            //$grid->disableCreateButton();
        } else if (Admin::user()->isRole('trader')) {
            $grid->model()->where(
                'trader',
                '=',
                Admin::user()->id,
            );
            $grid->actions(function ($actions) {
                //$actions->disableDelete();
                //$actions->disableEdit();
            });
            //$grid->disableCreateButton();
        }


        $grid->filter(function ($filter) {



            $sub_counties = [];
            foreach (SubCounty::all() as $key => $p) {
                $sub_counties[$p->id] = $p->name_text ;
            }

            $districts = [];
            foreach (District::all() as $key => $p) {
                $districts[$p->id] = $p->name . "m  ";
            }

  
            $filter->equal('type', "Livestock species")->select(array(
                'Cattle' => "Cattle",
                'Goat' => "Goat",
                'Sheep' => "Sheep"
            ));

            $filter->equal('district_id', "District")->select($districts);
            $filter->equal('sub_county_id', "Sub county")->select($sub_counties);
 
            $filter->equal('e_id', "E-ID");
            $filter->equal('v_id', "V-ID");
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->column('e_id', __('E-ID'))->sortable();
        $grid->column('v_id', __('V-ID'))->sortable();
        $grid->column('lhc', __('LHC'))->sortable();
        $grid->column('type', __('Species'))->sortable();
        $grid->column('breed', __('Breed'))->sortable();
        $grid->column('sex', __('Sex'))->sortable();
        $grid->column('dob', __('Year born'))->sortable();
        $grid->column('fmd', __('Last FMD'))->sortable(); 


        $grid->column('created_at', __('Created'))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable();



        $grid->column('trader', __('Trader'))
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
            
            
            $grid->disableActions();
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
        /*$form = new WidgetForm();
        $form->email('email')->default('qwe@aweq.com');
        $form->password('password');
        $form->text('name'); */

        
        
        $form = new Form(new Animal());
        $form->submitted(function (Form $form) {
            if(
                isset($_POST['animals']) &&
                isset($_POST['trader']) 
            
            ){
                $trader = (int)($_POST['trader']);
                foreach ($_POST['animals'] as $key => $value) {
                    $id = (int)($value);
                    $an = Animal::find($id);
                    if($an==null){
                        continue;
                    }
                    $an->status = "sold";
                    $an->trader = $trader;
                    $an->save(); 
                }
            }
            return redirect(admin_url("sales"));
        });

        $admins = [];
        foreach (Administrator::all() as $key => $v) {
            if (!$v->isRole('trader')) {
                continue;
            }
            $admins[$v->id] = $v->name . " - " . $v->id;
        }
        
        $form->select('trader', __('Select trader'))
        ->options($admins)
        ->required();


        $_items = [];
        foreach (
            Animal::where('administrator_id', '=', Admin::user()->id)
            ->where('status', '!=', 'sold')->get()  as $key => $item) {
            $_items[$item->id] = $item->e_id . " - " . $item->v_id;
        }
        $form->multipleSelect('animals', __('Select animals'))
        ->options($_items)
        ->required();

        $form->checkbox('accept',"Are sure you selected right trader and right animals?")->options([1 => 'Yes'])->required();
       

 
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->disableReset();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });
        return $form;
    }
}
