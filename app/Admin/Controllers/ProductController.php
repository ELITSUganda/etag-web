<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'U-LITS Cattle & Livestock Drugs Marketplace';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /* Admin::css('/assets/css/market-place.css'); */
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
        $grid->disableExport();

        $grid->filter(function ($filter) {

            $items = [];
            $cats = ProductCategory::all();

            foreach ($cats as $key => $c) {
                $items[$c->id] = $c->name;
            }

            $filter->disableIdFilter();
            $filter->like('name', 'Search by title');
            $filter->equal('type')->select([
                'Drugs' => 'Drugs',
                'Livestock' => 'Livestock',
            ]);

            $filter->equal('product_category_id')->select($items);
        });

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
        $u = Auth::user();
        $cats = ProductCategory::all();
        $lives = [];
        $drugs = [];
        foreach ($cats as $key => $c) {
            if ($c->type == 'Livestock') {
                $lives[$c->id] = $c->name;
            } else {
                $drugs[$c->id] = $c->name;
            }
        }

        $form->radio('type', __('Product Type'))
            ->options([
                'Drugs' => 'Drugs',
                'Livestock' => 'Livestock',
            ])
            ->when('Drugs', function (Form $form) {
                $items = [];
                $lives = [];
                $drugs = [];
                $cats = ProductCategory::all();

                foreach ($cats as $key => $c) {
                    if ($c->type != 'Livestock') {
                        $drugs[$c->id] = $c->name;
                    }
                }
                $form->select('product_category_id', __('Livestock category'))
                    ->options($drugs);
                    
            })
            ->when('Livestock', function (Form $form) {
                $items = [];
                $lives = [];
                $cats = ProductCategory::all();

                foreach ($cats as $key => $c) {
                    if ($c->type == 'Livestock') {
                        $lives[$c->id] = $c->name;
                    }
                }
                $form->select('product_category_id', __('Livestock category'))
                    ->options($lives);
            })
            ->required();

        $form->hidden('administrator_id', __('Administrator id'))->default($u->id)->value($u->id);

        $form->text('name', __('Product title'))->required();
        $form->text('price', __('Price'))->attribute(['type' => 'number'])->required();
        $form->text('quantity', __('Quantity available'))->attribute(['type' => 'number'])->required();
        $form->image('thumbnail', __('Thumbnail'))->required();
        $form->multipleImage('images', __('Images'))->removable();
        $form->textarea('details', __('Details'));

        return $form;
    }
}
