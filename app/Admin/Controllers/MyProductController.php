<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use App\Models\DrugStockBatch;
use App\Models\Product;
use App\Models\ProductCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class MyProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'My Products';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Product());
        $grid->disableBatchActions();

        $grid->disableExport();

        $grid->model()->where('administrator_id', '=', Admin::user()->id);
        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Title'))->sortable();
        $grid->column('price', __('Price'))->display(function ($f) {
            return 'UGX ' . number_format($f);
        })->sortable();

        $grid->column('type', __('Product type'))
            ->sortable();

        $grid->column('views', __('Views'))
            ->label()
            ->sortable();

        $grid->column('product_category_id', __('Category'))
            ->display(function () {
                if ($this->category == null) {
                    return '-';
                }
                return $this->category->name;
            })->sortable();
        $grid->column('created_at', __('Created'))->sortable();
        $grid->column('details', __('Details'))->hide();

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

            $filter->equal('product_category_id', 'Product category')->select($items);
        });

        if (Request::get('view') !== 'table') {
            //$grid->setView('admin.grid.card');
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

        if ($form->isEditing()) {
            $form->radio('type', __('Product Type'))
                ->options([
                    'Drugs' => 'Drugs',
                    'Livestock' => 'Livestock',
                ])
                ->readonly()
                ->when('Drugs', function (Form $form) {
                    $stocks = [];
                    foreach (DrugStockBatch::where([
                        'administrator_id' => Auth::user()->id
                    ])->get() as $v) {
                        if ($v->current_quantity > 0) {
                            $stocks[$v->id] = $v->name . " - " . $v->batch_number
                                . "(Available quantity $v->quantity_text) ";
                        }
                    }
                    $form->select('drug_stock_batch_id', __('Select Drug stock/Batch'))
                        ->options($stocks)
                        ->readonly();
                })
                ->when('Livestock', function (Form $form) {
                    $animals = [];
                    foreach (Animal::where([
                        'administrator_id' => Auth::user()->id
                    ])->get() as $key => $v) {
                        $animals[$v->id] = $v->e_id . " - " . $v->v_id;
                    }

                    $form->select('animal_id', __('Select Animal'))
                        ->readonly()
                        ->options($animals);
                    $form->text('quantity', __('Livestock\s weight'))->attribute(['type' => 'number'])->readonly();
                })
                ->readonly()
                ->required();
        } else {
            $form->radio('type', __('Product Type'))
                ->options([
                    'Drugs' => 'Drugs',
                    'Livestock' => 'Livestock',
                ])
                ->when('Drugs', function (Form $form) {
                    $stocks = [];
                    foreach (DrugStockBatch::where([
                        'administrator_id' => Auth::user()->id
                    ])->get() as $v) {
                        if ($v->current_quantity > 0) {
                            $stocks[$v->id] = $v->name . " - " . $v->batch_number
                                . "(Available quantity $v->quantity_text) ";
                        }
                    }
                    $form->select('drug_stock_batch_id', __('Select Drug stock/Batch'))
                        ->options($stocks)
                        ->rules('required');
                })
                ->when('Livestock', function (Form $form) {
                    $animals = [];
                    foreach (Animal::where([
                        'administrator_id' => Auth::user()->id
                    ])->get() as $key => $v) {
                        $animals[$v->id] = $v->e_id . " - " . $v->v_id;
                    }

                    $form->select('animal_id', __('Select Animal'))
                        ->options($animals)
                        ->required();
                })
                ->required();
            $form->text('quantity', __('Livestock\'s weight'))->attribute(['type' => 'number'])->required();
        }


        $form->hidden('administrator_id', __('Administrator id'))->default($u->id)->value($u->id);

        $form->text('name', __('Product title'))->required();
        $form->text('price', __('Selling price'))->attribute(['type' => 'number'])->required();
        $form->textarea('details', __('Details'));

        $form->html('<h4>Click on NEW to add this product\'s photo.</h4>');

        $form->hasMany('images', "Drugs.", function (Form\NestedForm $form) {
            $form->image('src', __('Image'));
            $form->hidden('administrator_id', __('Administrator id'))->default(Auth::user()->id)->value(Auth::user()->id);
        });

        return $form;
    }
}
