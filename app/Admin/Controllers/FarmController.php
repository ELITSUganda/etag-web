<?php

namespace App\Admin\Controllers;

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

class FarmController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Farm';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {



        /*Farm::truncate();
        for ($i=0; $i < 500; $i++) { 
            $faker = \Faker\Factory::create();
            $f = new Farm();
            $f->name = $faker->sentence(2);
            //$f->dfm = $faker->sentence(2);
            $f->administrator_id = rand(7,400); 
            $f->sub_county_id = rand(1,11); 
            $types = ['Dairy','Beef','Mixed'];
            shuffle($types);
            $f->farm_type = $types[0]; 
            $f->size = rand(1,45); 
            $f->animals_count = rand(1,45); 
            $f->latitude = "0.0"; 
            $f->longitude = "0.0"; 
            $f->save();
        }*/
        $grid = new Grid(new Farm());
        if (Admin::user()->isRole('farmer')) {
            $grid->model()->where('administrator_id', '=', Admin::user()->id);
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
            });
            $grid->disableCreateButton();
        }

        $grid->filter(function ($filter) {



            $sub_counties = [];
            foreach (SubCounty::all() as $key => $p) {
                $sub_counties[$p->id] = $p->name . ", " .
                    $p->district->name . ".";
            }

            $districts = [];
            foreach (District::all() as $key => $p) {
                $districts[$p->id] = $p->name . "  ";
            }

            $admins = [];
            foreach (Administrator::all() as $key => $v) {
                if (!$v->isRole('farmer')) {
                    continue;
                }
                $admins[$v->id] = $v->name . " - " . $v->code;
            }

            $filter->equal('administrator_id', "Owner")->select($admins);
            $filter->equal('farm_type', "Farm type")->select([
                'Beef' => 'Beef',
                'Dairy' => 'Dairy',
                'Mixed' => 'Mixed',
            ]);
            $filter->equal('district_id', "District")->select($districts);
            $filter->equal('sub_county_id', "Sub county")->select($sub_counties);
        });


        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created'))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable();
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


        $grid->column('farm_type', __('Farm type'))->sortable();
        $grid->column('holding_code', __('Holding code'))->sortable();
        $grid->column('size', __('Size'))->sortable();
        $grid->column('animals_count', __('Animals'))->sortable()->width(50);
        $grid->column('dfm', __('Detail'));

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
        $show->field('farm_type', __('Farm type'));
        $show->field('holding_code', __('Holding code'));
        $show->field('size', __('Size'));
        $show->field('latitude', __('Latitude'));
        $show->field('longitude', __('Longitude'));
        $show->field('dfm', __('Detail'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Farm());
        $admins = [];
        foreach (Administrator::all() as $key => $v) {
            if (!$v->isRole('farmer')) {
                continue;
            }
            $admins[$v->id] = $v->name . " - " . $v->id;
        }
        $sub_counties = [];
        foreach (SubCounty::all() as $key => $p) {
            $sub_counties[$p->id] = $p->name . ", " .
                $p->district->name . ".";
        }


        $form->hidden('district_id', __('District id'))->default(1);
        $form->select('administrator_id', __('Farm owner'))
            ->options($admins)
            ->required();

        $form->select('sub_county_id', __('Sub counties'))
            ->options($sub_counties)
            ->required();

        $form->text('village', __('Village'))->required();

        $form->select('farm_type', __('Farm type'))
            ->options(array(
                'Dairy' => 'Dairy',
                'Beef' => 'Beef',
                'Mixed' => 'Mixed'
            ))
            ->required();
        $form->text('sheep_count', __('Number of sheep'))->required();
        $form->text('goats_count', __('Number of goats'))->required();
        $form->text('cattle_count', __('Number of cattle'))->required();
        $form->text('size', __('Size (in Ha)'))->attribute('type', 'number')->required();

        $form->latlong('latitude', 'longitude', 'Location of the farm')->default(['lat' => 0.3130291, 'lng' => 32.5290854])->required();

        $form->textarea('dfm', __('Detail'));
        $form->text('holding_code', __('Holding code'))->readonly();

        return $form;
    }
}
