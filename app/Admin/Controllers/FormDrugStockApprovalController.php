<?php

namespace App\Admin\Controllers;

use App\Models\FormDrugStockApproval;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Form\NestedForm;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FormDrugStockApprovalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'FormDrugStockApproval';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FormDrugStockApproval());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('applicant_id', __('Applicant id'));
        $grid->column('details', __('Details'));
        $grid->column('status', __('Status'));

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
        $show = new Show(FormDrugStockApproval::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('applicant_id', __('Applicant id'));
        $show->field('details', __('Details'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FormDrugStockApproval());
        $u = Admin::user();
        $form->hidden('applicant_id', __('Applicant id'))->default($u->id);

        $form->textarea('details', __('Details'));
        $form->hidden('status', __('Status'));


        $form->hasMany('drugs_list', function (NestedForm $form) {
            $form->setWidth(8, 4);
            $form->text('name', __('Name'))->required();
        });


        return $form;
    }
}
