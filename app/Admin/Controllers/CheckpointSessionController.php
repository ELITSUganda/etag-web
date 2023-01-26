<?php

namespace App\Admin\Controllers;

use App\Models\CheckpointSession;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CheckpointSessionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'CheckpointSession';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CheckpointSession());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('checked_by', __('Checked by'));
        $grid->column('movement_id', __('Movement id'));
        $grid->column('check_point_id', __('Check point id'));
        $grid->column('animals_expected', __('Animals expected'));
        $grid->column('animals_checked', __('Animals checked'));
        $grid->column('animals_found', __('Animals found'));
        $grid->column('animals_missed', __('Animals missed'));
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
        $show = new Show(CheckpointSession::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('checked_by', __('Checked by'));
        $show->field('movement_id', __('Movement id'));
        $show->field('check_point_id', __('Check point id'));
        $show->field('animals_expected', __('Animals expected'));
        $show->field('animals_checked', __('Animals checked'));
        $show->field('animals_found', __('Animals found'));
        $show->field('animals_missed', __('Animals missed'));
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
        $form = new Form(new CheckpointSession());

        $form->number('checked_by', __('Checked by'));
        $form->number('movement_id', __('Movement id'));
        $form->number('check_point_id', __('Check point id'));
        $form->textarea('animals_expected', __('Animals expected'));
        $form->textarea('animals_checked', __('Animals checked'));
        $form->textarea('animals_found', __('Animals found'));
        $form->textarea('animals_missed', __('Animals missed'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
