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
            $grid->model()
                ->where('administrator_id', '=', Admin::user()->id)
                ->orderBy('id', 'desc');
        }


        $grid->column('id', __('#ID'))->sortable();
        $grid->column('created_at', __('Date'))
            ->display(function ($f) {
                return Carbon::parse($f)->toFormattedDateString();
            })->sortable();



        $grid->column('drug_stock_batch_id', __('Drug batch'))
            ->display(function ($id) {
                return $this->batch->name . " - " . $this->batch->batch_number;
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
        $form = new Form(new DrugStockBatchRecord());


        $stocks = [];
        foreach (DrugStockBatch::where([
            'administrator_id' => Auth::user()->id
        ]) as $v) {
            if ($v->current_quantity > 0) {
                $stocks[$v->id] = $v->name . " - " . $v->batch_number
                    . " Available ($v->quantity_text) ";
            }
        }

        $form->select('drug_stock_batch_id', __('Select Drug stock batch/Batch'))
            ->options($stocks)
            ->rules('required');
        $form->text('quantity', __('Quantity'))
            ->attribute('type', 'number')
            ->rules('required');



        $form->radio('action', 'Action')->options([
            'transfer' => 'Transfer to another acount',
            'animal_event' => 'Animal drug event',
            'offline_sales' => 'Offline sale',
            'other' => 'Other',
        ])->when('transfer', function ($f) {
            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );
            $f->select('receiver_account', "Receiver Account")
                ->options(function ($id) {
                    $a = User::find($id);
                    if ($a) {
                        return [$a->id => "#" . $a->id . " - " . $a->name];
                    }
                })
                ->ajax($ajax_url)->rules('required');
        })->when('animal_event', function ($f) {
            $u = Admin::user();
            $animals = [];
            foreach (Animal::where([
                'administrator_id' => Auth::user()->id
            ]) as $key => $v) {
                $animals[$v->id] = $v->e_id . " - " . $v->v_id;
            }

            $f->select('event_animal', __('Select Animal'))
                ->options($animals)
                ->rules('required');
        })
            ->when('offline_sales', function ($form) {
                $form->text('buyer_name', __('Buyer\'s name, Address and Contact.'))
                    ->rules('required');
            })

            ->when('other', function ($form) {
                $form->text('Other_explantion', __('Specify'))
                    ->rules('required');
            })
            ->rules('required');





        $form->textarea('description', __('Description'));

        return $form;
    }
}
