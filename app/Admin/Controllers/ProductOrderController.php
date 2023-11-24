<?php

namespace App\Admin\Controllers;

use App\Models\ProductOrder;
use App\Models\Utils;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProductOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Products';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProductOrder());

        $grid->disableBatchActions();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableCreateButton();

        $grid->model()->orderBy('id', 'desc');

        //filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('name', __('Name'));
            $filter->like('phone_number', __('Phone number'));
            $filter->like('address', __('Address'));
            $filter->like('note', __('Note'));
            $filter->like('product_data', __('Product data'));
            //total_price range filter
            $filter->equal('status', __('Status'))->select([
                'Pending' => 'Pending',
                'Completed' => 'Completed',
                'Cancelled' => 'Cancelled'
            ]);
            $filter->equal('order_is_paid', __('is paid'))->select([
                0 => 'No',
                1 => 'Yes'
            ]);
            $filter->between('created_at', __('Created at'))->datetime();
        });

        $grid->column('id', __('#ID'))->sortable();
        $grid->column('created_at', __('Created'))
            ->sortable()
            ->display(function ($created_at) {
                return Utils::my_date_1($created_at);
            });

        //editable by select
        $grid->column('status', __('Status'))->filter([
            'Pending' => 'Pending',
            'Completed' => 'Completed',
            'Cancelled' => 'Cancelled'
        ])

            ->editable('select', [
                'Pending' => 'Pending',
                'Shipping' => 'Shipping',
                'Delivered' => 'Delivered',
                'Cancelled' => 'Cancelled'
            ]);
        $grid->column('customer_id', __('Customer'))
            ->display(function ($customer_id) {
                if ($this->customer != null) {
                    return $this->customer->name;
                }
                return "Customer #" . $customer_id;
            });

        $grid->column('product_data', __('Product data'))->hide();
        $grid->column('customer_data', __('Customer data'))->hide();
        $grid->column('address', __('Delivery Address'))->sortable();
        $grid->column('note', __('Note'));
        $grid->column('name', __('Name'))->hide();
        $grid->column('phone_number', __('Phone number'))->sortable();
        $grid->column('latitude', __('Latitude'))->hide();
        $grid->column('longitude', __('Longitude'))->hide();
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('total_price', __('Total price'))->sortable();
        $grid->column('order_is_paid', __('is paid'))
            ->filter([
                0 => 'No',
                1 => 'Yes'
            ])
            ->using([
                0 => 'Not Paid',
                1 => 'Paid'
            ])
            ->label([
                0 => 'danger',
                1 => 'success'
            ]);

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
        $show = new Show(ProductOrder::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('status', __('Status'));
        $show->field('customer_id', __('Customer id'));
        $show->field('product_id', __('Product id'));
        $show->field('product_data', __('Product data'));
        $show->field('customer_data', __('Customer data'));
        $show->field('address', __('Address'));
        $show->field('note', __('Note'));
        $show->field('name', __('Name'));
        $show->field('phone_number', __('Phone number'));
        $show->field('latitude', __('Latitude'));
        $show->field('longitude', __('Longitude'));
        $show->field('phone_number_2', __('Phone number 2'));
        $show->field('order_is_paid', __('Order is paid'));
        $show->field('total_price', __('Total price'));
        $show->field('type', __('Type'));
        $show->field('payment_link', __('Payment link'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ProductOrder());

        $form->text('status', __('Status'))->default('Pending');
        $form->number('customer_id', __('Customer id'));
        $form->number('product_id', __('Product id'));
        $form->textarea('product_data', __('Product data'));
        $form->textarea('customer_data', __('Customer data'));
        $form->textarea('address', __('Address'));
        $form->textarea('note', __('Note'));
        $form->text('name', __('Name'));
        $form->text('phone_number', __('Phone number'));
        $form->text('latitude', __('Latitude'));
        $form->text('longitude', __('Longitude'));
        $form->textarea('phone_number_2', __('Phone number 2'));
        $form->switch('order_is_paid', __('Order is paid'));
        $form->number('total_price', __('Total price'));
        $form->text('type', __('Type'));
        $form->textarea('payment_link', __('Payment link'));
        //has many items
        //
        $form->hasMany('items', 'Items', function (Form\NestedForm $form) {
 
            $form->number('product_order_id', __('Product order id'));
            $form->number('product_id', __('Product id'));
            $form->number('quantity', __('Quantity'));
            $form->number('price', __('Price'));
            $form->number('total', __('Total'));
            $form->text('product_name', __('Product name'));
            $form->text('product_photo', __('Product photo'));
        });

        return $form;
    }
}
