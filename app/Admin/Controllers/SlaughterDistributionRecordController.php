<?php

namespace App\Admin\Controllers;

use App\Models\SlaughterDistributionRecord;
use App\Models\SlaughterRecord;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Carbon\Carbon;

class SlaughterDistributionRecordController extends AdminController
{
    protected $title = 'Slaughter Distributions';

    protected function grid()
    {
        $grid = new Grid(new SlaughterDistributionRecord());

        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', __('ID'))->sortable();
        $grid->column('created_at', __('Date'))->display(function ($f) {
            return Carbon::parse($f)->toFormattedDateString();
        })->sortable();
        $grid->column('slaughter_id', __('Carcass'))->display(function ($id) {
            $sr = SlaughterRecord::find($id);
            if (!$sr) return "#{$id}";
            $info = "<strong>{$sr->e_id}</strong><br>";
            $info .= "<small>{$sr->sex} | {$sr->breed}</small>";
            return $info;
        })->sortable();
        $grid->column('source_address', __('Section/Quarter'))->sortable();
        $grid->column('original_weight', __('Original Weight'))->display(function ($w) {
            return '<strong>' . $w . ' Kgs</strong>';
        })->sortable();
        $grid->column('current_weight', __('Current Weight'))->display(function ($w) {
            $distributed = floatval($this->original_weight) - floatval($w);
            $color = $distributed > 0 ? 'green' : 'gray';
            return $w . ' Kgs<br><small style="color:'.$color.'">(' . $distributed . ' Kgs distributed)</small>';
        })->sortable();
        $grid->column('bar_code', __('Barcode'))->lightbox(['width' => 200, 'height' => 200]);
        $grid->column('animal_id', __('Animal'))->display(function ($a) {
            return $a ? "#{$a}" : 'N/A';
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('source_address', 'Section');
            $filter->between('created_at', 'Date');
            $filter->equal('slaughter_id', 'Slaughter ID');
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(SlaughterDistributionRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('slaughter_id', __('Slaughter id'));
        $show->field('source_address', __('Section'));
        $show->field('original_weight', __('Original weight'));
        $show->field('current_weight', __('Current weight'));
        $show->field('animal_id', __('Animal id'));
        $show->field('bar_code', __('Bar code'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new SlaughterDistributionRecord());

        $form->number('slaughter_id', __('Slaughter id'))->rules('required|exists:slaughter_records,id');
        $form->text('source_address', __('Section'))->rules('required');
        $form->decimal('original_weight', __('Original weight'))->rules('required|numeric|min:0');
        $form->decimal('current_weight', __('Current weight'))->default(0)->rules('numeric|min:0');
        $form->number('animal_id', __('Animal id'))->help('Optional: link to animal record');
        $form->text('bar_code', __('Bar code'));

        return $form;
    }
}
