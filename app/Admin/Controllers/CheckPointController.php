<?php

namespace App\Admin\Controllers;

use App\Models\CheckPoint;
use App\Models\SubCounty;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CheckPointController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Check Points';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CheckPoint());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created '))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable();
        $grid->column('name', __('Name'));
        $grid->column('details', __('Details'));
        $grid->column('gps', __('GPS'))
            ->display(function () {
                return $this->longitude . "," . $this->longitude;
            });
        $grid->column('administrator_id', __('Checkpoint officer'))
            ->display(function ($id) {
                $u = Administrator::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();
        $grid->column('sub_county_id', __('Sub county id'))
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
        $show = new Show(CheckPoint::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('details', __('Details'));
        $show->field('longitude', __('Longitude'));
        $show->field('latitube', __('Latitube'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('sub_county_id', __('Sub county id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CheckPoint());

        $sub_counties = [];
        foreach (SubCounty::all() as $key => $p) {
            $sub_counties[$p->id] = $p->name . ", " .
                $p->district->name . ".";
        }
        $admins = [];
        foreach (Administrator::all() as $key => $v) {
            if (!$v->isRole('check-point-officer')) {
                continue;
            }
            $admins[$v->id] = $v->name . " - " . $v->id . " - ({$v->username})";
        }


        $form->text('name', __('Checkpoinnt Name/Route'))->required();

        $form->select('sub_county_id', __('Sub-county'))
            ->options($sub_counties)
            ->required();

        $form->select('administrator_id', __('Chech-point officer'))
            ->options($admins)
            ->required();

        $form->latlong('latitube', 'longitude', 'Location on map')->height(300);
        $form->textarea('details', __('Details'))->required();

        return $form;
    }
}
