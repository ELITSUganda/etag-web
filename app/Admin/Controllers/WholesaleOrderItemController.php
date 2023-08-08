<?php

namespace App\Admin\Controllers;

use App\Models\WholesaleOrderItem;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class WholesaleOrderItemController extends AdminController
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
        $grid = new Grid(new WholesaleOrderItem());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('wholesale_drug_stock_id', __('Wholesale drug stock id'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('description', __('Description'));
        $grid->column('wholesale_order_id', __('Wholesale order id'));

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
        $show = new Show(WholesaleOrderItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('wholesale_drug_stock_id', __('Wholesale drug stock id'));
        $show->field('quantity', __('Quantity'));
        $show->field('description', __('Description'));
        $show->field('wholesale_order_id', __('Wholesale order id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WholesaleOrderItem());

        $form->number('wholesale_drug_stock_id', __('Wholesale drug stock id'));
        $form->number('quantity', __('Quantity'));
        $form->textarea('description', __('Description'));
        $form->number('wholesale_order_id', __('Wholesale order id'))->default(1);

        return $form;
    }
}
