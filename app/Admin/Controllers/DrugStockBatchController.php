<?php

namespace App\Admin\Controllers;

use App\Models\DrugCategory;
use App\Models\DrugStockBatch;
use App\Models\DrugStockBatchRecord;
use App\Models\Location;
use Doctrine\DBAL\Schema\Table;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;


class DrugStockBatchController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Drug batch';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DrugStockBatch());
        $grid->filter(function ($filter) {
            $cats = [];
            foreach (DrugCategory::all() as $key => $p) {
                $cats[$p->id] = $p->name;
            }

            $filter->equal('drug_category_id', "Filter by Drug category")->select($cats);
        });


        if (Admin::user()->isRole('nda')) {
            $grid->model()
                ->orderBy('id', 'desc');
        } else {
            $grid->model()
                ->where('administrator_id', '=', Admin::user()->id)
                ->orderBy('id', 'desc');
            $grid->disableActions();
        }

        $grid->disableBatchActions();
        $grid->column('id', __('#ID'))->sortable();
        $grid->column('batch_number', __('Batch number'))
            ->display(function ($batch_number) {
                return
                    '<a href="' . admin_url('drug-stock-batches/' . $this->id) . '">' . $batch_number . '</a>';
            });
        $grid->column('name', __('Drug name'))->sortable();
        $grid->column('original_quantity', __('Original quantity'));
        $grid->column('current_quantity', __('Current quantity'));
        $grid->column('selling_price', __('Selling price'));
        $grid->column('image', __('Image'))->hide();
        $grid->column('last_activity', __('Last activity'));
        $grid->column('details', __('Details'))->hide();
        $grid->column('created_at', __('Created'))->hide();
        $grid->column('ingredients', __('Ingredients'))->hide();
        $grid->column('source_id', __('Source id'))->hide();
        $grid->column('source_text', __('Source text'))->hide();
        $grid->column('sub_county_id', __('Sub county id'))->hide();

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

        $tab = new Tab();

        $item = DrugStockBatch::findOrFail($id);
        $recs = [];
        foreach ($item->all_records($item->batch_number) as $key => $value) {
            $recs[] = $value;
        }

        $recs = array_reverse($recs);

        $tab->add('Timeline', view('admin.dashboard.show-drug-stock-batches', [
            'items' => $recs
        ]));
        $tab->add('Drug batch details', view('admin.dashboard.show-drug-stock-details', [
            'item' => $item
        ]));


        return $tab;
        $show = new Show(DrugStockBatch::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('drug_category_id', __('Drug category id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('source_id', __('Source id'));
        $show->field('source_text', __('Source text'));
        $show->field('name', __('Name'));
        $show->field('manufacturer', __('Manufacturer'));
        $show->field('batch_number', __('Batch number'));
        $show->field('ingredients', __('Ingredients'));
        $show->field('expiry_date', __('Expiry date'));
        $show->field('original_quantity', __('Original quantity'));
        $show->field('current_quantity', __('Current quantity'));
        $show->field('selling_price', __('Selling price'));
        $show->field('image', __('Image'));
        $show->field('last_activity', __('Last activity'));
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
        $form = new Form(new DrugStockBatch());
        $u = Admin::user();


        if ($form->isCreating()) {
            $form->hidden('administrator_id', __('Administrator id'))->default($u->id)->value($u->id);
            $form->hidden('source_id', __('source_id'))->default($u->id)->value($u->id);
            $form->text('source_text', __('Supplier name'))->rules('required');
            $form->hidden('sub_county_id', __('Administrator id'))->default($u->id)->value($u->sub_county_id);
            $form->select('drug_category_id', __('Drug category'))
                ->rules('required')
                ->options(DrugCategory::all()->pluck('name', 'id'));
            $form->text('name', __('Drug Name'))->rules('required');
            $form->text('manufacturer', __('Manufacturer'))->rules('required');
            $form->text('batch_number', __('Batch number'))->rules('required');
            $form->text('ingredients', __('Ingredients'))->rules('required');
            $form->date('expiry_date', __('Expiry date'))->rules('required');
            $form->decimal('original_quantity', __('Quantity'))->rules('required');
        } else {


            $form->select('drug_category_id', __('Drug category'))
                ->rules('required')
                ->readOnly()
                ->options(DrugCategory::all()->pluck('name', 'id'));
            $form->text('manufacturer', __('Manufacturer'))->rules('required')->readOnly();
            $form->text('batch_number', __('Batch number'))->rules('required')->readOnly();
            $form->text('ingredients', __('Ingredients'))->rules('required')->readOnly();
            $form->date('expiry_date', __('Expiry date'))->rules('required')->readOnly();
            $form->decimal('original_quantity', __('Quantity'))->rules('required')->readOnly();

            $form->divider();
            $form->text('source_text', __('Supplier name'))->rules('required');
            $form->text('name', __('Drug Name'))->rules('required');
        }



        $form->decimal('selling_price', __('Total Price'));
        $form->image('image', __('Image'));
        $form->textarea('details', __('Drug description'));
        $form->disableEditingCheck();

        return $form;
    }
}
