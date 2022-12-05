<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class MilkController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Milk production records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Event());

        //$grid->disableActions();
        $grid->disableBatchActions();
        $grid->model()->where([
            'type' => 'Milking',
            'administrator_id' => Auth::user()->id,
        ])->orderBy('id', 'Desc');
        $grid->column('id', __('Records Id'))->hide()->sortable();
        $grid->column('created_at', __('Created'))
            ->display(function ($date) {
                return Utils::my_date_time($date);
            })
            ->sortable();
        /* $grid->column('administrator_id', __('Administrator id')); */
        /* $grid->column('district_id', __('District id')); 
        $grid->column('sub_county_id', __('Sub county id'));
        $grid->column('parish_id', __('Parish id'));
        $grid->column('weight', __('Weight'));*/
        $grid->column('farm_id', __('Farm'))
            ->display(function ($id) {
                return Utils::get_object(Farm::class, $id)->holding_code;
            })->sortable();
        $grid->column('animal_id', __('Animal'))
            ->display(function ($id) {
                return Utils::get_object(Animal::class, $id)->v_id;
            })->sortable();
        $grid->column('e_id', __('E-id'))->hide();
        $grid->column('milk', __('Milk quantity'))
            ->display(function ($count) {
                return $count . " Ltrs";
            })->sortable();

        $grid->column('description', __('Description'));
        $grid->column('detail', __('Detail'))->hide();

        $grid->column('session_id', __('Session'))->display(function ($id) {
            return "#$id";
        })->sortable();

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
        $show = new Show(Event::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('parish_id', __('Parish id'));
        $show->field('farm_id', __('Farm id'));
        $show->field('animal_id', __('Animal id'));
        $show->field('type', __('Type'));
        $show->field('approved_by', __('Approved by'));
        $show->field('detail', __('Detail'));
        $show->field('animal_type', __('Animal type'));
        $show->field('disease_id', __('Disease id'));
        $show->field('vaccine_id', __('Vaccine id'));
        $show->field('medicine_id', __('Medicine id'));
        $show->field('is_batch_import', __('Is batch import'));
        $show->field('time_stamp', __('Time stamp'));
        $show->field('import_file', __('Import file'));
        $show->field('description', __('Description'));
        $show->field('temperature', __('Temperature'));
        $show->field('e_id', __('E id'));
        $show->field('v_id', __('V id'));
        $show->field('status', __('Status'));
        $show->field('disease_text', __('Disease text'));
        $show->field('short_description', __('Short description'));
        $show->field('medicine_text', __('Medicine text'));
        $show->field('medicine_quantity', __('Medicine quantity'));
        $show->field('medicine_name', __('Medicine name'));
        $show->field('medicine_batch_number', __('Medicine batch number'));
        $show->field('medicine_supplier', __('Medicine supplier'));
        $show->field('medicine_manufacturer', __('Medicine manufacturer'));
        $show->field('medicine_expiry_date', __('Medicine expiry date'));
        $show->field('medicine_image', __('Medicine image'));
        $show->field('vaccination', __('Vaccination'));
        $show->field('weight', __('Weight'));
        $show->field('milk', __('Milk'));
        $show->field('photo', __('Photo'));
        $show->field('session_id', __('Session id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Event());

        $form->number('administrator_id', __('Administrator id'));
        $form->number('district_id', __('District id'));
        $form->number('sub_county_id', __('Sub county id'));
        $form->number('parish_id', __('Parish id'));
        $form->number('farm_id', __('Farm id'));
        $form->number('animal_id', __('Animal id'));
        $form->text('type', __('Type'));
        $form->text('approved_by', __('Approved by'));
        $form->textarea('detail', __('Detail'));
        $form->textarea('animal_type', __('Animal type'));
        $form->textarea('disease_id', __('Disease id'));
        $form->textarea('vaccine_id', __('Vaccine id'));
        $form->textarea('medicine_id', __('Medicine id'));
        $form->switch('is_batch_import', __('Is batch import'));
        $form->textarea('time_stamp', __('Time stamp'));
        $form->textarea('import_file', __('Import file'));
        $form->textarea('description', __('Description'));
        $form->decimal('temperature', __('Temperature'));
        $form->textarea('e_id', __('E id'));
        $form->textarea('v_id', __('V id'));
        $form->text('status', __('Status'))->default('success');
        $form->textarea('disease_text', __('Disease text'));
        $form->textarea('short_description', __('Short description'));
        $form->textarea('medicine_text', __('Medicine text'));
        $form->textarea('medicine_quantity', __('Medicine quantity'));
        $form->textarea('medicine_name', __('Medicine name'));
        $form->textarea('medicine_batch_number', __('Medicine batch number'));
        $form->textarea('medicine_supplier', __('Medicine supplier'));
        $form->textarea('medicine_manufacturer', __('Medicine manufacturer'));
        $form->textarea('medicine_expiry_date', __('Medicine expiry date'));
        $form->textarea('medicine_image', __('Medicine image'));
        $form->textarea('vaccination', __('Vaccination'));
        $form->decimal('weight', __('Weight'));
        $form->decimal('milk', __('Milk'));
        $form->textarea('photo', __('Photo'));
        $form->text('session_id', __('Session id'));

        return $form;
    }
}
