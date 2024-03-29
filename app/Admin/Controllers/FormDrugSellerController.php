<?php

namespace App\Admin\Controllers;

use App\Models\FormDrugSeller;
use App\Models\SubCounty;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FormDrugSellerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Drug distributor registration form';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FormDrugSeller());
        $u = Admin::user();
        if (!$u->isRole('nda')) {
            $grid->model()->where('applicant_id', '=', Admin::user()->id);
        }
        $has_form = false;

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $app = FormDrugSeller::where([
            'applicant_id' => $u->id
        ])->first();

        if ($app != null) {
            $has_form = false;

            if (!$u->isRole('nda')) {
                if ($app->status == 1) {
                    $grid->disableActions();
                }
                /*                 $grid->actions(function ($actions) {
                    $actions->disableEdit();
                    $actions->disableDelete();
                }); */
            }

            $grid->disableCreateButton();
        } else {
            $has_form = true;
        }


        if ($u->isRole('nda')) {
            $grid->disableCreateButton();
            $grid->actions(function ($actions) {
                $actions->disableView();
                $actions->disableDelete();
            });
        } else {
            $grid->disableBatchActions();
            $grid->disableExport();
            $grid->disableFilter();
        }


        //$grid->disableActions();

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('phone_number', __('Contact'))->sortable();
        $grid->column('nin', __('Nin'))->sortable();
        $grid->column('license', __('License'));
        $grid->column('sub_county_id', __('Sub county'))
            ->display(function () {
                return $this->sub_county->name;
            });

        $grid->column('type', __('Type'));

        $grid->column('status', __('Status'))
            ->using([
                0 => 'Pending',
                2 => 'Rejected',
                1 => 'Approved',
            ], 'warning')
            ->label([
                0 => 'warning',
                2 => 'danger',
                1 => 'success',
            ], 'warning');

        $grid->column('status_comment', __('NDA\'s Comment'));

        if ($u->isRole('nda')) {

            $grid->column('applicant_id', __('Applicant id'))
                ->display(function () {
                    return $this->applicant->name;
                });

            $grid->column('approved_by', __('Approved by'))->hide();
        }

        $grid->column('details', __('Details'))->hide();
        $grid->column('address', __('Address'))->hide();
        $grid->column('created_at', __('Created'));

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
        $show = new Show(FormDrugSeller::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('phone_number', __('Phone number'));
        $show->field('nin', __('Nin'));
        $show->field('license', __('License'));
        $show->field('address', __('Address'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('applicant_id', __('Applicant id'));
        $show->field('approved_by', __('Approved by'));
        $show->field('type', __('Type'));
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
        $form = new Form(new FormDrugSeller());
        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();
        $sub_counties = [];
        foreach (SubCounty::all() as $key => $p) {
            $sub_counties[$p->id] = $p->name . ", " .
                $p->district->name . ".";
        }
        $u = Admin::user();

        $form->setWidth(6, 3);



        if (!$u->isRole('nda')) {

            $form->hidden('applicant_id', __('Applicant id'))->default($u->id);
            $form->hidden('approved_by', __('Approved by'))->default(0);
            $form->hidden('status', __('status'))->default(0);




            $form->html('
                <h3>Enterprise location</h3>
                <p>Before you proceed, <b>This form should be field at premises of drugs enterprise.</b> 
                We need to know exactly where your drugs enterprise is located on the map.
                therefore we will need to GPS coordites for your enterprise location.</p>
                <br>
                <b>Please click on the button below to enable use collect your enterprise GPS Coordicates.</b>
                <br><br> 
                <a id="location-picker" href="javascript:;" class="btn btn-info btn-lg">PICK MY GPS LOCATION</a>
            ');

            $form->text('latitude', __('GPS latitude'))
                ->rules('required')
                ->readonly()

                ->required();
            $form->text('longitude', __('GPS longitude'))
                ->rules('required')
                ->readonly()
                ->required();

            $form->divider();
            $form->text('name', __('Enterprise name'))
                ->help("Name on the lisence")
                ->required();

            $form->select('type', __('Nature of your enterprise'))
                ->options([
                    'Exporter' => 'Exporter',
                    'Importer' => 'Importer',
                    'Wholesaler' => 'Wholesaler',
                    'Retailer' => 'Retailer',
                ])->required();


            $form->text('license', __('License number'))
                ->help("Your existing operation License number as drug seller")
                ->required();

            $form->date('expiry', __('License expiry date'))->required();

            $form->text('nin', __('National ID  number'));



            $form->text('phone_number', __('Phone number'))
                ->required();

            $form->select('sub_county_id', __('Sub county'))
                ->required()
                ->help("Where is your enterprise located?")
                ->options($sub_counties)
                ->default(1);

            $form->text('address', __('Enterprise Address'))
                ->help("Where your Enterprise is physically located")
                ->required();


            $form->textarea('details', __('Enterprise profile'))
                ->help("Write what buyers will see about your enterprise.")
                ->required();
        } else {

            $form->textarea('latitube', __('latitube'))->required();
            $form->textarea('longitude', __('longitude'))->required();

            $form->display('name', 'Enterprise name');
            $form->display('type', 'Nature of your enterprise');
            $form->display('nin', 'National ID  number');
            $form->display('phone_number', 'Phone number');
            $form->display('license', 'Operating License');
            $form->display('address', 'Enterprise address');
            $form->divider();

            $form->select('status', __('Application status'))
                ->options([
                    1 => 'Approved',
                    2 => 'Rejected',
                    0 => 'Pending',

                ])
                ->when(2, function (Form $form) {
                    $form->textarea('status_comment', __('NDA\'s comment'))
                        ->rules('required');
                })
                ->rules('required');
        }

        Admin::js(url('assets/js/form-drug-sellers.js'));

        return $form;
    }
}
