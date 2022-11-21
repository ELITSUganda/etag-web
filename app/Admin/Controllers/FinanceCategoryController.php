<?php

namespace App\Admin\Controllers;

use App\Models\FinanceCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FinanceCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Financial Categories';

    /**
     * Make a grid builder. 
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FinanceCategory());

        
        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('name', __('Name'));
        $grid->column('balance', __('Balance'));
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
        $show = new Show(FinanceCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('name', __('Name'));
        $show->field('balance', __('Balance'));
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
        $form = new Form(new FinanceCategory());


        $u = Admin::user();
        $form->hidden('administrator_id', __('Administrator id'))->default($u->id);

        $form->text('name', __('Category Name'))->rules('required');
        $form->textarea('details', __('Category Details'));

        return $form;
    }
}
