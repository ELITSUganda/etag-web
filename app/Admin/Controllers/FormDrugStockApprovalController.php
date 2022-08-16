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
        /* $item = FormDrugStockApproval::find(2);
        $item->details = time();
        $item->save();
        dd($item); */

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

        if (!$u->isRole('nda')) {
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
        }


        $form_data = null;
        $id = 0;

        if ($form->isEditing()) {

            $cert = null;
            $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri_segments = explode('/', $uri_path);
            $id = ((int)($uri_segments[4]));
            $form_data = FormDrugStockApproval::find($id);

            if ($form_data == null) {
                return admin_error(
                    'Form not found.',
                    ''
                );
            }

            $cert = FormDrugSeller::Where([
                'applicant_id' => $form_data->applicant_id
            ])->first();

            if ($cert == null) {
                return admin_error(
                    'Form distributor form not found.',
                    ''
                );
            }


            /* "id" => 5
            "created_at" => "2022-08-09 16:52:25"
            "updated_at" => "2022-08-09 16:55:26"
            "name" => "Muhindo and Sons"
            "phone_number" => "+256706638494"
            "nin" => "10299100093"
            "license" => "1021009221"
            "address" => "Near Ndere Cultural Centre, Plot 4505 Kira Rd, Ntinda - Kisaasi Rd, Kampala."
            "sub_county_id" => 5
            "applicant_id" => 4
            "approved_by" => 0 
            "type" => "Importer"
            "details" => "Some details ..."
            "status" => 1 */
        }


        $form->hidden('applicant_id', __('Applicant id'))->default($u->id);

        $form->display('',  __('Applican\'s name'))->default($cert->name);
        $form->display('',  __('Applican\'s phone number'))->default($cert->phone_number);
        $form->display('',  __('Applican\'s NIN'))->default($cert->nin);
        $form->display('',  __('Applican\'s license number'))->default($cert->license);
        $form->display('',  __('Applican\'s address'))->default($cert->address);

        /*         "sub_county_id" => 7
        "applicant_id" => 10
        "approved_by" => 0
        "type" => "Importer"
        "details" => "Simple"
        "status" => 1 */
        $form->divider();
        $form->html('<h4>Click on NEW to add drugs and their quantity.</h4>');

        $form->hasMany('items', "Drugs.", function (Form\NestedForm $form) {
            $cats = [];
            foreach (DrugCategory::all() as $val) {
                $cats[$val->id] = $val->name . " - (in $val->unit)";
            }
            $form->setWidth(8, 4);
            $form->select('drug_category_id', __('Select Drug'))
                ->options($cats)
                ->required();
            $form->text('name', __('Drug name'))->rules('required');
            $form->date('expiry_date', __('Expiry date'))->rules('required');
            $form->text('quantity', __('Quantity'))
                ->attribute('type', 'number')
                ->rules('int|required');
            $form->text('manufacturer', __('Drug manufacturer'))->rules('required');
            $form->text('batch_number', __('Batch number'))->rules('required');
            $form->text('ingredients', __('Drug ingredients'));

            $form->text('selling_price', __('Your selling price'));
            $form->image('image', __('Drugs photo'));
            $form->text('note', __('Details'));
            $form->hidden('status', __('status'))->default(0);

            /* 
  

    "" => null
    "details" => null
    "done_approving" => 0
            
            */
        });

        $form->text('details', __('General note'))
            ->help("Type here extra information that you would like us to know about this application");



        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        $form->divider();

        if ($form->isEditing() && $u->isRole('nda')) {
            $form->select('status', __('Decision'))
                ->options([
                    1 => 'Approve',
                    0 => 'Not Approve',
                ])->required();
        } else {
            $form->hidden('status', __('Status'))->default(0);
        }


        return $form;
    }
}
