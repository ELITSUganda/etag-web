<?php

namespace App\Admin\Controllers;

use App\Models\SickAnimal;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SickAnimalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SickAnimal';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SickAnimal());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('animal_id', __('Animal id'));
        $grid->column('disease_id', __('Disease id'));
        $grid->column('district_id', __('District id'));
        $grid->column('sub_county_id', __('Sub county id'));
        $grid->column('test_results', __('Test results'));
        $grid->column('current_results', __('Current results'));
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
        $show = new Show(SickAnimal::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('animal_id', __('Animal id'));
        $show->field('disease_id', __('Disease id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('test_results', __('Test results'));
        $show->field('current_results', __('Current results'));
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
        $form = new Form(new SickAnimal());

        $form->number('administrator_id', __('Administrator id'));
        $form->number('animal_id', __('Animal id'));
        $form->number('disease_id', __('Disease id'));
        $form->number('district_id', __('District id'))->default(1);
        $form->number('sub_county_id', __('Sub county id'))->default(1);
        $form->text('test_results', __('Test results'))->default('Positive');
        $form->text('current_results', __('Current results'))->default('Positive');
        $form->textarea('details', __('Details'));

        return $form;
    }
}
