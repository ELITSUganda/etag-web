<?php

namespace App\Admin\Controllers;

use App\Models\FarmVaccinationRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FarmVaccinationRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'FarmVaccinationRecord';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FarmVaccinationRecord());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('farm_id', __('Farm id'));
        $grid->column('vaccine_main_stock_id', __('Vaccine main stock id'));
        $grid->column('district_vaccine_stock_id', __('District vaccine stock id'));
        $grid->column('district_id', __('District id'));
        $grid->column('created_by_id', __('Created by id'));
        $grid->column('updated_by_id', __('Updated by id'));
        $grid->column('number_of_doses', __('Number of doses'));
        $grid->column('number_of_animals_vaccinated', __('Number of animals vaccinated'));
        $grid->column('vaccination_batch_number', __('Vaccination batch number'));
        $grid->column('remarks', __('Remarks'));
        $grid->column('gps_location', __('Gps location'));
        $grid->column('lhc', __('Lhc'));
        $grid->column('farmer_name', __('Farmer name'));
        $grid->column('farmer_phone_number', __('Farmer phone number'));

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
        $show = new Show(FarmVaccinationRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('farm_id', __('Farm id'));
        $show->field('vaccine_main_stock_id', __('Vaccine main stock id'));
        $show->field('district_vaccine_stock_id', __('District vaccine stock id'));
        $show->field('district_id', __('District id'));
        $show->field('created_by_id', __('Created by id'));
        $show->field('updated_by_id', __('Updated by id'));
        $show->field('number_of_doses', __('Number of doses'));
        $show->field('number_of_animals_vaccinated', __('Number of animals vaccinated'));
        $show->field('vaccination_batch_number', __('Vaccination batch number'));
        $show->field('remarks', __('Remarks'));
        $show->field('gps_location', __('Gps location'));
        $show->field('lhc', __('Lhc'));
        $show->field('farmer_name', __('Farmer name'));
        $show->field('farmer_phone_number', __('Farmer phone number'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FarmVaccinationRecord());

        $form->number('farm_id', __('Farm id'));
        $form->number('vaccine_main_stock_id', __('Vaccine main stock id'));
        $form->number('district_vaccine_stock_id', __('District vaccine stock id'));
        $form->number('district_id', __('District id'));
        $form->number('created_by_id', __('Created by id'));
        $form->number('updated_by_id', __('Updated by id'));
        $form->number('number_of_doses', __('Number of doses'));
        $form->number('number_of_animals_vaccinated', __('Number of animals vaccinated'));
        $form->text('vaccination_batch_number', __('Vaccination batch number'));
        $form->textarea('remarks', __('Remarks'));
        $form->textarea('gps_location', __('Gps location'));
        $form->text('lhc', __('Lhc'));
        $form->textarea('farmer_name', __('Farmer name'));
        $form->textarea('farmer_phone_number', __('Farmer phone number'));

        return $form;
    }
}
