<?php

namespace App\Admin\Controllers;

use App\Models\CentralTagBatch;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CentralTagBatchController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Central Tags - Batch';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CentralTagBatch());


        $grid->filter(function ($filter) {
            $filter->like('batch_name', 'Batch Name');
            $filter->like('supplier_name', 'Supplier Name');
            $filter->between('purchase_date', 'Purchase Date')->date();
            $filter->between('total_purchase_quantity', 'Total Quantity');
            $filter->between('purchase_total_price', 'Total Price');
        });

        $grid->model()->orderBy('created_at', 'desc');
        $grid->disableBatchActions();
        $grid->quickSearch('batch_name')->placeholder('Search by batch name', 'batch description', 'supplier name', 'supplier contact');

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Date'))
            ->display(function ($created_at) {
                $d = \Carbon\Carbon::parse($created_at);
                if ($d == null) {
                    return '';
                }
                return $d->format('d M Y');
            })
            ->sortable();
        $grid->column('batch_name', __('Batch Name'))
            ->sortable()
            ->filter('like');
        $grid->column('vid_range_start', __('VID Range'))
            ->display(function ($vid_range_start) {
                return $this->vid_range_start . ' - ' . $this->vid_range_end;
            })->sortable();
        $grid->column('eid_range_start', __('Eid Range'))
            ->display(function ($eid_range_start) {
                return $this->eid_range_start . ' - ' . $this->eid_range_end;
            })->sortable();


        $grid->column('total_purchase_quantity', __('Total purchase quantity'))
            ->display(function ($purchase_unit_price) {
                return number_format($purchase_unit_price, 0, '.', ',') . ' tags';
            })->sortable();
        $grid->column('purchase_unit_price', __('Purchase unit price UGX'))
            ->display(function ($purchase_unit_price) {
                return 'UGX ' . number_format($purchase_unit_price, 0, '.', ',');
            })->sortable();

        $grid->column('purchase_total_price', __('Purchase Total Price'))
            ->display(function ($purchase_total_price) {
                return 'UGX ' . number_format($purchase_total_price, 0, '.', ',');
            })->sortable();
        $grid->column('purchase_invoice_number', __('Purchase invoice number'))->hide();
        $grid->column('batch_description', __('Batch Description'))->hide();
        $grid->column('supplier_name', __('Supplier'))
            ->sortable()
            ->filter('like');
        $grid->column('supplier_contact', __('Supplier Contact'))->hide();
        $grid->column('supplier_country', __('Supplier country'))->hide();
        $grid->column('supplier_address', __('Supplier address'))->hide();
        $grid->column('supplier_type', __('Supplier type'))->hide();
        $grid->column('purchase_date', __('Purchase date'))->hide();
        //total_purchase_quantity
        $grid->column('available_quantity', __('Available quantity'))
            ->display(function ($available_quantity) {
                return number_format($available_quantity, 0, '.', ',') . ' tags';
            })->sortable();
        //action buttons  column
        $grid->column('actions_buttons', __('Actions'))
            ->display(function () {
                $url = admin_url('district-tag-distribution-batches');
                // add district record
                $add_url = $url . "/create?central_tag_batch_id=" . $this->id;
                $view_records = $url . "?central_tag_batch_id=" . $this->id;
                $html = '<a href="' . $add_url . '" class="btn btn-xs btn-primary  btn-sm" title="Add District Batch">Add District Record</a><br>';
                $html .= '<a href="' . $view_records . '" class="btn btn-xs btn-info btn-sm" title="View District Batches">View District Record</a>';
                return $html;
            })->sortable()->label('primary');
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
        $show = new Show(CentralTagBatch::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('batch_name', __('Batch name'));
        $show->field('batch_description', __('Batch description'));
        $show->field('vid_range_start', __('Vid range start'));
        $show->field('vid_range_end', __('Vid range end'));
        $show->field('eid_range_start', __('Eid range start'));
        $show->field('eid_range_end', __('Eid range end'));
        $show->field('supplier_name', __('Supplier name'));
        $show->field('supplier_contact', __('Supplier contact'));
        $show->field('supplier_country', __('Supplier country'));
        $show->field('supplier_address', __('Supplier address'));
        $show->field('supplier_type', __('Supplier type'));
        $show->field('purchase_date', __('Purchase date'));
        $show->field('purchase_total_price', __('Purchase total price'));
        $show->field('purchase_unit_price', __('Purchase unit price'));
        $show->field('total_purchase_quantity', __('Total purchase quantity'));
        $show->field('purchase_invoice_number', __('Purchase invoice number'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CentralTagBatch());

        $form->text('batch_name', __('Batch Name'))->required();
        $form->decimal('vid_range_start', __('Vid range start'))->required();
        $form->decimal('vid_range_end', __('Vid range end'))->required();
        $form->decimal('eid_range_start', __('Eid range start'))->required();
        $form->decimal('eid_range_end', __('Eid range end'))->required();
        $form->divider();
        $form->radio('supplier_type', __('Supplier Type'))
            ->options(['local' => 'Local', 'international' => 'International'])
            ->default('local')
            ->when('international', function (Form $form) {
                $form->text('supplier_country', __('Supplier Country'))
                    ->rules('required')
                    ->placeholder('Enter supplier country');
            });
        $form->text('supplier_name', __('Supplier name'));
        $form->text('supplier_contact', __('Supplier Contact'));
        $form->text('supplier_address', __('Supplier address'));
        $form->divider();
        $form->text('purchase_date', __('Purchase date'));
        $form->decimal('total_purchase_quantity', __('Total quantity (Number of tags)'))
            ->required();

        $form->decimal('purchase_total_price', __('Purchase total price (UGX)'))->required();
        $form->text('purchase_invoice_number', __('Purchase invoice number'));
        $form->textarea('batch_description', __('Batch Details'));


        return $form;
    }
}
