<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class Animal1Controller extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Animal';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Animal());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('district_id', __('District id'));
        $grid->column('sub_county_id', __('Sub county id'));
        $grid->column('parish_id', __('Parish id'));
        $grid->column('status', __('Status'));
        $grid->column('type', __('Type'));
        $grid->column('e_id', __('E id'));
        $grid->column('v_id', __('V id'));
        $grid->column('lhc', __('Lhc'));
        $grid->column('breed', __('Breed'));
        $grid->column('sex', __('Sex'));
        $grid->column('dob', __('Dob'));
        $grid->column('color', __('Color'));
        $grid->column('farm_id', __('Farm id'));
        $grid->column('fmd', __('Fmd'));
        $grid->column('trader', __('Trader'));
        $grid->column('destination', __('Destination'));
        $grid->column('destination_slaughter_house', __('Destination slaughter house'));
        $grid->column('destination_farm', __('Destination farm'));
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
        $show = new Show(Animal::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('parish_id', __('Parish id'));
        $show->field('status', __('Status'));
        $show->field('type', __('Type'));
        $show->field('e_id', __('E id'));
        $show->field('v_id', __('V id'));
        $show->field('lhc', __('Lhc'));
        $show->field('breed', __('Breed'));
        $show->field('sex', __('Sex'));
        $show->field('dob', __('Dob'));
        $show->field('color', __('Color'));
        $show->field('farm_id', __('Farm id'));
        $show->field('fmd', __('Fmd'));
        $show->field('trader', __('Trader'));
        $show->field('destination', __('Destination'));
        $show->field('destination_slaughter_house', __('Destination slaughter house'));
        $show->field('destination_farm', __('Destination farm'));
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
        $form = new Form(new Animal());

        $form->number('administrator_id', __('Administrator id'))->default(1);
        $form->number('district_id', __('District id'))->default(1);
        $form->number('sub_county_id', __('Sub county id'))->default(1);
        $form->number('parish_id', __('Parish id'))->default(1);
        $form->textarea('status', __('Status'));
        $form->text('type', __('Type'));
        $form->textarea('e_id', __('E id'));
        $form->textarea('v_id', __('V id'));
        $form->textarea('lhc', __('Lhc'));
        $form->textarea('breed', __('Breed'));
        $form->textarea('sex', __('Sex'));
        $form->textarea('dob', __('Dob'));
        $form->textarea('color', __('Color'));
        $form->number('farm_id', __('Farm id'))->default(1);
        $form->textarea('fmd', __('Fmd'));
        $form->number('trader', __('Trader'));
        $form->textarea('destination', __('Destination'));
        $form->number('destination_slaughter_house', __('Destination slaughter house'));
        $form->number('destination_farm', __('Destination farm'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
