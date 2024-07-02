<?php

namespace App\Admin\Controllers;

use App\Models\DistrictVaccineStock;
use App\Models\Farm;
use App\Models\FarmVaccinationRecord;
use App\Models\Utils;
use App\Models\VaccineMainStock;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FarmVaccinationRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Farm Vaccine Distribution Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        Utils::check_duplicates();


        $grid = new Grid(new FarmVaccinationRecord());
        $grid->disableBatchActions();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $items = [];
            foreach (Farm::all() as $key => $f) {
                $items[$f->id] = $f->holding_code;
            }

            $district_vaccine_stocks = [];
            foreach (DistrictVaccineStock::all() as $stock) {
                $district_vaccine_stocks[$stock->id] = $stock->district->name . " - " . $stock->drug_stock->batch_number . ", Available: " . $stock->current_quantity." Doses";
            }
            $main_vaccines = [];
            foreach (VaccineMainStock::all() as $stock) {
                $main_vaccines[$stock->id] = $stock->drug_category->name_of_drug. " - Batch No.: " . $stock->batch_number . ", Available: " . $stock->current_quantity." Doses";
            } 

            //vaccine_main_stock_id
            $filter->equal('vaccine_main_stock_id', __('Filter by Central Vaccine'))->select($main_vaccines);
            $filter->equal('district_vaccine_stock_id', __('Filter by District stock'))->select($district_vaccine_stocks);
            $filter->equal('farm_id', __('Farm'))->select($items);
            $filter->equal('created_by_id', __('Created by'))->select(Admin::user()->pluck('name', 'id'));
            $filter->like('vaccination_batch_number', 'Batch number');
            $filter->between('created_at', 'Entry date')->date();
        });

        $grid->model()->orderBy('id', 'Desc');
        $grid->column('created_at', __('Date'))
            ->display(function ($t) {
                return Utils::my_date($t);
            })->sortable();
        $grid->column('updated_at', __('Updated at'))
            ->display(function ($t) {
                return Utils::my_date($t);
            })->sortable()->hide();
        $grid->column('lhc', __('FARM'))->display(function ($t) {
            if($this->farm == null){
                $this->delete();
                return 'DELETED FARM RECORD';
            }
            return $this->farm->holding_code;
        })->sortable()->hide();

        $grid->column('farm_id', __('Farm'))
            ->display(function ($t) {
                if($this->farm == null){
                    $this->delete();
                    return 'DELETED FARM RECORD';
                } 
                return $this->farm->holding_code;
            })->sortable();

        $grid->column('district_id', __('Sub-county'))
            ->display(function ($t) {
                if($this->farm == null){
                    $this->delete();
                    return 'DELETED FARM RECORD';
                } 
                return $this->farm->sub_county_text;
            })->sortable()->hide(); 
        $grid->column('vaccine_main_stock_id', __('Vaccine'))
            ->display(function ($t) {
                return $this->vaccine_main_stock->drug_category->name_of_drug;
            })->sortable();

        $grid->column('vaccination_batch_number', __('Vaccine Batch Number'))->sortable();
        $grid->column('district_vaccine_stock_id', __('District vaccine stock id'))->hide();


        $grid->column('number_of_doses', __('Number of doses'))
            ->display(function ($t) {
                return number_format($this->number_of_doses);
            })->sortable();
        $grid->column('number_of_animals_vaccinated', __('Animals Vaccinated'))
            ->display(function ($t) {
                return number_format($this->number_of_animals_vaccinated);
            })->sortable()
            ->width('100');

        $grid->column('remarks', __('Remarks'))->hide();
        $grid->column('farmer_name', __('Farmer name'))->sortable();
        $grid->column('farmer_phone_number', __('Farmer Contact'))
            ->display(function ($t) {
                return $this->farmer_phone_number;
            })->sortable();

        $grid->column('created_by_id', __('Created by'))
            ->display(function ($t) {
                return $this->created_by->name;
            })->sortable();
        $grid->column('gps_location', __('Gps location'));
        //track


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
        $show = new Show(FarmVaccinationRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('farm_id', __('Farm id'));
        $show->field('vaccine_main_stock_id', __('Vaccine main stock id'));
        $show->field('district_vaccine_stock_id', __('District vaccine stock id'));
        $show->field('district_id', __('District id'));
        $show->field('created_by_id', __('Created by id'));
        $show->field('updated_by_id', __('Updated by id'));
        $show->field('number_of_doses', __('Number of doses'));
        $show->field('number_of_animals_vaccinated', __('Number of animals vaccinated'));
        $show->field('vaccination_batch_number', __('Vaccination batch number'));
        $show->field('remarks', __('Remarks'));
        $show->field('gps_location', __('Gps location'));
        $show->field('lhc', __('Lhc'));
        $show->field('farmer_name', __('Farmer name'));
        $show->field('farmer_phone_number', __('Farmer phone number'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FarmVaccinationRecord());

        $items = [];
        foreach (Farm::all() as $key => $f) {
            $items[$f->id] = $f->holding_code;
        }

        $district_vaccine_stocks = [];
        foreach (DistrictVaccineStock::all() as $stock) {
            $district_vaccine_stocks[$stock->id] = $stock->district->name . " - " . $stock->drug_stock->batch_number . " Available: " . $stock->current_quantity;
        }
        $form->select('farm_id', __('Farm'))
            ->options($items)
            ->required();
        $form->select('district_vaccine_stock_id', __('Select Vaccine Stock'))
            ->options($district_vaccine_stocks)
            ->required();

        $u = Admin::user();
        $form->hidden('created_by_id', __('Created by id'))->default($u->id);
        $form->hidden('updated_by_id', __('Updated by id'))->default($u->id);

        $form->decimal('number_of_doses', __('Number of doses'))->required();
        $form->decimal('number_of_animals_vaccinated', __('Number of animals vaccinated'))->required();
        $form->text('remarks', __('Remarks'));
        $form->text('gps_location', __('Gps Location'))->default('0.0000,0.0000');


        return $form;
    }
}
