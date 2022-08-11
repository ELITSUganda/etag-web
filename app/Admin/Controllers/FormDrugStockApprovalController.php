<?php

namespace App\Admin\Controllers;

use App\Models\DrugCategory;
use App\Models\FormDrugSeller;
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
    protected $title = 'Drugs stock approval form';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FormDrugStockApproval());

        $grid->column('id', __('Application #ID'))->sortable();


        $grid->column('phone', __('Applican\'s number'))->display(function () {
            $cert = FormDrugSeller::Where([
                'applicant_id' => $this->applicant->id
            ])->first();
            return $cert->name;
        });

        $grid->column('phone_number', __('Applican\'s phone number'))->display(function () {
            $cert = FormDrugSeller::Where([
                'applicant_id' => $this->applicant->id
            ])->first();
            return $cert->phone_number;
        });
        $grid->column('NIN', __('Applican\'s phone number'))->display(function () {
            $cert = FormDrugSeller::Where([
                'applicant_id' => $this->applicant->id
            ])->first();
            return $cert->nin;
        });
        $grid->column('NIN', __('License number'))->display(function () {
            $cert = FormDrugSeller::Where([
                'applicant_id' => $this->applicant->id
            ])->first();
            return $cert->license;
        });

        $grid->column('items', __('Drugs'))->display(function () {
            return count($this->items);
        });


        $grid->column('details', __('Details'))->hide();
        $grid->column('status', __('Status'));

        return $grid;
    }

    // $form->display('',  __('Applican\'s name'))->value($cert->name);
    // $form->display('',  __('Applican\'s phone number'))->value($cert->);
    // $form->display('',  __('Applican\'s phone '))->value($cert->);
    // $form->display('',  __('Applican\'s '))->value($cert->license);
    // $form->display('',  __('Applican\'s address'))->value($cert->address);

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
        $cert = FormDrugSeller::Where([
            'applicant_id' => $u->id
        ])->first();
        if ($cert == null) {
            return admin_error(
                'You are not an approved drug seller or distributor.',
                'You need to apply for drug distributor/seller aproval before you procees. 
            You can find this form under application forms section.'
            );
        }
        $form->hidden('applicant_id', __('Applicant id'))->default($u->id);

        $form->display('',  __('Applican\'s name'))->value($cert->name);
        $form->display('',  __('Applican\'s phone number'))->value($cert->phone_number);
        $form->display('',  __('Applican\'s NIN'))->value($cert->nin);
        $form->display('',  __('Applican\'s license number'))->value($cert->license);
        $form->display('',  __('Applican\'s address'))->value($cert->address);

        /*         "sub_county_id" => 7
        "applicant_id" => 10
        "approved_by" => 0
        "type" => "Importer"
        "details" => "Simple"
        "status" => 1 */
        $form->divider();
        $form->hidden('status', __('Status'))->default(0);
        $form->hasMany('items', "Click on NEW to add drugs and their quantity.", function (Form\NestedForm $form) {
            $cats = [];
            foreach (DrugCategory::all() as $val) {
                $cats[$val->id] = $val->name . " - (in $val->unit)";
            }
            $form->setWidth(8, 4);
            $form->select('drug_category_id', __('Select Drug'))
                ->options($cats)
                ->required();
            $form->text('quantity', __('Quantity'))
                ->attribute('type', 'number')
                ->rules('int|required');
            $form->text('note', __('Note'))->rules('required');
            $form->hidden('status', __('Note'))->default(0)->rules('int|required');
        });

        $form->text('details', __('General note'))
            ->help("Type here extra information that you would like us to know about this application");



        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();


        return $form;
    }
}
