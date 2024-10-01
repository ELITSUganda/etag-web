<?php

namespace App\Admin\Controllers;

use App\Models\PregnantAnimal;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PregnantAnimalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Pregnant Animals';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PregnantAnimal());
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Created at'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('animal_id', __('Animal id'));
        $grid->column('disease_id', __('Disease id'));
        $grid->column('district_id', __('District id'));
        $grid->column('sub_county_id', __('Sub county id'));
        $grid->column('original_status', __('Original status'));
        $grid->column('current_status', __('Current status'));
        $grid->column('fertilization_method', __('Fertilization method'));
        $grid->column('expected_sex', __('Expected sex'));
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
        $show = new Show(PregnantAnimal::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('animal_id', __('Animal id'));
        $show->field('disease_id', __('Disease id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('original_status', __('Original status'));
        $show->field('current_status', __('Current status'));
        $show->field('fertilization_method', __('Fertilization method'));
        $show->field('expected_sex', __('Expected sex'));
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
        $form = new Form(new PregnantAnimal());

        $form->number('administrator_id', __('Administrator id'));
        $form->number('animal_id', __('Animal id'));
        $form->number('disease_id', __('Disease id'));
        $form->number('district_id', __('District id'))->default(1);
        $form->number('sub_county_id', __('Sub county id'))->default(1);
        $form->text('original_status', __('Original status'))->default('Pregnant');
        $form->text('current_status', __('Current status'))->default('Pregnant');
        $form->text('fertilization_method', __('Fertilization method'));
        $form->text('expected_sex', __('Expected sex'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
