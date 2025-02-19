<?php

namespace App\Admin\Controllers;

use App\Models\DrugReport;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DrugReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Drug Usage Reports';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DrugReport());
        $grid->quickSearch('title');
        $grid->disableBatchActions();
        $u = Admin::user();
        //if admin, see all
        if (!$u->isRole('administrator')) {
            $grid->model()->orderBy('id', 'desc');
        }
        $grid->model()->orderBy('id', 'desc'); 

        $grid->column('created_at', __('Date'))
            ->display(function ($created_at) {
                return date('Y-m-d H:i:s', strtotime($created_at));
            })->sortable();
        //title
        $grid->column('title', __('Title'))->sortable();
        // $grid->column('owner_id', __('Owner id'));
        $grid->column('farm_id', __('Farm'))
            ->display(function ($farm_id) {
                $f = \App\Models\Farm::find($farm_id);
                if ($f) {
                    return $f->holding_code . ' (' . $f->sub_county_text . ')';
                }
                return '';
            });
        // $grid->column('period_type', __('Period type'));
        $grid->column('period', __('Period'))
            ->display(function ($period) {
                return ucfirst(strtolower($period));
            });
        $grid->column('start_date', __('Start Date'))
            ->display(function ($start_date) {
                return date('Y-m-d', strtotime($start_date));
            })->sortable();
        $grid->column('end_date', __('End date'))
            ->display(function ($end_date) {
                return date('Y-m-d', strtotime($end_date));
            })->sortable();
        $grid->column('total_cost', __('Total Cost'))
            ->display(function ($total_cost) {
                return number_format($total_cost);
            })->sortable();
        /*      $grid->column('pdf_generated', __('Pdf generated'));
        $grid->column('pdf_path', __('Pdf path'));
        $grid->column('data', __('Data')); */
        //print-drrugs-report
        $grid->column('print', __('Print'))->display(function ($print) {
            $url = url('print-drrugs-report?report_id=' . $this->id);
            return "<a href='$url' target='_blank'>Print Report</a>";
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
        $show = new Show(DrugReport::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('owner_id', __('Owner id'));
        $show->field('farm_id', __('Farm id'));
        $show->field('period_type', __('Period type'));
        $show->field('period', __('Period'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));
        $show->field('total_cost', __('Total cost'));
        $show->field('pdf_generated', __('Pdf generated'));
        $show->field('pdf_path', __('Pdf path'));
        $show->field('data', __('Data'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DrugReport());
        $u = Admin::user();
        $form->hidden('owner_id')->value($u->id);
        $farms = [];
        foreach (
            \App\Models\Farm::where([
                'administrator_id' => $u->id
            ])->get() as $farm
        ) {
            $farms[$farm->id] = $farm->holding_code . ' (' . $farm->sub_county_text . ')';
        }
        $form->select('farm_id', __('Select farm'))->options($farms);
        $form->radio('period_type', __('Period type'))->options([
            'DAILY' => 'Daily',
            'WEEKLY' => 'Weekly',
            'MONTHLY' => 'Monthly',
            'QUARTERLY' => 'Quarterly',
            'YEARLY' => 'Yearly',
            'CUSTOM' => 'Custom',
        ])->default('DAILY')
            ->when('DAILY', function (Form $form) {
                $form->radio('period', __('Period'))->options([
                    'TODAY' => 'Today',
                    'YESTERDAY' => 'Yesterday',
                ])->rules('required');
            })->when('WEEKLY', function (Form $form) {
                $form->radio('period', __('Period'))->options([
                    'THIS_WEEK' => 'This week',
                    'LAST_WEEK' => 'Last week',
                ])->rules('required');
            })->when('MONTHLY', function (Form $form) {
                $form->radio('period', __('Period'))->options([
                    'THIS_MONTH' => 'This month',
                    'LAST_MONTH' => 'Last month',
                ])->rules('required');
            })->when('QUARTERLY', function (Form $form) {
                $form->radio('period', __('Period'))->options([
                    'THIS_QUARTER' => 'This quarter',
                    'LAST_QUARTER' => 'Last quarter',
                ])->rules('required');
            })->when('YEARLY', function (Form $form) {
                $form->radio('period', __('Period'))->options([
                    'THIS_YEAR' => 'This year',
                    'LAST_YEAR' => 'Last year',
                ])->rules('required');
            })->when('CUSTOM', function (Form $form) {
                $form->date('start_date', __('Start date'))->rules('required|date|before:end_date');
                $form->date('end_date', __('End date'))->rules('required|date|after:start_date');
            })->rules('required');

        //radio for design
        $form->radio('design', __('Report Design'))->options([
            'design_1' => 'Design 1',
            'design_2' => 'Design 2',
        ])->default('design_1');
        $form->textarea('data', __('Data'));

        return $form;
    }
}
