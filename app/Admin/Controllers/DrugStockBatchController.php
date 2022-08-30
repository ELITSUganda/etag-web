<?php

namespace App\Admin\Controllers;

use App\Models\DrugCategory;
use App\Models\DrugStockBatch;
use App\Models\DrugStockBatchRecord;
use App\Models\SubCounty;
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

        /*  $b = DrugStockBatch::find(12);
        $d = new DrugStockBatchRecord();
        $d->administrator_id = $b->administrator_id;
        $d->drug_stock_batch_id = $b->id;
        $d->description = 'Sold drugs to some seller.';
        $d->record_type = 'transfer';
        $d->receiver_account = 11;
        $d->event_animal_id = 0;
        $d->quantity = 500;
        $d->is_generated = 'no';
        $d->save(); */


        /* 
	
buyer_info
other_explantion		
	
	

        
        
        'transfer' => 'Transfer to another acount',
        'animal_event' => 'Animal drug event',
        'offline_sales' => 'Offline sale',
        'other' => 'Other', */

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
        }

        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->column('id', __('#ID'))->sortable();
        $grid->column('batch_number', __('Batch number'));
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

        $tab->add('Records', view('admin.dashboard.show-drug-stock-batches', [
            'items' => $recs
        ]));
        $tab->add('Livestock', '....');
        $tab->add('People', '......');
        $tab->add('Farms', '....');
        $tab->add('Batch details', '....');

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

        $sub_counties = [];
        foreach (SubCounty::all() as $key => $p) {
            $sub_counties[$p->id] = $p->name . ", " .
                $p->district->name . ".";
        }

        $form->select('sub_county_id', __('Sub-county'))
            ->options($sub_counties)
            ->required();
        $form->text('manufacturer', __('Manufacturer'));
        $form->text('name', __('Name'));
        $form->textarea('batch_number', __('Batch number'));
        $form->textarea('ingredients', __('Ingredients'));
        $form->textarea('expiry_date', __('Expiry date'));
        $form->decimal('original_quantity', __('Original quantity'));
        $form->decimal('current_quantity', __('Current quantity'));
        $form->textarea('selling_price', __('Selling price'));
        $form->textarea('image', __('Image'));
        $form->textarea('last_activity', __('Last activity'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
