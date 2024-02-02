<?php

namespace App\Admin\Controllers;

use App\Models\BatchSession;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BatchSessionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Batch Session';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BatchSession());
        $grid->model()->where('type', 'Treatment');
        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Date'))->display(function ($created_at) {
            return date('Y-m-d H:i:s', strtotime($created_at));
        });
        $grid->column('name', __('Name'));
        $grid->column('type', __('Type'));
        $grid->column('description', __('Description'));
        $grid->column('session_date', __('Session date'));
        $grid->column('animal_text_found', __('Animal text found'));

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
        $show = new Show(BatchSession::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('name', __('Name'));
        $show->field('type', __('Type'));
        $show->field('description', __('Description'));
        $show->field('present', __('Present'));
        $show->field('absent', __('Absent'));
        $show->field('session_category', __('Session category'));
        $show->field('session_date', __('Session date'));
        $show->field('group_id', __('Group id'));
        $show->field('animal_ids_not_found', __('Animal ids not found'));
        $show->field('animal_text_not_found', __('Animal text not found'));
        $show->field('animal_text_found', __('Animal text found'));
        $show->field('animal_ids_found', __('Animal ids found'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new BatchSession());

        $form->number('administrator_id', __('Administrator id'));
        $form->textarea('name', __('Name'));
        $form->textarea('type', __('Type'));
        $form->textarea('description', __('Description'));
        $form->number('present', __('Present'));
        $form->number('absent', __('Absent'));
        $form->text('session_category', __('Session category'));
        $form->text('session_date', __('Session date'));
        $form->number('group_id', __('Group id'));
        $form->textarea('animal_ids_not_found', __('Animal ids not found'));
        $form->textarea('animal_text_not_found', __('Animal text not found'));
        $form->textarea('animal_text_found', __('Animal text found'));
        $form->textarea('animal_ids_found', __('Animal ids found'));

        return $form;
    }
}
