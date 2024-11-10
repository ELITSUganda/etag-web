<?php

namespace App\Admin\Controllers;

use App\Models\FarmReport;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FarmReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Farm Reports';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $r = FarmReport::find(5);
        $r = FarmReport::do_process($r);
        $r->save();
        die("stop");
        $grid = new Grid(new FarmReport());
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created'));
        $grid->column('title', __('Title'));
        $grid->column('description', __('Description'));
        $grid->column('start_date', __('Start date'));
        $grid->column('end_date', __('End date'));
        $grid->column('farm_id', __('Farm id'));
        $grid->column('user_id', __('User id'));
        $grid->column('pdf', __('PRINT'))
            ->display(function ($pdf) {
                $url = url('farm-report-print?report_id=' . $this->id);
                return "<a href='$url' target='_blank'>PDF</a>";
                if ($pdf) {
                    return "<a href='/storage/$pdf' target='_blank'>PDF</a>";
                } else {
                    return "No PDF";
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
        $show = new Show(FarmReport::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('title', __('Title'));
        $show->field('description', __('Description'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));
        $show->field('farm_id', __('Farm id'));
        $show->field('user_id', __('User id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FarmReport());
        $u = auth()->user();

        //my farms
        $farms = \App\Models\Farm::where('administrator_id', $u->id)
            ->get()
            ->pluck('holding_code', 'id');


        $form->select('farm_id', __('Farm'))
            ->options($farms)
            ->required();
        $form->text('title', __('Title'));
        $form->date('start_date', __('Start date'))
            ->default(date('Y-m-d'))
            ->rules('required')
            ->required();
        $form->date('end_date', __('End date'))
            ->default(date('Y-m-d'))
            ->rules('required')
            ->required();


        $form->textarea('description', __('Description'));

        //user_id hidden field
        $form->hidden('user_id')->value($u->id);
        return $form;
    }
}
