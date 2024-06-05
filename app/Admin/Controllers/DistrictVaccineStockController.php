<?php

namespace App\Admin\Controllers;

use App\Models\DistrictVaccineStock;
use App\Models\DrugCategory;
use App\Models\DrugStock;
use App\Models\Utils;
use App\Models\VaccineCategory;
use App\Models\VaccineMainStock;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class DistrictVaccineStockController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'District Vaccine Stocks';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DistrictVaccineStock());
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->disableExport();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $stocks = [];
            foreach (VaccineMainStock::all() as $stock) {
                if ($stock->current_quantity < 1) {
                    continue;
                }
                $stocks[$stock->id] = $stock->drug_category->name_of_drug . " - Batch No.: " .
                    $stock->batch_number . ", Available Quantity: " . $stock->current_quantity_text;
            }
            $filter->equal('drug_stock_id', 'Vaccine stock')->select($stocks);
            $cats = VaccineCategory::all()->pluck('name_of_drug', 'id');
            //drug_category_id
            $filter->where(function ($query) {
                $query->whereHas('drug_category', function ($query) {
                    $query->where('name_of_drug', 'like', "%{$this->input}%");
                });
            }, 'Filter by vaccine')->select($cats);

            //district_id
            $district_ajax_url = url('/api/districts');
            $filter->equal('district_id', 'District')->select()->ajax($district_ajax_url);
            //between original_quantity
            $filter->between('original_quantity', 'Original quantity')->integer();
            //current_quantity
            $filter->between('current_quantity', 'Current quantity')->integer();
            $filter->between('created_at', 'Entry date')->date();
        });


        $grid->model()->orderBy('id', 'Desc');


        $grid->column('created_at', __('Date'))->display(function ($t) {
            return Utils::my_date($t);
        })->sortable();

        $grid->column('district_id', __('District'))->display(function ($t) {
            return $this->district->name;
        })->sortable();

        $grid->column('drug_category_id', __('Vaccine'))->display(function ($t) {
            return $this->drug_category->name_of_drug;
        })->sortable();

        $grid->column('drug_stock_id', __('Batch'))->display(function ($t) {
            return $this->drug_stock->batch_number;
        })->sortable();

        $grid->column('original_quantity', __('Original quantity'))
            ->display(function ($t) {
                return number_format($t) . " Doses";
                return  Utils::quantity_convertor($t, $this->drug_stock->drug_state);
            })->sortable()
            ->totalRow(function ($amount) {
                return "<span class='text-success'>" . number_format($amount) . " Doses</span>";
            });


        $grid->column('current_quantity', __('Current quantity'))
            ->display(function ($t) {
                return number_format($t) . " Doses";
                return  Utils::quantity_convertor($t, $this->drug_stock->drug_state);
            })->sortable()
            ->totalRow(function ($amount) {
                return "<span class='text-danger'>" . number_format($amount) . " Doses</span>";
            });


        $grid->column('created_by', __('Created by'))
            ->display(function ($t) {
                return $this->creator->name;
            })->sortable()->hide();


        $grid->column('track', __('Track'))->display(function () {
            //3Fdistrict_vaccine_stock_id

            $farm_url = admin_url('farm-vaccination-records?district_vaccine_stock_id=' . $this->id . '');
            $farm_count = \App\Models\FarmVaccinationRecord::where('district_vaccine_stock_id', $this->id)->count();

            $gen_link_text = '<a target="_blank" href="' . $farm_url . '" >Farm Distribution (' . $farm_count . ')</a>';
            return $gen_link_text;

            //$dis_url = "<a href='$url'>District </a>";
        });


        //$grid->disableActions();
        $u = Auth::user();



        /*         $grid->column('packaging', __('Action'))
            ->display(function () {
                return '<a href="' . admin_url('health-centre-drug-stocks/create?district_stock_id=' . $this->id) . '" >SUPPLY TO HEALTH CENTRE</a>';
            }); */

        //is eding




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
        $show = new Show(DistrictVaccineStock::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('drug_category_id', __('Vaccine category id'));
        $show->field('drug_stock_id', __('Vaccine stock id'));
        $show->field('district_id', __('District id'));
        $show->field('created_by', __('Created by'));
        $show->field('original_quantity', __('Original quantity'));
        $show->field('current_quantity', __('Current quantity'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $drug_id = 0;
        if (isset($_GET['drug_id'])) {
            $drug_id =  ((int)($_GET['drug_id']));
        }

        $form = new Form(new DistrictVaccineStock());
        $form->disableReset();
        $form->disableViewCheck();
        $form->disableCreatingCheck();
        $form->disableEditingCheck();

        $stocks = [];
        foreach (VaccineMainStock::all() as $stock) {
            if ($stock->current_quantity < 1) {
                continue;
            }
            $stocks[$stock->id] = $stock->drug_category->name_of_drug . " - Batch No.: " .
                $stock->batch_number . ", Available Quantity: " . $stock->current_quantity_text;
        }

        $district_ajax_url = url('/api/districts');


        //creating
        if ($form->isCreating()) {

            $form->select('drug_stock_id', 'Vaccine stock')
                ->options($stocks)
                ->default($drug_id)
                ->readOnly()
                ->rules('required');
        } else {
            //display
            $form->display('drug_stock_id', 'Vaccine stock')
                ->with(function ($val) {
                    return $val . " - " . $this->drug_stock->batch_number;
                }); 
        }



        $form->select('district_id', 'Select District')
            ->ajax($district_ajax_url)
            ->rules('required');



        $form->hidden('created_by', __('Created by'))->default(Auth::user()->id);

        $form->divider();

        if ($form->isCreating()) {
            $form->decimal('original_quantity', 'Number of Doses Supplied')
                ->rules('required');
        } else {
            $form->display('original_quantity', 'Number of Doses Supplied');
        }




        return $form;
    }
}
