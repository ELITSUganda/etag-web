<?php

namespace App\Admin\Controllers;

use App\Models\DrugCategory;
use App\Models\VaccineMainStock;
use App\Models\Utils;
use App\Models\VaccineCategory;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class VaccineMainStockController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Central Vaccine Stock';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new VaccineMainStock());
        $grid->disableBatchActions();
        $grid->quickSearch('batch_number', 'description', 'manufacturer')
            ->placeholder('Search by batch number, description, manufacturer'); 
        /* $s = VaccineMainStock::find(1);
        $s->description .= "1";
        $s->original_quantity_temp = 10;

        $s->save();
        die("done"); */

        $grid->model()->orderBy('id', 'Desc');
        $grid->column('created_at', __('Added'))->display(function ($t) {
            return Utils::my_date($t);
        })->sortable()
            ->width('100'); 
        $grid->column('drug_category_id', __('Drug'))
            ->display(function ($t) {
                return $this->drug_category->name_of_drug;
            })->sortable();
        $grid->column('manufacturer', __('Manufacturer'))->hide(); 
        $grid->column('batch_number', __('Batch number'))->sortable();
        $grid->column('expiry_date', __('Expiry date'))->sortable();
        $grid->column('original_quantity', __('Original QTY (Doses)'))
            ->display(function ($t) {
                return  number_format($t);
                return  Utils::quantity_convertor($t, $this->drug_state);
            })->sortable()
            ->width('120');

        $grid->column('current_quantity', __('Current QTY (Doses)'))
            ->display(function ($t) {
                return  number_format($t);
                return  Utils::quantity_convertor($t, $this->drug_state);
            })->sortable()
            ->width('120'); 


        $grid->disableActions();
        $u = Auth::user();


        $grid->column('packaging', __('Action'))
            ->display(function () {
                return '<a href="' . admin_url('district-vaccine-stocks/create?drug_id=' . $this->id) . '" >SUPPLY TO DISTRICT</a>';
            });


        $grid->column('description', __('Description'))->hide();

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
        $show = new Show(VaccineMainStock::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('drug_category_id', __('Drug category id'));
        $show->field('manufacturer', __('Manufacturer'));
        $show->field('batch_number', __('Batch number'));
        $show->field('expiry_date', __('Expiry date'));
        $show->field('original_quantity', __('Original quantity'));
        $show->field('current_quantity', __('Current quantity'));
        $show->field('image', __('Image'));
        $show->field('description', __('Description'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new VaccineMainStock());


        $form->divider("Drug information");
        $form->select('drug_category_id', 'Select drug cateogry')
            ->options(VaccineCategory::all()->pluck('name_of_drug', 'id'))
            ->rules('required');
        $form->text('manufacturer', __('Manufacturer'))->rules('required');
        $form->text('batch_number', __('Batch number'))->rules('required');
        $form->date('expiry_date', __('Expiry date'))->rules('required');
        $form->image('image', __('Photo'));
        $form->textarea('description', __('Drug Description'))->rules('required');

        $form->divider("Drug quantity & Packaging");
        $form->hidden('drug_state')->default('Liquid');
        $form->hidden('drug_packaging_type')->default('Bottle');

        $form->decimal('drug_packaging_type_pieces', 'Number of deses')
            ->rules('required');

        $form->decimal('drug_packaging_unit_quantity', 'Single dose quantity (in Milliliters - ml)')
            ->rules('required')
            ->help('in Milliliters - (ml)');


        return $form;
        $form->radio('drug_state', 'Drug state')
            ->options([
                'Solid' => 'Solid',
                'Liquid' => 'Liquid / Syrup',
            ])
            ->when('Solid', function (Form $form) {

                $form->decimal('drug_packaging_unit_quantity', 'Single tablet mass (in Milligrams - mg)')
                    ->rules('required');

                $form->radio('drug_packaging_type', 'Drug packaging type')
                    ->options([
                        'Blister pack' => 'Blister pack',
                        'Container' => 'Container',
                    ])->rules('required')
                    ->when('Blister pack', function (Form $form) {
                        $form->decimal('drug_packaging_type_pieces', 'Number of tablets per blister pack')
                            ->rules('required');
                    })
                    ->when('Container', function (Form $form) {
                        $form->decimal('drug_packaging_type_pieces', 'Number of tablets per container')
                            ->rules('required');
                    })->rules('required');
                $form->divider();
                $form->decimal('original_quantity_temp', 'Drug quantity (in Killograms - KGs)')
                    ->rules('required');
            })
            ->when('Liquid', function (Form $form) {


                $form->radio('drug_packaging_type', 'Drug packaging type')
                    ->options([
                        'Infusion bag' => 'Infusion bag',
                        'Bottle' => 'Bottle',
                    ])->rules('required')
                    ->when('Infusion bag', function (Form $form) {
                        $form->decimal('drug_packaging_unit_quantity', 'Drug quantity  per bag (in Milliliters - ml)')
                            ->rules('required');

                        $form->decimal('drug_packaging_type_pieces', 'Number of bags per box')
                            ->rules('required');
                    })
                    ->when('Bottle', function (Form $form) {
                        $form->decimal('drug_packaging_unit_quantity', 'Drug quantity  per bottle (in Milliliters - ml)')
                            ->rules('required');

                        $form->decimal('drug_packaging_type_pieces', 'Number of bottles per box')
                            ->rules('required');
                    })->rules('required');

                $form->divider();
                $form->decimal('original_quantity_temp', 'Drug quantity (in Litters - L)')
                    ->rules('required');
            })->rules('required');



        return $form;
    }
}
