<?php

namespace App\Admin\Controllers;

use App\Models\Location;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SubCountyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Subcounties';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Location());
        $grid->model()->where('type', 'Sub-County');

        $grid->disableBatchActions();
        $grid->disableExport();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('name', 'Name');
            $districts = [];
            foreach (Location::get_districts() as $key => $value) {
                $districts[$value->id] = $value->name . " - #" . $value->id;
            }

            $filter->equal('parent', 'Parent')->select($districts);
        });
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->quickSearch('name')->placeholder("Search by name...");
        $grid->column('id', __('ID'))->sortable()->width(100);
        $grid->column('parent', __('District'))->display(function () {
            if ($this->parent == 0) {
                return $this->name;
            }
            $district = $this->district;
            if ($district == null) {
                return 'N/A';
            }
            return $district->name;
        })->sortable();

        //sub county
        $grid->column('name', __('Sub-County'))->display(function () {
            if ($this->parent == 0) {
                return 'N/A';
            }
            return $this->name;
        })->sortable();



        //code
        $grid->column('code', __('ISO CODE'))->sortable();

        //processed
        $grid->column('processed', __('Processed'))->sortable()
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
                'FAILED' => 'Failed',
            ])->label([
                'Yes' => 'success',
                'No' => 'danger',
                'FAILED' => 'warning',
            ])->hide();
        $grid->disableActions();

        //farm_count
        $grid->column('farm_count', __('Farms'))->sortable();
        //cattle_count
        $grid->column('cattle_count', __('Cattle'))->sortable();
        //goat_count
        $grid->column('goat_count', __('Goats'))->sortable();
        //sheep_count
        $grid->column('sheep_count', __('Sheep'))->sortable();

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

        $form->text('name', __('Location Name'))->rules('required')->required();
        $districts = [];
        foreach (Location::get_districts() as $key => $value) {
            $districts[$value->id] = $value->name . " - #" . $value->id;
        }

        $form->select('parent', __('Parent District'))
            ->options($districts)->rules('required')->required();



        $form->hidden('type')->default('Sub-County');

        $form->radio('locked_down', 'Quarantine')->options([
            0 => 'Opened',
            1 => 'Lock down',
        ])
            ->default(0)
            ->help('NOTE: Lock down means no movement of livestock will be allowed in that region  until opened.');

        return $form;
    }
}
