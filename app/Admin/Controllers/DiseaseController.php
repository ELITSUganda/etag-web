<?php

namespace App\Admin\Controllers;

use App\Models\Disease;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DiseaseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Disease';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Disease());
        $grid->disableBatchActions();
        $grid->quickSearch('name')->placeholder('Search by name');

        $grid->column('id', __('Id'))
            ->width(40)
            ->sortable();

        $grid->column('photo', __('Photo'))
            ->width(55)
            ->sortable()
            ->lightbox(['width' => 50, 'height' => 50]);

        $grid->column('name', __('Name'))->sortable();
        $grid->column('details', __('Description'))->sortable();

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
        $show = new Show(Disease::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
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
        $form = new Form(new Disease());

        $form->text('name', __('Name'))->required();
        $form->textarea('details', __('Description'));
        $form->image('photo', __('Photo'));

        return $form;
    }
}
