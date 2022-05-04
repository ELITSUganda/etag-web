<?php

namespace App\Admin\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class TaskController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Task';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Task());

        $grid->column('id', __('Id'))->sortable();


        $grid->column('created_at', __('Created'))
            ->sortable()
            ->display(function () {
                return Carbon::parse($this->created_at)->toFormattedDateString();
            });

        $grid->column('start_date', __('Starts'))
            ->sortable()
            ->display(function () {
                return Carbon::parse($this->start_date)->toFormattedDateString();
            });


        $grid->column('end_date', __('Ends'))
            ->sortable()
            ->display(function () {
                return Carbon::parse($this->end_date)->toFormattedDateString();
            });




        $grid->column('assigned_to', __('Assigned to'))
            ->sortable()
            ->display(function () {
                return $this->assignedTo->name;
            });

        $grid->column('assigned_by', __('Assigned by'))
            ->sortable()
            ->display(function () {
                return $this->assignedTo->name;
            });

        $grid->column('submision_status', __('Submision status'))
            ->sortable()
            ->display(function () {
                return $this->get_status();
            });
        $grid->column('title', __('Title'));
        $grid->column('review_status', __('Review status'))->sortable();
        $grid->column('review_comment', __('Review comment'));

        $grid->column('submit_before', __('Submit before'));
        $grid->column('value', __('Value'));
        $grid->column('category_id', __('Category id'));

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
        $show = new Show(Task::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('assigned_to', __('Assigned to'));
        $show->field('assigned_by', __('Assigned by'));
        $show->field('submision_status', __('Submision status'));
        $show->field('body', __('Body'));
        $show->field('review_comment', __('Review comment'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));
        $show->field('submit_before', __('Submit before'));
        $show->field('review_status', __('Review status'));
        $show->field('value', __('Value'));
        $show->field('category_id', __('Category id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Task());
        $u = Auth::user();
        $is_manager = false;
        if ($u->isRole('manager')) {
            $is_manager = true;
        }

        $admins = [];
        foreach (Administrator::all() as $key => $v) {

            $admins[$v->id] = $v->name . " - " . $v->id . " - ({$v->username})";
        }


        if ($form->isCreating()) {
            $form->hidden('assigned_by', __('Task title'))->default($u->id)->required();

            if ($is_manager) {
                $form->select('assigned_to', __('Assigned to'))
                    ->options($admins)
                    ->default($u->id)
                    ->value($u->id)
                    ->required();
            } else {
                $form->select('assigned_to', __('Assigned to'))
                    ->options($admins)
                    ->default($u->id)
                    ->readOnly()
                    ->value($u->id)
                    ->required();
            }



            $form->text('title', __('Task title'))->required();
            $form->textarea('body', __('Task desscription'))->required();

            $form->datetime('start_date', __('Start date'))->default(date('Y-m-d'))->required();
            $form->datetime('end_date', __('End date'))->required();
            $form->datetime('submit_before', __('Submit before'))->required();
        } else {
            $form->textarea('review_comment', __('Review comment'));
            $form->text('submision_status', __('Submision status'))->default(0);
            $form->number('review_status', __('Review status'));
            $form->number('value', __('Value'))->default(1);
            $form->number('category_id', __('Category id'))->default(1);
        }







        return $form;
    }
}
