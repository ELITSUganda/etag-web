<?php

namespace App\Admin\Controllers;

use App\Models\District;
use App\Models\DistrictTagDistributionBatch;
use App\Models\FamerTagsOrder;
use App\Models\Farm;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FamerTagsOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Famer Tags Orders';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FamerTagsOrder());
        $grid->model()->orderBy('created_at', 'desc');
        $grid->column('id', __('ORDER #ID'))->sortable();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('farm_id', __('Farm id'));
        $grid->column('famer_id', __('Famer id'));
        $grid->column('district_id', __('District id'));
        $grid->column('sub_county_id', __('Sub county id'));
        $grid->column('district_tag_distribution_batch_id', __('District tag distribution batch id'));
        $grid->column('farmer_message', __('Farmer message'));
        $grid->column('delivery_address', __('Delivery address'));
        $grid->column('delivery_date', __('Delivery date'));
        $grid->column('order_status', __('Order status'));
        $grid->column('pending_message_sent', __('Pending message sent'));
        $grid->column('shipping_started_message_sent', __('Shipping started message sent'));
        $grid->column('delivered_message_sent', __('Delivered message sent'));
        $grid->column('total_tags_ordered_quantity', __('Total tags ordered quantity'));
        $grid->column('total_tags_ordered_amount', __('Total tags ordered amount'));
        $grid->column('total_tags_delivered_quantity', __('Total tags delivered quantity'));
        $grid->column('flutterwave_amount', __('Flutterwave amount'));
        $grid->column('flutterwave_status', __('Flutterwave status'));
        $grid->column('flutterwave_link', __('Flutterwave link'));
        $grid->column('flutterwave_phone_number', __('Flutterwave phone number'));
        $grid->column('vid_range_start', __('Vid range start'));
        $grid->column('vid_range_end', __('Vid range end'));
        $grid->column('eid_range_start', __('Eid range start'));
        $grid->column('eid_range_end', __('Eid range end'));

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
        $show = new Show(FamerTagsOrder::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('farm_id', __('Farm id'));
        $show->field('famer_id', __('Famer id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('district_tag_distribution_batch_id', __('District tag distribution batch id'));
        $show->field('farmer_message', __('Farmer message'));
        $show->field('delivery_address', __('Delivery address'));
        $show->field('delivery_date', __('Delivery date'));
        $show->field('order_status', __('Order status'));
        $show->field('pending_message_sent', __('Pending message sent'));
        $show->field('shipping_started_message_sent', __('Shipping started message sent'));
        $show->field('delivered_message_sent', __('Delivered message sent'));
        $show->field('total_tags_ordered_quantity', __('Total tags ordered quantity'));
        $show->field('total_tags_ordered_amount', __('Total tags ordered amount'));
        $show->field('total_tags_delivered_quantity', __('Total tags delivered quantity'));
        $show->field('flutterwave_amount', __('Flutterwave amount'));
        $show->field('flutterwave_status', __('Flutterwave status'));
        $show->field('flutterwave_link', __('Flutterwave link'));
        $show->field('flutterwave_phone_number', __('Flutterwave phone number'));
        $show->field('vid_range_start', __('Vid range start'));
        $show->field('vid_range_end', __('Vid range end'));
        $show->field('eid_range_start', __('Eid range start'));
        $show->field('eid_range_end', __('Eid range end'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FamerTagsOrder());

        $u = Admin::user();
        /*
        $form->select('farm_id', 'Select Farm')
            ->options(function ($id) {
                $parent = Farm::find($id);
                if ($parent != null) {
                    return [$parent->id =>  $parent->v_id . " - " . $parent->e_id];
                }
            })
            ->rules('required')
            ->ajax(
                url('/api/ajax-farms?'
                    . "&administrator_id={$u->id}")
            )->rules('required');*/


        $form->text('delivery_address', __('Delivery address'));


        $form->decimal('total_tags_ordered_quantity', __('Total tags ordered quantity'))->readonly();
        //district_tag_distribution_batch_id
        $district_tag_distribution_batches = [];
        foreach (DistrictTagDistributionBatch::all() as $key => $stock) {
            $district_tag_distribution_batches[$stock->id] = $stock->batch_name. " - (" . $stock->available_quantity . " tags available) ";
        }
        $form->select('district_tag_distribution_batch_id', __('Select District Tag Distribution Batch'))
            ->options($district_tag_distribution_batches)
            ->rules('required')
            ->required();

        if ($form->isEditing()) {


            $form->decimal('vid_range_start', __('Vid range start'));
            $form->decimal('vid_range_end', __('Vid range end'));
            $form->decimal('eid_range_start', __('Eid range start'));
            $form->decimal('eid_range_end', __('Eid range end'));

            $form->select('order_status', __('Order status'))
                ->options([
                    'Pending' => 'Pending',
                    'Processing' => 'Processing',
                    'Out for delivery' => 'Out for delivery',
                    'Delivered' => 'Delivered',
                    'Cancelled' => 'Cancelled',
                    'Completed' => 'Completed',
                ])
                ->default('Pending')
                ->rules('required');
            $form->decimal('total_tags_delivered_quantity', __('Total tags delivered quantity'));
        }


        return $form;
    }
}
