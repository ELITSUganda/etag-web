<?php

namespace App\Admin\Controllers;

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
        $grid = new Grid(new Farm());

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
        $grid->column('parish_id', __('Parish'))
            ->display(function ($id) {
                $u = Parish::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();
   
        $grid->column('farm_type', __('Farm type'))->sortable();
        $grid->column('holding_code', __('Holding code'))->sortable();
        $grid->column('size', __('Size'))->sortable();
        $grid->column('dfm', __('Dfm'));

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

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('parish_id', __('Parish id'));
        $show->field('farm_type', __('Farm type'));
        $show->field('holding_code', __('Holding code'));
        $show->field('size', __('Size'));
        $show->field('latitude', __('Latitude'));
        $show->field('longitude', __('Longitude'));
        $show->field('dfm', __('Dfm'));

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
        $form->setWidth(8, 4);
        $admins = [];
        $parishes = [];
        foreach (Administrator::all() as $key => $v) {
            if (!$v->isRole('farmer')) {
                continue;
            }
            $admins[$v->id] = $v->name . " - " . $v->id;
        }
        foreach (Parish::all() as $key => $p) {
            $parishes[$p->id] = $p->name . ", " .
                $p->sub_county->name . ", " .
                $p->sub_county->district->name . ".";
        }

        $form->hidden('district_id', __('District id'))->default(1);
        $form->hidden('sub_county_id', __('Sub county id'))->default(1);


        $form->select('administrator_id', __('Farm owner'))
            ->options($admins)
            ->required();

        $form->select('parish_id', __('Parish'))
            ->options($parishes)
            ->required();

        $form->select('farm_type', __('Farm type'))
            ->options(array(
                'Diary' => 'Diary',
                'Beef' => 'Beef',
                'Mixed' => 'Mixed'
            ))
            ->required();
 
        $form->text('holding_code', __('Holding code'))->required();
        $form->text('size', __('Size (in Ha)'))->attribute('type', 'number')->required();
        $form->text('latitude', __('Latitude'));
        $form->text('longitude', __('Longitude'));
        $form->textarea('dfm', __('Dfm'));

        return $form;
    }
}
