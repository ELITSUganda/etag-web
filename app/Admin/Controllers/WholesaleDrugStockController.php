<?php

namespace App\Admin\Controllers;

use App\Models\DrugCategory;
use App\Models\Utils;
use App\Models\WholesaleDrugStock;
use Carbon\Carbon;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class WholesaleDrugStockController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Drugs stock';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WholesaleDrugStock());
        $grid->disableBatchActions();
        /* $s = DrugStock::find(1);
        $s->description .= "1";
        $s->original_quantity_temp = 10;

        $s->save();
        die("done"); */

        if (!Auth::user()->isRole('nda')) {
            $grid->model()->where(
                'administrator_id',
                Auth::user()->id
            );
        }
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('ID'))->sortable();
        $grid->column('created_at', __('DATE'))->display(function ($t) {
            return Utils::my_date($t);
        })->sortable()
            ->hide();

        $grid->column('drug_category_id', __('Drug'))
            ->display(function ($t) {
                return $this->drug_category->name;
            })->sortable();

        $grid->column('manufacturer', __('Manufacturer'))->hide();
        $grid->column('batch_number', __('Batch number'))->sortable();
        $grid->column('expiry_date', __('Expiry date'))->sortable();
        $grid->column('original_quantity', __('Original quantity'))
            ->display(function ($t) {
                return  Utils::quantity_convertor($t, $this->drug_state);
            })->sortable();
        $grid->column('current_quantity', __('Current quantity'))
            ->display(function ($t) {
                return  Utils::quantity_convertor($t, $this->drug_state);
            })->sortable();

        $grid->column('by_pieces', __('Current quantity (by pieces)'))
            ->display(function () {
                return  $this->drug_packaging_unit_quantity_text;
            });
        $grid->column('by_packaging', __('Current quantity (by packaging)'))
            ->display(function () {
                return  $this->drug_packaging_type_text;
            });


        $u = Auth::user();
        if (!$u->isRole('drugs-wholesaler')) {
            $grid->disableCreateButton();
        }


        $grid->column('description', __('Description'))->hide();
        $grid->column('status', __('Status'))
            ->label([
                'Approved' => 'success',
                'Pending' => 'warning',
                'Rejected' => 'danger',
                'Halted' => 'danger',
            ], 'warning')->sortable();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('manufacturer', 'Manufacturer');
            $filter->like('batch_number', 'Batch number');
            $filter->like('description', 'Description');
            $filter->equal('drug_category_id', 'Drug category')->select(DrugCategory::all()->pluck('name', 'id'));
            $filter->between('expiry_date', 'Expiry date')->date();
            $filter->between('created_at', 'Added')->date();
        });


        $grid->actions(function ($actions) {
            if ($actions->row->status == 'Approved') {
                $actions->disableEdit();
                $actions->disableDelete();
            }
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
        $show = new Show(WholesaleDrugStock::findOrFail($id));

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
        $form = new Form(new WholesaleDrugStock());

        if ($form->isCreating()) {
            $form->hidden('administrator_id', 'owner')->value(Auth::user()->id)
                ->default(Auth::user()->id);
        }

        $form->divider("Drug information");
        $form->select('drug_category_id', 'Select drug cateogry')
            ->options(DrugCategory::all()->pluck('name', 'id'))
            ->rules('required');
        $form->text('manufacturer', __('Manufacturer'))->rules('required');
        $form->text('batch_number', __('Batch number'))->rules('required');
        $form->date('expiry_date', __('Expiry date'))->rules(
            'required|date|after:' . Carbon::now()->subYears(13)
        );
        $form->image('image', __('Photo'));
        $form->textarea('description', __('Drug Description'))->rules('required');

        $form->divider("Drug quantity & Packaging");

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
                    ->creationRules('required');
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
                    ->creationRules('required');
            })->rules('required');

        if ($form->isEditing()) {
            if (Auth::user()->isRole('nda')) {
                $form->divider("Drug Status");
                $form->select('status', 'Drug Status')->options([
                    'Pending' => 'Pending',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected',
                    'Halted' => 'Halted',
                ])->rules('required');
            }
        }

        return $form;
    }
}
