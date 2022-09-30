<?php

namespace App\Admin\Controllers;

use App\Models\Animal;
use App\Models\DrugStockBatch;
use App\Models\DrugStockBatchRecord;
use App\Models\User;
use Attribute;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class DrugStockBatchRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Drugs records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        $grid = new Grid(new DrugStockBatchRecord());
        $grid->disableActions();


        if (Admin::user()->isRole('nda')) {
            $grid->model()
                ->orderBy('id', 'desc');
        } else {
            $grid->disableActions();
            $grid->model()
                ->where('administrator_id',  Admin::user()->id)
                ->orderBy('id', 'desc');
        }


        $grid->column('id', __('#ID'))->sortable();
        $grid->column('created_at', __('Date'))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable();


        $grid->column('drug_stock_batch_id', __('Drug batch'))
            ->display(function ($id) {
                $batch_number = $this->batch->name . " - " . $this->batch->batch_number;
                return
                    '<a href="' . admin_url('drug-stock-batches/' . $this->batch->id) . '">' . $batch_number . '</a>';
            })->sortable();


        $grid->column('description', __('Description'));

        if (Admin::user()->isRole('nda')) {
            $grid->column('administrator_id', __('User'))
                ->display(function ($user) {
                    $_user = Administrator::find($user);
                    if (!$_user) {
                        return "-";
                    }
                    return $_user->name;
                })->sortable();
        }

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
        $show = new Show(DrugStockBatchRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('drug_stock_batch_id', __('Drug stock batch id'));
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
        /* $x = new DrugStockBatchRecord();
        $x->administrator_id = 2;
        $x->drug_stock_batch_id = 1;
        $x->quantity = 5;
        $x->is_generated = 'no';
        $x->description = 'Sold to new customer';
        $x->record_type = 'offline_sales';
        $x->buyer_name = 'Simple test';
        $x->buyer_phone = '0783204619';
        $x->save();
 */
        /*
        receiver_account	
        event_animal_id
        buyer_info
        other_explantion	
        is_generated	
        quantity	
        batch_number */

        $form = new Form(new DrugStockBatchRecord());

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
        $form->text('quantity', __('Quantity'))
            ->attribute('type', 'number')
            ->rules('required');


        $form->radio('record_type', 'Action')->options([
            'transfer' => 'Transfer to another acount',
            'animal_event' => 'Animal drug event',
            'offline_sales' => 'Sell drugs to new customer',
        ])->when('transfer', function ($f) {
            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . "&search_by_1=name"
                    . "&search_by_2=phone_number"
                    . "&model=User"
            );
            $f->select('receiver_account', "Receiver Account")
                ->options(function ($id) {
                    $a = User::find($id);
                    if ($a) {
                        return [$a->id => "" . $a->name . " - " . $a->phone_number];
                    }
                })
                ->ajax($ajax_url)->rules('required');
        })->when('animal_event', function ($f) {
            $animals = [];
            foreach (Animal::where([
                'administrator_id' => Auth::user()->id
            ])->get() as $key => $v) {
                $animals[$v->id] = $v->e_id . " - " . $v->v_id;
            }

            $f->select('event_animal_id', __('Select Animal'))
                ->options($animals)
                ->rules('required');
        })
            ->when('offline_sales', function ($form) {

                $form->text('buyer_name', __('Enter Buyer\'s full name'))
                    ->rules('required');

                $form->text('buyer_nin', __('Enter Buyer\'s National ID Number (NIN)'))
                    ->rules('required');

                $form->text('buyer_phone', __('Enter Buyer\'s Phone Number'))
                    ->rules('required');

                $form->text('buyer_info', __('Enter Buyer\'s Address'))
                    ->rules('required');
            })->rules('required');

        /* ->when('other', function ($form) {
                $form->text('other_explantion', __('Specify'))
                    ->rules('required');
            })
            ->rules('required');
 */




        $form->textarea('description', __('Description'));
        $form->hidden('is_generated', __('is_generated'))->default("no")->value('no');

        return $form;
    }
}
