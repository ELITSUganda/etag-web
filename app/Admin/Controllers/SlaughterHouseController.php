<?php

namespace App\Admin\Controllers;

use App\Models\AdminRoleUser;
use App\Models\Location;
use App\Models\SlaughterHouse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SlaughterHouseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Slaughter houses';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new SlaughterHouse());
        $grid->disableBatchActions();

        $grid->model()->orderBy('id', 'desc');
        $grid->column('name', __('Name'))->sortable();
        $grid->column('district_id', __('District'))->display(function () {
            return $this->district->name;
        })->sortable();
        $grid->column('subcounty_id', __('Subcounty'))->display(function () {
            return $this->subcounty->name;
        })->sortable();

        $grid->column('administrator_id', __('Administrator'))->display(function () {
            return $this->admin->name;
        })->sortable();

        $grid->column('details', __('Details'));
        $grid->column('gps_lati', __('Gps lati'));
        $grid->column('gps_long', __('Gps long'));

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
        $show = new Show(SlaughterHouse::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('subcounty_id', __('Subcounty id'));
        $show->field('name', __('Name'));
        $show->field('details', __('Details'));
        $show->field('gps_lati', __('Gps lati'));
        $show->field('gps_long', __('Gps long'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SlaughterHouse());

        $form->text('name', __('Name'))->rules('required');


        $subs = Location::get_sub_counties();
        $form->select('subcounty_id', 'Subcounty')->options(
            $subs->pluck('name_text', 'id')
        )->rules('required');

        $houses = [];
        foreach (AdminRoleUser::where([
            'role_id' => 5
        ])->get() as $key => $v) {
            if ($v->owner == null) {
                continue;
            }
            $houses[$v->user_id] = $v->owner->name;
        }

        $form->select('administrator_id', 'Abattoir administrator')->options(
            $houses
        )->rules('required');

        $form->textarea('details', __('Details about Abattoir'));

        $form->latlong('gps_lati', 'gps_long', 'Abattoir Location on map')->height(300);



        return $form;
    }
}
