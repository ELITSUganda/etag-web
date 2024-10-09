<?php

namespace App\Admin\Controllers;

use App\Models\CheckPoint;
use App\Models\Location;
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
    protected $title = 'CheckPoints';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CheckPoint());

        $grid->model()->orderBy('id', 'desc');
        $grid->quickSearch('name')->placeholder('Search by name...');
        $grid->column('id', __('Id'))->sortable();
        $grid->disableBatchActions();
        $grid->column('name', __('Name'))->sortable();

        $grid->column('administrator_id', __('Checkpoint officer'))
            ->display(function ($id) {
                $u = Administrator::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();
        $grid->column('movement_route_id', 'Movement Route')
            ->display(function ($id) {
                $u = \App\Models\MovementRoute::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name;
            })->sortable();
        $grid->column('sub_county_id', __('Sub county id'))
            ->display(function ($id) {
                $u = Location::find($id);
                if (!$u) {
                    return $id;
                }
                return $u->name_text;
            })->sortable();

        $grid->column('details', __('Details'))->hide();
        $grid->column('gps', __('GPS'))
            ->display(function () {
                return $this->longitude . "," . $this->longitude;
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

        $form->select('movement_route_id', __('Movement Route'))
            ->options(\App\Models\MovementRoute::pluck('name', 'id'))
            ->rules('required')
            ->required();

        $form->text('name', __('Checkpoinnt Name'))->required();


        $form->select('administrator_id', __('Checkpoint officer'))
            ->options(\App\Models\User::pluck('name', 'id'))
            ->required();

     
        $form->select('sub_county_id', 'Sub-county')->options(function ($id) {
            $a = Location::find($id);
            if ($a) {
                return [$a->id =>  $a->name_text];
            }
        })
            ->rules('required')
            ->ajax(url(
                '/api/sub-counties'
            ));


        $form->text('latitube', __('GPS latitube'))->required();
        $form->text('longitude', __('GPS longitude'))->required();
        $form->textarea('details', __('Details'))->required();

        return $form;
    }
}
