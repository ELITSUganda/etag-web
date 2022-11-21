<?php

namespace App\Admin\Controllers;

use App\Models\Farm;
use App\Models\FinanceCategory;
use App\Models\Transaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Faker\Factory as Faker;

class TransactionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Transactions';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


    /*     $amount = [10000,15000,12000,1600,7000,8000,1100,35000,135000,36500,36100,];
        $is_income = [0,1,1];
        $finance_category_id = [1,2,1];
        $faker = Faker::create();
        for ($i = 0; $i < 1000; $i++) {
            shuffle($amount);
            shuffle($is_income);
            shuffle($finance_category_id);
            $t = new Transaction();
            $t->farm_id = 180;
            $t->finance_category_id = $finance_category_id[0];
            $t->amount = $amount[0];
            $t->is_income = $is_income[0];
            $t->transaction_date = $faker->dateTimeBetween('-1 year');
            $t->description = $faker->sentence(25);
            $t->created_at = $faker->dateTimeBetween('-1 year');

            $t->save();
        } */

        $grid = new Grid(new Transaction());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('district_id', __('District id'))->editable();
        $grid->column('sub_county_id', __('Sub county id'));
        $grid->column('farm_id', __('Farm id'));
        $grid->column('finance_category_id', __('Finance category id'));
        $grid->column('amount', __('Amount'));
        $grid->column('is_income', __('Is income'));
        $grid->column('description', __('Description'));
        $grid->column('transaction_date', __('Transaction date'));

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
        $show = new Show(Transaction::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district_id', __('District id'));
        $show->field('sub_county_id', __('Sub county id'));
        $show->field('farm_id', __('Farm id'));
        $show->field('finance_category_id', __('Finance category id'));
        $show->field('amount', __('Amount'));
        $show->field('is_income', __('Is income'));
        $show->field('description', __('Description'));
        $show->field('transaction_date', __('Transaction date'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Transaction());

        $u = Admin::user();
        $form->hidden('administrator_id', __('Administrator id'))->default($u->id);
        /* 
district_id
sub_county_id
*/
        $farms = [];
        foreach (Farm::where([
            'administrator_id' => $u->id
        ])->get() as $key => $f) {
            $farms[$f->id] = $f->holding_code . " - " . $f->village;
        }
        $cats = [];
        foreach (FinanceCategory::where([
            'administrator_id' => $u->id
        ])->get() as $key => $f) {
            $cats[$f->id] = $f->name;
        }

        $form->radio('farm_id', __('Select Farm'))
            ->rules('required')
            ->options($farms);
        $form->select('finance_category_id', __('Financial category'))
            ->rules('required')
            ->options($cats);

        $form->radio('is_income', __('Transaction type'))
            ->rules('required')
            ->default(null)
            ->options([
                1 => 'Income (+)',
                2 => 'Expense (-)',
            ]);

        $form->decimal('amount', __('Amount'))->rules('required');
        $form->date('transaction_date', __('Transaction date'))
            ->rules('required')
            ->default(date('Y-m-d'));

        $form->text('description', __('Description'));

        return $form;
    }
}
