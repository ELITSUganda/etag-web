<?php

namespace App\Admin\Controllers;

use App\Models\Group;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class GroupController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Group';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Group());
        $grid->quickSearch('name', 'description');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('name', __('Name'));
            $filter->like('description', __('Description'));
        });
        $admin = Admin::user();
        //if not admin
        if (!$admin->isRole('administrator')) {
            $grid->model()->where('administrator_id', $admin->id);
        }
        $grid->model()
            ->orderBy('id', 'desc');
        $grid->disableBatchActions();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('description', __('Description'));
        /*         $grid->column('administrator_id', __('Administrator id')); */
        //animals_count
        $grid->column('animals_count', __('Animals'))->display(function () {
            $count = $this->animals()->count();
            return "<span class='label label-success'>{$count}</span>";
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
        $show = new Show(Group::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('administrator_id', __('Administrator id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Group());

        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->text('name', __('Name'));
        $form->textarea('description', __('Description'));
        $form->hidden('administrator_id', __('Administrator id'))->default(Auth::user()->id);

        return $form;
    }
}
