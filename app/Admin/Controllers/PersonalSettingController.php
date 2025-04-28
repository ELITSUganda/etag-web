<?php

namespace App\Admin\Controllers;

use App\Models\PersonalSetting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PersonalSettingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'PersonalSetting';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PersonalSetting());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('user_id', __('User id'));
        $grid->column('enable_sms_notification', __('Enable sms notification'));
        $grid->column('sms_phone_number', __('Sms phone number'));
        $grid->column('paid_for_sms_notification', __('Paid for sms notification'));
        $grid->column('enable_email_notification', __('Enable email notification'));
        $grid->column('email_address', __('Email address'));
        $grid->column('farm_worker_can_view_data', __('Farm worker can view data'));
        $grid->column('farm_worker_can_edit_data', __('Farm worker can edit data'));
        $grid->column('farm_worker_can_add_data', __('Farm worker can add data'));
        $grid->column('farm_worker_can_delete_data', __('Farm worker can delete data'));
        $grid->column('enable_automated_reports', __('Enable automated reports'));
        $grid->column('report_frequency', __('Report frequency'));
        $grid->column('enable_milk_production_report', __('Enable milk production report'));
        $grid->column('enable_animal_health_report', __('Enable animal health report'));
        $grid->column('enable_animal_sales_report', __('Enable animal sales report'));
        $grid->column('enable_animal_birth_report', __('Enable animal birth report'));
        $grid->column('enable_animal_death_report', __('Enable animal death report'));
        $grid->column('enable_animal_movement_report', __('Enable animal movement report'));
        $grid->column('enable_animal_treatment_report', __('Enable animal treatment report'));
        $grid->column('enable_animal_vaccination_report', __('Enable animal vaccination report'));
        $grid->column('enable_animal_weighing_report', __('Enable animal weighing report'));
        $grid->column('enable_animal_tagging_report', __('Enable animal tagging report'));
        $grid->column('enable_animal_breeding_report', __('Enable animal breeding report'));
        $grid->column('enable_financial_report', __('Enable financial report'));
        $grid->column('enable_milk_sales_report', __('Enable milk sales report'));

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
        $show = new Show(PersonalSetting::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('user_id', __('User id'));
        $show->field('enable_sms_notification', __('Enable sms notification'));
        $show->field('sms_phone_number', __('Sms phone number'));
        $show->field('paid_for_sms_notification', __('Paid for sms notification'));
        $show->field('enable_email_notification', __('Enable email notification'));
        $show->field('email_address', __('Email address'));
        $show->field('farm_worker_can_view_data', __('Farm worker can view data'));
        $show->field('farm_worker_can_edit_data', __('Farm worker can edit data'));
        $show->field('farm_worker_can_add_data', __('Farm worker can add data'));
        $show->field('farm_worker_can_delete_data', __('Farm worker can delete data'));
        $show->field('enable_automated_reports', __('Enable automated reports'));
        $show->field('report_frequency', __('Report frequency'));
        $show->field('enable_milk_production_report', __('Enable milk production report'));
        $show->field('enable_animal_health_report', __('Enable animal health report'));
        $show->field('enable_animal_sales_report', __('Enable animal sales report'));
        $show->field('enable_animal_birth_report', __('Enable animal birth report'));
        $show->field('enable_animal_death_report', __('Enable animal death report'));
        $show->field('enable_animal_movement_report', __('Enable animal movement report'));
        $show->field('enable_animal_treatment_report', __('Enable animal treatment report'));
        $show->field('enable_animal_vaccination_report', __('Enable animal vaccination report'));
        $show->field('enable_animal_weighing_report', __('Enable animal weighing report'));
        $show->field('enable_animal_tagging_report', __('Enable animal tagging report'));
        $show->field('enable_animal_breeding_report', __('Enable animal breeding report'));
        $show->field('enable_financial_report', __('Enable financial report'));
        $show->field('enable_milk_sales_report', __('Enable milk sales report'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PersonalSetting());

        $form->number('user_id', __('User id'));
        $form->text('enable_sms_notification', __('Enable sms notification'))->default('No');
        $form->text('sms_phone_number', __('Sms phone number'));
        $form->text('paid_for_sms_notification', __('Paid for sms notification'))->default('No');
        $form->text('enable_email_notification', __('Enable email notification'))->default('No');
        $form->textarea('email_address', __('Email address'));
        $form->text('farm_worker_can_view_data', __('Farm worker can view data'))->default('Yes');
        $form->text('farm_worker_can_edit_data', __('Farm worker can edit data'))->default('Yes');
        $form->text('farm_worker_can_add_data', __('Farm worker can add data'))->default('Yes');
        $form->text('farm_worker_can_delete_data', __('Farm worker can delete data'))->default('Yes');
        $form->text('enable_automated_reports', __('Enable automated reports'))->default('No');
        $form->text('report_frequency', __('Report frequency'))->default('Monthly');
        $form->text('enable_milk_production_report', __('Enable milk production report'))->default('No');
        $form->text('enable_animal_health_report', __('Enable animal health report'))->default('No');
        $form->text('enable_animal_sales_report', __('Enable animal sales report'))->default('No');
        $form->text('enable_animal_birth_report', __('Enable animal birth report'))->default('No');
        $form->text('enable_animal_death_report', __('Enable animal death report'))->default('No');
        $form->text('enable_animal_movement_report', __('Enable animal movement report'))->default('No');
        $form->text('enable_animal_treatment_report', __('Enable animal treatment report'))->default('No');
        $form->text('enable_animal_vaccination_report', __('Enable animal vaccination report'))->default('No');
        $form->text('enable_animal_weighing_report', __('Enable animal weighing report'))->default('No');
        $form->text('enable_animal_tagging_report', __('Enable animal tagging report'))->default('No');
        $form->text('enable_animal_breeding_report', __('Enable animal breeding report'))->default('No');
        $form->text('enable_financial_report', __('Enable financial report'))->default('No');
        $form->text('enable_milk_sales_report', __('Enable milk sales report'))->default('No');

        return $form;
    }
}
