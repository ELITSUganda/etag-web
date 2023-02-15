<?php

namespace App\Admin\Controllers;

use App\Models\VetServiceCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class VetServiceCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'VetServiceCategory';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new VetServiceCategory());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('service_name', __('Service name'));
        $grid->column('service_description', __('Service description'));
        $grid->column('photo', __('Photo'));

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
        $show = new Show(VetServiceCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('service_name', __('Service name'));
        $show->field('service_description', __('Service description'));
        $show->field('photo', __('Photo'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new VetServiceCategory());

        $form->text('service_name', __('Service name'))->required();
        $form->image('photo', __('Photo'));
        $form->textarea('service_description', __('Service description'));

        return $form;
    }
}
