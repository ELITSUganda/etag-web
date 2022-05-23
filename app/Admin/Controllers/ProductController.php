<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;

class ProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Product';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('product_category_id', __('Product category id'));
        $grid->column('name', __('Name'));
        $grid->column('price', __('Price'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('thumbnail', __('Thumbnail'));
        $grid->column('images', __('Images'));
        $grid->column('details', __('Details'));

        if (Request::get('view') !== 'table') {
            $grid->setView('admin.grid.card');
        }

          $grid->actions(function ($actions) {
            $actions->disableDelete();
        });

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
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('product_category_id', __('Product category id'));
        $show->field('name', __('Name'));
        $show->field('price', __('Price'));
        $show->field('quantity', __('Quantity'));
        $show->field('thumbnail', __('Thumbnail'));
        $show->field('images', __('Images'));
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
        $form = new Form(new Product());

        $form->number('administrator_id', __('Administrator id'))->default(1);
        $form->number('product_category_id', __('Product category id'))->default(1);
        $form->textarea('name', __('Name'));
        $form->textarea('price', __('Price'));
        $form->textarea('quantity', __('Quantity'));
        $form->textarea('thumbnail', __('Thumbnail'));
        $form->textarea('images', __('Images'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
