<?php

namespace App\Admin\Controllers;

use App\Models\MovementRoute;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MovementRouteController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Movement Routes';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new MovementRoute());
        $grid->model()->orderBy('id', 'desc');
        $grid->quickSearch('name')->placeholder('Search by name...');
        $grid->column('id', __('Id'))->sortable();
        $grid->disableBatchActions();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('start_location_id', __('Starts'))->display(function ($id) {
            $u = \App\Models\Location::find($id);
            if (!$u) {
                return $id;
            }
            return $u->name_text;
        });
        $grid->column('end_location_id', __('Ends'))->display(function ($id) {
            $u = \App\Models\Location::find($id);
            if (!$u) {
                return $id;
            }
            return $u->name_text;
        });
        $grid->column('checkpoints', __('Checkpoints'))->display(function ($checkpoints) {
            $count = count($checkpoints);
            return "<b class='label label-primary'>{$count}</b>";
        });
        $grid->column('description', __('Description'))->sortable();

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
        $show = new Show(MovementRoute::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('start_location_id', __('Start location id'));
        $show->field('end_location_id', __('End location id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MovementRoute());

        $form->text('name', __('Name'))->rules('required');
        $form->select('start_location_id', __('Start location'))
            ->options(\App\Models\Location::get_sub_counties_array())
            ->rules('required');
        $form->select('end_location_id', __('End location id'))
            ->options(\App\Models\Location::get_sub_counties_array())
            ->rules('required');
        $form->textarea('description', __('Description'))->rules('required');

        $form->morphMany('checkpoints', function (Form\NestedForm $form) {
            $form->text('name', __('Name'))->rules('required');
            $form->select('sub_county_id', __('Sub county id'))
                ->options(\App\Models\Location::get_sub_counties_array())
                ->rules('required');
            $form->select('administrator_id', __('Checkpoint officer'))
                ->options(\App\Models\User::pluck('name', 'id'))
                ->rules('required');
            $form->text('latitube', __('GPS Latitude'))->rules('required');
            $form->text('longitude', __('GPS Longitude'))->rules('required');
        });
        return $form;
    }
}
