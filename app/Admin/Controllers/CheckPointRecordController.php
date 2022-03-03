<?php

namespace App\Admin\Controllers;

use App\Models\CheckPointRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CheckPointRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'CheckPointRecord';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CheckPointRecord());
 
        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('check_point_id', __('Check point id'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('movement_id', __('Movement id'));
        $grid->column('time', __('Time'));
        $grid->column('latitude', __('Latitude'));
        $grid->column('longitude', __('Longitude'));
        $grid->column('on_permit', __('On permit'));
        $grid->column('checked', __('Checked'));
        $grid->column('success', __('Success'));
        $grid->column('failed', __('Failed'));
        $grid->column('details', __('Details'));

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
        $show = new Show(CheckPointRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('check_point_id', __('Check point id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('movement_id', __('Movement id'));
        $show->field('time', __('Time'));
        $show->field('latitude', __('Latitude'));
        $show->field('longitude', __('Longitude'));
        $show->field('on_permit', __('On permit'));
        $show->field('checked', __('Checked'));
        $show->field('success', __('Success'));
        $show->field('failed', __('Failed'));
        $show->field('details', __('Details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CheckPointRecord());

        $form->number('check_point_id', __('Check point id'));
        $form->number('administrator_id', __('Administrator id'));
        $form->number('movement_id', __('Movement id'));
        $form->textarea('time', __('Time'));
        $form->textarea('latitude', __('Latitude'));
        $form->textarea('longitude', __('Longitude'));
        $form->textarea('on_permit', __('On permit'));
        $form->textarea('checked', __('Checked'));
        $form->textarea('success', __('Success'));
        $form->textarea('failed', __('Failed'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
