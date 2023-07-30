<?php

namespace App\Admin\Controllers;

use App\Models\WholesaleOrder;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class WholesaleOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Wholesale Orders';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WholesaleOrder());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('customer_id', __('Customer id'));
        $grid->column('supplier_id', __('Supplier id'));
        $grid->column('status', __('Status'));
        $grid->column('delivery_type', __('Delivery type'));
        $grid->column('customer_name', __('Customer name'));
        $grid->column('customer_contact', __('Customer contact'));
        $grid->column('customer_address', __('Customer address'));
        $grid->column('customer_gps_patitude', __('Customer gps patitude'));
        $grid->column('customer_gps_longitude', __('Customer gps longitude'));
        $grid->column('customer_note', __('Customer note'));
        $grid->column('details', __('Details'));

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
        $show = new Show(WholesaleOrder::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('customer_id', __('Customer id'));
        $show->field('supplier_id', __('Supplier id'));
        $show->field('status', __('Status'));
        $show->field('delivery_type', __('Delivery type'));
        $show->field('customer_name', __('Customer name'));
        $show->field('customer_contact', __('Customer contact'));
        $show->field('customer_address', __('Customer address'));
        $show->field('customer_gps_patitude', __('Customer gps patitude'));
        $show->field('customer_gps_longitude', __('Customer gps longitude'));
        $show->field('customer_note', __('Customer note'));
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
        $form = new Form(new WholesaleOrder());

        if ($form->isCreating()) {
            $form->select('customer_id', 'Select user')
                ->options(function ($id) {
                    $parent = Administrator::find($id);
                    if ($parent != null) {
                        return [$parent->id =>  $parent->name];
                    }
                })
                ->rules('required')
                ->ajax(
                    url('/api/wholesellers')
                );
        } else {
            $form->select('customer_id', 'Select user')
                ->options(function ($id) {
                    $parent = Administrator::find($id);
                    if ($parent != null) {
                        return [$parent->id =>  $parent->name];
                    }
                })->readOnly();
        }
        $form->hidden('supplier_id', __('Supplier id'))->default(1);
        $form->radioCard('status', __('Status'))
            ->options([
                'Pending' => 'Pending',
                'Processing' => 'Processing',
                'Shipping' => 'Shipping',
                'Delivered' => 'Delivered',
                'Completed' => 'Completed',
                'Canceled' => 'Canceled',
            ])
            ->default('Pending');

        $form->radio('delivery_type', __('Delivery Methord'))
            ->options([
                'By Customer' => 'By Customer',
                'By Supplier' => 'By Supplier',
            ])->rules('required');

        $form->text('customer_name', __('Customer Name'))
            ->default(Auth::user()->name);
        $form->text('customer_contact', __('Customer contact'))
            ->default(Auth::user()->phone_number);
        $form->text('customer_address', __('Customer address'))
            ->default(Auth::user()->address);
        $form->text('customer_note', __('Customer note'));
        $form->text('customer_gps_patitude', __('Customer gps patitude'));
        $form->text('customer_gps_longitude', __('Customer gps longitude'));
        $form->divider('ORDER ITEMS');

        //$form->textarea('details', __('Details'));

        $form->hasMany('order_items', function ($form) {

            $form->select('wholesale_drug_stock_id', 'Select stock');
            /* 
            '',
            'quantity',
        'description',
        'wholesale_order_id',
            
            */
        });

        return $form;
    }
}
