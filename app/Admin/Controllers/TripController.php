<?php

namespace App\Admin\Controllers;

use App\Models\Trip;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TripController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Trip';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Trip());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('transporter_id', __('Transporter id'));
        $grid->column('transporter_name', __('Transporter name'));
        $grid->column('transporter_nin', __('Transporter nin'));
        $grid->column('transporter_phone_number_1', __('Transporter phone number 1'));
        $grid->column('transporter_phone_number_2', __('Transporter phone number 2'));
        $grid->column('transporter_photo', __('Transporter photo'));
        $grid->column('vehicle_type', __('Vehicle type'));
        $grid->column('vehicle_registration_number', __('Vehicle registration number'));
        $grid->column('has_trip_started', __('Has trip started'));
        $grid->column('has_trip_ended', __('Has trip ended'));
        $grid->column('trip_start_time', __('Trip start time'));
        $grid->column('trip_end_time', __('Trip end time'));
        $grid->column('start_latitude', __('Start latitude'));
        $grid->column('start_longitude', __('Start longitude'));
        $grid->column('current_latitude', __('Current latitude'));
        $grid->column('current_longitude', __('Current longitude'));
        $grid->column('trip_destination_type', __('Trip destination type'));
        $grid->column('trip_destination_id', __('Trip destination id'));
        $grid->column('trip_destination_latitude', __('Trip destination latitude'));
        $grid->column('trip_destination_longitude', __('Trip destination longitude'));
        $grid->column('trip_destination_address', __('Trip destination address'));
        $grid->column('trip_destination_phone_number', __('Trip destination phone number'));
        $grid->column('trip_destination_contact_person', __('Trip destination contact person'));
        $grid->column('trip_details', __('Trip details'));

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
        $show = new Show(Trip::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('transporter_id', __('Transporter id'));
        $show->field('transporter_name', __('Transporter name'));
        $show->field('transporter_nin', __('Transporter nin'));
        $show->field('transporter_phone_number_1', __('Transporter phone number 1'));
        $show->field('transporter_phone_number_2', __('Transporter phone number 2'));
        $show->field('transporter_photo', __('Transporter photo'));
        $show->field('vehicle_type', __('Vehicle type'));
        $show->field('vehicle_registration_number', __('Vehicle registration number'));
        $show->field('has_trip_started', __('Has trip started'));
        $show->field('has_trip_ended', __('Has trip ended'));
        $show->field('trip_start_time', __('Trip start time'));
        $show->field('trip_end_time', __('Trip end time'));
        $show->field('start_latitude', __('Start latitude'));
        $show->field('start_longitude', __('Start longitude'));
        $show->field('current_latitude', __('Current latitude'));
        $show->field('current_longitude', __('Current longitude'));
        $show->field('trip_destination_type', __('Trip destination type'));
        $show->field('trip_destination_id', __('Trip destination id'));
        $show->field('trip_destination_latitude', __('Trip destination latitude'));
        $show->field('trip_destination_longitude', __('Trip destination longitude'));
        $show->field('trip_destination_address', __('Trip destination address'));
        $show->field('trip_destination_phone_number', __('Trip destination phone number'));
        $show->field('trip_destination_contact_person', __('Trip destination contact person'));
        $show->field('trip_details', __('Trip details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Trip());

        $form->number('transporter_id', __('Transporter id'));
        $form->text('transporter_name', __('Transporter name'));
        $form->text('transporter_nin', __('Transporter nin'));
        $form->text('transporter_phone_number_1', __('Transporter phone number 1'));
        $form->text('transporter_phone_number_2', __('Transporter phone number 2'));
        $form->textarea('transporter_photo', __('Transporter photo'));
        $form->text('vehicle_type', __('Vehicle type'));
        $form->text('vehicle_registration_number', __('Vehicle registration number'));
        $form->text('has_trip_started', __('Has trip started'));
        $form->text('has_trip_ended', __('Has trip ended'));
        $form->text('trip_start_time', __('Trip start time'));
        $form->text('trip_end_time', __('Trip end time'));
        $form->text('start_latitude', __('Start latitude'));
        $form->text('start_longitude', __('Start longitude'));
        $form->text('current_latitude', __('Current latitude'));
        $form->text('current_longitude', __('Current longitude'));
        $form->text('trip_destination_type', __('Trip destination type'));
        $form->number('trip_destination_id', __('Trip destination id'));
        $form->text('trip_destination_latitude', __('Trip destination latitude'));
        $form->text('trip_destination_longitude', __('Trip destination longitude'));
        $form->text('trip_destination_address', __('Trip destination address'));
        $form->text('trip_destination_phone_number', __('Trip destination phone number'));
        $form->text('trip_destination_contact_person', __('Trip destination contact person'));
        $form->textarea('trip_details', __('Trip details'));

        return $form;
    }
}
