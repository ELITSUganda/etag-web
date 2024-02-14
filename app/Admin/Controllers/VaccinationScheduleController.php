<?php

namespace App\Admin\Controllers;

use App\Models\VaccinationSchedule;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class VaccinationScheduleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Vaccination Schedules';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new VaccinationSchedule());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('farm_id', __('Farm id'));
        $grid->column('applicant_id', __('Applicant id'));
        $grid->column('approver_id', __('Approver id'));
        $grid->column('veterinary_officer_id', __('Veterinary officer id'));
        $grid->column('district_id', __('District id'));
        $grid->column('sub_county_id', __('Sub county id'));
        $grid->column('gps_latitute', __('Gps latitute'));
        $grid->column('schedule_date', __('Schedule date'));
        $grid->column('actual_date', __('Actual date'));
        $grid->column('verification_code', __('Verification code'));
        $grid->column('gps_longitude', __('Gps longitude'));
        $grid->column('status', __('Status'));
        $grid->column('vaccination_type', __('Vaccination type'));
        $grid->column('applicant_name', __('Applicant name'));
        $grid->column('applicant_contact', __('Applicant contact'));
        $grid->column('applicant_address', __('Applicant address'));
        $grid->column('applicant_message', __('Applicant message'));
        $grid->column('veterinary_officer_message', __('Veterinary officer message'));
        $grid->column('dvo_message', __('Dvo message'));
        $grid->column('reason_for_rejection', __('Reason for rejection'));
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
        $show = new Show(VaccinationSchedule::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('farm_id', __('Farm id'));
        $show->field('applicant_id', __('Applicant id'));
        $show->field('approver_id', __('Approver id'));
        $show->field('veterinary_officer_id', __('Veterinary officer id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('gps_latitute', __('Gps latitute'));
        $show->field('schedule_date', __('Schedule date'));
        $show->field('actual_date', __('Actual date'));
        $show->field('verification_code', __('Verification code'));
        $show->field('gps_longitude', __('Gps longitude'));
        $show->field('status', __('Status'));
        $show->field('vaccination_type', __('Vaccination type'));
        $show->field('applicant_name', __('Applicant name'));
        $show->field('applicant_contact', __('Applicant contact'));
        $show->field('applicant_address', __('Applicant address'));
        $show->field('applicant_message', __('Applicant message'));
        $show->field('veterinary_officer_message', __('Veterinary officer message'));
        $show->field('dvo_message', __('Dvo message'));
        $show->field('reason_for_rejection', __('Reason for rejection'));
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
        $form = new Form(new VaccinationSchedule());

        $form->number('farm_id', __('Farm id'));
        $form->number('applicant_id', __('Applicant id'));
        $form->number('approver_id', __('Approver id'));
        $form->number('veterinary_officer_id', __('Veterinary officer id'));
        $form->number('district_id', __('District id'));
        $form->number('sub_county_id', __('Sub county id'));
        $form->text('gps_latitute', __('Gps latitute'));
        $form->date('schedule_date', __('Schedule date'))->default(date('Y-m-d'));
        $form->date('actual_date', __('Actual date'))->default(date('Y-m-d'));
        $form->text('verification_code', __('Verification code'));
        $form->text('gps_longitude', __('Gps longitude'));
        $form->text('status', __('Status'))->default('Pending');
        $form->text('vaccination_type', __('Vaccination type'))->default('FMD');
        $form->text('applicant_name', __('Applicant name'));
        $form->text('applicant_contact', __('Applicant contact'));
        $form->textarea('applicant_address', __('Applicant address'));
        $form->textarea('applicant_message', __('Applicant message'));
        $form->textarea('veterinary_officer_message', __('Veterinary officer message'));
        $form->textarea('dvo_message', __('Dvo message'));
        $form->textarea('reason_for_rejection', __('Reason for rejection'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
