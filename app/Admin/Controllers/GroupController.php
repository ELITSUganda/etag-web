<?php

namespace App\Admin\Controllers;

use App\Models\Group;
use Encore\Admin\Controllers\AdminController;
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
        $grid->model()
            ->where([
                'administrator_id' => Auth::user()->id
            ])
            ->orderBy('name', 'desc');
        $grid->disableBatchActions();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('description', __('Description'));
/*         $grid->column('administrator_id', __('Administrator id')); */

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
