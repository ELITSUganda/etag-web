<?php

namespace App\Admin\Controllers;

use App\Models\VaccinationProgram;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class VaccinationProgramController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'VaccinationProgram';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new VaccinationProgram());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('title', __('Title'));
        $grid->column('district_vaccine_stock_id', __('District vaccine stock id'));
        $grid->column('district_id', __('District id'));
        $grid->column('sub_district_id', __('Sub district id'));
        $grid->column('parish_id', __('Parish id'));
        $grid->column('dose_per_animal', __('Dose per animal'));
        $grid->column('status', __('Status'));
        $grid->column('description', __('Description'));
        $grid->column('start_date', __('Start date'));
        $grid->column('end_date', __('End date'));
        $grid->column('total_target_farms', __('Total target farms'));
        $grid->column('total_target_animals', __('Total target animals'));
        $grid->column('total_target_doses', __('Total target doses'));
        $grid->column('total_vaccinated_farms', __('Total vaccinated farms'));
        $grid->column('total_vaccinated_animals', __('Total vaccinated animals'));
        $grid->column('total_vaccinated_doses', __('Total vaccinated doses'));

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
        $show = new Show(VaccinationProgram::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('title', __('Title'));
        $show->field('district_vaccine_stock_id', __('District vaccine stock id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_district_id', __('Sub district id'));
        $show->field('parish_id', __('Parish id'));
        $show->field('dose_per_animal', __('Dose per animal'));
        $show->field('status', __('Status'));
        $show->field('description', __('Description'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));
        $show->field('total_target_farms', __('Total target farms'));
        $show->field('total_target_animals', __('Total target animals'));
        $show->field('total_target_doses', __('Total target doses'));
        $show->field('total_vaccinated_farms', __('Total vaccinated farms'));
        $show->field('total_vaccinated_animals', __('Total vaccinated animals'));
        $show->field('total_vaccinated_doses', __('Total vaccinated doses'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new VaccinationProgram());

        $form->textarea('title', __('Title'));
        $form->number('district_vaccine_stock_id', __('District vaccine stock id'));
        $form->number('district_id', __('District id'));
        $form->number('sub_district_id', __('Sub district id'));
        $form->number('parish_id', __('Parish id'));
        $form->number('dose_per_animal', __('Dose per animal'));
        $form->text('status', __('Status'))->default('Upcoming');
        $form->textarea('description', __('Description'));
        $form->date('start_date', __('Start date'))->default(date('Y-m-d'));
        $form->date('end_date', __('End date'))->default(date('Y-m-d'));
        $form->number('total_target_farms', __('Total target farms'));
        $form->number('total_target_animals', __('Total target animals'));
        $form->number('total_target_doses', __('Total target doses'));
        $form->number('total_vaccinated_farms', __('Total vaccinated farms'));
        $form->number('total_vaccinated_animals', __('Total vaccinated animals'));
        $form->number('total_vaccinated_doses', __('Total vaccinated doses'));

        return $form;
    }
}
