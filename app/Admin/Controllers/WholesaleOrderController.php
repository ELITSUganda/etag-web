<?php

namespace App\Admin\Controllers;

use App\Models\WholesaleDrugStock;
use App\Models\WholesaleOrder;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;

use Illuminate\Support\Facades\Auth;

class WholesaleOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Orders';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WholesaleOrder());

        $grid->column('id', __('ID'))->sortable();

        $grid->disableBatchActions();

        if (!Auth::user()->isRole('nda')) {
            $grid->model()->where(
                'customer_id',
                Auth::user()->id
            )->orWhere(['supplier_id' => Auth::user()->id]);
        }

        $grid->column('created_at', __('Created'))
            ->display(function ($date) {
                return date('d M Y H:i:s', strtotime($date));
            })
            ->sortable();
        $grid->column('customer_id', __('Customer'))
            ->display(function ($id) {
                $parent = Administrator::find($id);
                if ($parent != null) {
                    return $parent->name;
                }
            })
            ->sortable();
        $grid->column('supplier_id', __('Supplier'))
            ->display(function ($id) {
                $parent = Administrator::find($id);
                if ($parent != null) {
                    return $parent->name;
                }
            })
            ->sortable();

        $grid->column('delivery_type', __('Delivery Methord'));
        $grid->column('customer_name', __('Customer name'))->hide();
        $grid->column('customer_contact', __('Customer contact'));
        $grid->column('customer_address', __('Customer address'));
        $grid->column('customer_gps_patitude', __('Customer gps patitude'))->hide();
        $grid->column('customer_gps_longitude', __('Customer gps longitude'))->hide();
        $grid->column('customer_note', __('Customer note'));
        $grid->column('details', __('Details'))
            ->hide();

        $grid->column('status', __('Status'))
            ->label([
                'Pending' => 'default',
                'Processing' => 'warning',
                'Shipping' => 'info',
                'Delivered' => 'primary',
                'Completed' => 'success',
                'Canceled' => 'danger',
            ])
            ->sortable();
        $grid->column('processed', __('Processed'))
            ->label([
                'No' => 'default',
                'Yes' => 'success',
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

        /*         $o = WholesaleOrder::find(1);
        $o->customer_name .= ".";
        $o->save(); 
        die("done");
 */
        $form = new Form(new WholesaleOrder());


        if ($form->isCreating()) {
            $form->select('customer_id', 'Select Customer')
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
            $form->select('customer_id', 'Select Customer')
                ->options(function ($id) {
                    $parent = Administrator::find($id);
                    if ($parent != null) {
                        return [$parent->id =>  $parent->name];
                    }
                })->readOnly();
        }

        if (
            Auth::user()->isRole('nda') ||
            Auth::user()->isRole('admin') ||
            Auth::user()->isRole('administrator')
        ) {
            $form->select('supplier_id', 'Select Supplier')
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
            $form->hidden('supplier_id', __('Supplier id'))->default(Auth::user()->id);
        }


        if ($form->isCreating()) {
            $form->hidden('status', __('Status'))->default('Pending');
        } else {
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
        }

        $form->radio('delivery_type', __('Delivery Methord'))
            ->options([
                'By Customer' => 'Collected By Customer',
                'By Supplier' => 'Delivered By Supplier',
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
        $isProcessed = false;
        if ($form->isEditing()) {
            $currentUrl = $_SERVER['REQUEST_URI'];
            $currentUrl = trim($currentUrl, '/');
            $urlSegments = explode('/', $currentUrl);

            $thismodel = null;
            foreach ($urlSegments as $key => $seg) {
                $thismodel = WholesaleOrder::find(((int) $seg));
                if ($thismodel != null) {
                    break;
                }
            }

            if ($thismodel->processed == 'Yes') {
                $isProcessed = true;
            } else {
                $isProcessed = false;
            }
        }

        if ($isProcessed) {
            $form->hasMany('order_items', function ($form) {
                $u = Auth::user();
                $form->select('wholesale_drug_stock_id', 'Select stock')
                    ->readOnly()
                    ->options(function ($id) {
                        $parent = WholesaleDrugStock::find($id);
                        if ($parent != null) {
                            return [$parent->id =>  $parent->drug_category->name . " - " . $parent->drug_packaging_type_text];
                        }
                    });
                $form->decimal('quantity', 'Drug quantity (in Killograms for solids, in Litters for Liquids)')
                    ->readOnly();
                $form->text('description', 'Description');
            })
                ->disableDelete()
                ->disableCreate();
        } else {
            $form->hasMany('order_items', function ($form) {
                $u = Auth::user();
                $form->select('wholesale_drug_stock_id', 'Select stock')
                    ->options(WholesaleDrugStock::get_items($u))->rules('required');
                $form->decimal('quantity', 'Drug quantity (in Killograms for solids, in Litters for Liquids)')
                    ->rules('required');
                $form->text('description', 'Description');
            });
        }
        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableEditingCheck();
        $form->disableViewCheck();
        return $form;
    }
}
