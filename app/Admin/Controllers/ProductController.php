<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProductController extends AdminController
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
        $grid = new Grid(new Product());
        $grid->disableBatchActions();
        $grid->quickSearch('name');
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('Id')); 
        $grid->column('name', __('Name'))->sortable();
        $grid->column('price', __('Price'))->sortable();
        /*         $grid->column('metric', __('Metric'));
        $grid->column('currency', __('Currency'));
        $grid->column('description', __('Description'));
        $grid->column('summary', __('Summary'));
        $grid->column('price_1', __('Price 1'));
        $grid->column('price_2', __('Price 2'));
        $grid->column('feature_photo', __('Feature photo'));
        $grid->column('rates', __('Rates'));
        $grid->column('date_added', __('Date added'));
        $grid->column('date_updated', __('Date updated'));
        $grid->column('user', __('User'));
        $grid->column('category', __('Category'));
        $grid->column('sub_category', __('Sub category'));
        $grid->column('supplier', __('Supplier'));
        $grid->column('url', __('Url'));
        $grid->column('status', __('Status'));
        $grid->column('in_stock', __('In stock'));
        $grid->column('keywords', __('Keywords'));
        $grid->column('p_type', __('P type'));
        $grid->column('local_id', __('Local id'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('created_at', __('Created at'));
        $grid->column('animal_id', __('Animal id'));
        $grid->column('drug_category_id', __('Drug category id'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('source_id', __('Source id'));
        $grid->column('manufacturer', __('Manufacturer'));
        $grid->column('batch_number', __('Batch number'));
        $grid->column('expiry_date', __('Expiry date'));
        $grid->column('original_quantity', __('Original quantity'));
        $grid->column('current_quantity', __('Current quantity'));
        $grid->column('image', __('Image'));
        $grid->column('drug_state', __('Drug state'));
        $grid->column('drug_packaging_unit_quantity', __('Drug packaging unit quantity'));
        $grid->column('drug_packaging_type', __('Drug packaging type'));
        $grid->column('drug_packaging_type_pieces', __('Drug packaging type pieces'));
        $grid->column('original_quantity_temp', __('Original quantity temp'));
        $grid->column('source_type', __('Source type'));
        $grid->column('source_name', __('Source name'));
        $grid->column('source_contact', __('Source contact'));
        $grid->column('ingredients', __('Ingredients')); */

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
        $show = new Show(Product::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        /*         $show->field('metric', __('Metric'));
        $show->field('currency', __('Currency'));
        $show->field('description', __('Description'));
        $show->field('summary', __('Summary'));
        $show->field('price_1', __('Price 1'));
        $show->field('price_2', __('Price 2'));
        $show->field('feature_photo', __('Feature photo'));
        $show->field('rates', __('Rates'));
        $show->field('date_added', __('Date added'));
        $show->field('date_updated', __('Date updated'));
        $show->field('user', __('User'));
        $show->field('category', __('Category'));
        $show->field('sub_category', __('Sub category'));
        $show->field('supplier', __('Supplier'));
        $show->field('url', __('Url'));
        $show->field('status', __('Status'));
        $show->field('in_stock', __('In stock'));
        $show->field('keywords', __('Keywords'));
        $show->field('p_type', __('P type'));
        $show->field('local_id', __('Local id'));
        $show->field('updated_at', __('Updated at'));
        $show->field('created_at', __('Created at'));
        $show->field('animal_id', __('Animal id'));
        $show->field('drug_category_id', __('Drug category id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('source_id', __('Source id'));
        $show->field('manufacturer', __('Manufacturer'));
        $show->field('batch_number', __('Batch number'));
        $show->field('expiry_date', __('Expiry date'));
        $show->field('original_quantity', __('Original quantity'));
        $show->field('current_quantity', __('Current quantity'));
        $show->field('image', __('Image'));
        $show->field('drug_state', __('Drug state'));
        $show->field('drug_packaging_unit_quantity', __('Drug packaging unit quantity'));
        $show->field('drug_packaging_type', __('Drug packaging type'));
        $show->field('drug_packaging_type_pieces', __('Drug packaging type pieces'));
        $show->field('original_quantity_temp', __('Original quantity temp'));
        $show->field('source_type', __('Source type'));
        $show->field('source_name', __('Source name'));
        $show->field('source_contact', __('Source contact'));
        $show->field('ingredients', __('Ingredients'));
        $show->field('other_photos', __('Other photos'));
        $show->field('details', __('Details'));
        $show->field('origin_longitude', __('Origin longitude'));
        $show->field('origin_latitude', __('Origin latitude'));
        $show->field('district_id', __('District id'));
        $show->field('phone_number', __('Phone number'));
        $show->field('type', __('Type'));
        $show->field('breed', __('Breed'));
        $show->field('sex', __('Sex'));
        $show->field('weight', __('Weight'));
        $show->field('price', __('Price'));
        $show->field('e_id', __('E id'));
        $show->field('v_id', __('V id')); */

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product());

        $form->text('name', __('Name'));
        $form->image('image', __('Feature photo'));
        $form->decimal('price', __('Price'));

        $form->quill('description', __('Description'));

        /*         $form->number('category', __('Category'));
        $form->number('sub_category', __('Sub category'));
        $form->number('supplier', __('Supplier'));
        $form->url('url', __('Url'));
        $form->switch('status', __('Status'));
        $form->switch('in_stock', __('In stock'));
        $form->textarea('keywords', __('Keywords'));
        $form->number('local_id', __('Local id'));
        $form->number('animal_id', __('Animal id'))->default(1);
        $form->number('drug_category_id', __('Drug category id'));
        $form->number('administrator_id', __('Administrator id'));
        $form->number('source_id', __('Source id'));
        $form->textarea('manufacturer', __('Manufacturer'));
        $form->textarea('batch_number', __('Batch number'));
        $form->textarea('expiry_date', __('Expiry date'));
        $form->number('original_quantity', __('Original quantity'));
        $form->number('current_quantity', __('Current quantity'));
        $form->textarea('image', __('Image'));
        $form->textarea('drug_state', __('Drug state'));
        $form->textarea('drug_packaging_unit_quantity', __('Drug packaging unit quantity'));
        $form->textarea('drug_packaging_type', __('Drug packaging type'));
        $form->textarea('drug_packaging_type_pieces', __('Drug packaging type pieces'));
        $form->textarea('original_quantity_temp', __('Original quantity temp'));
        $form->textarea('source_type', __('Source type'));
        $form->textarea('source_name', __('Source name'));
        $form->textarea('source_contact', __('Source contact'));
        $form->textarea('ingredients', __('Ingredients'));
        $form->textarea('other_photos', __('Other photos'));
        $form->textarea('details', __('Details'));
        $form->textarea('origin_longitude', __('Origin longitude'));
        $form->textarea('origin_latitude', __('Origin latitude'));
        $form->textarea('district_id', __('District id'));
        $form->textarea('phone_number', __('Phone number'));
        $form->textarea('type', __('Type'));
        $form->textarea('breed', __('Breed'));
        $form->textarea('sex', __('Sex'));
        $form->textarea('weight', __('Weight'));
        $form->textarea('e_id', __('E id'));
        $form->textarea('v_id', __('V id')); */

        return $form;
    }
}
