<?php

namespace App\Admin\Controllers;

use App\Models\DrugCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DrugCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Drug Categories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DrugCategory());
        $grid->disableBatchActions();
        $grid->column('id', __('Id'));
        $grid->picture('photo', __('Photo'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('unit', __('Unit'))->sortable(); 

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
        $show = new Show(DrugCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('unit', __('Unit'));
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
        $form = new Form(new DrugCategory());

        $form->text('name', __('Name'))->required();
        $form->text('unit', __('Measuring Unit'))->required();
        $form->image('photo', __('Drug Photo'))->required();
        $form->textarea('details', __('Details'));

        return $form;
    }
}
