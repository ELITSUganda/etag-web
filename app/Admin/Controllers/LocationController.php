<?php

namespace App\Admin\Controllers;

use App\Models\Location;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LocationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Location';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Location());

        $grid->disableBatchActions();
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableActions();
        $grid->quickSearch('name')->placeholder("Search by name...");
        $grid->column('id', __('ID'))->hide()->sortable();
        $grid->column('name', __('Name'))->display(function () {
            return $this->name_text;
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
        $show = new Show(Location::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('parent', __('Parent'));
        $show->field('photo', __('Photo'));
        $show->field('order', __('Order'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Location());

        $form->text('name', __('Location Name'))->required();
        $form->select('parent', __('Parent district'))
            ->default(0)
            ->help('Leave this field empty if you are creating a new district.')
            ->options(Location::get_districts()->pluck('name_text', 'id'));



        $admins = [];
        foreach (Administrator::all() as $key => $v) {
            if (!$v->isRole('scvo')) {
                continue;
            }
            $admins[$v->id] = $v->name . " - " . $v->id;
        }


 

        $form->radio('locked_down', 'Quarantine')->options([
            0 => 'Opened',
            1 => 'Lock down',
        ])
            ->default(0)
            ->help('NOTE: Lock down means no movement of livestock will be allowed in that region  until opened.');

        /*         $form->text('details', __('Location description')); */

        return $form;
    }
}
