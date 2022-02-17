<?php

namespace App\Admin\Controllers;

use App\Models\District;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Auth\Database\Administrator;

class DistrictController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'District';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
       
        $grid = new Grid(new District());

        $grid->column('id', __('Id'))->sortable()->width(50);
        $grid->column('name', __('Name'))->sortable()->width(100);
        $grid->column('code', __('CODE'))->sortable()->width(80);
        $grid->column('administrator_id', __('D.V.O'))->display(function ($user) {
            $_user = Administrator::find($user);
            if (!$_user) {
                return "-";
            }
            return $_user->name;
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
        $show = new Show(District::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('code', __('ISO-CODE'));
        $show->field('detail', __('Detail'));
        $show->field('administrator_id', __('Administrator id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new District());
        $form->setWidth(8, 4);
        $admins = [];
        foreach (Administrator::all() as $key => $v) {
            if (!$v->isRole('dvo')) {
                continue;
            }
            $admins[$v->id] = $v->name . " - " . $v->id;
        }


        $form->text('name', __('Name'))->required();
        $form->text('code', __('ISO-CODE'))->required();
        $form->select('administrator_id', __('District veterinary officer'))
            ->options($admins)
            ->required();

        $form->textarea('detail', __('Detail'));

        return $form;
    }
}
