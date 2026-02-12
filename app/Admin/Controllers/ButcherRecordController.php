<?php

namespace App\Admin\Controllers;

use App\Models\ButcherRecord;
use App\Models\SlaughterDistributionRecord;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Carbon\Carbon;

class ButcherRecordController extends AdminController
{
    protected $title = 'Butcher Records';

    protected function grid()
    {
        $grid = new Grid(new ButcherRecord());

        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', __('ID'))->sortable();
        $grid->column('created_at', __('Date'))->display(function ($d) {
            return Carbon::parse($d)->format('d/m/Y H:i');
        })->sortable();
        $grid->column('e_id', __('Animal'))->display(function () {
            $id = $this->e_id ?? ($this->v_id ?? 'N/A');
            return "<strong>{$id}</strong><br><small>{$this->source_name}</small>";
        })->sortable();
        $grid->column('cut_type', __('Cut Type'))->display(function ($c) {
            if ($c == 'Prime Cut') {
                return "<span class='label label-primary'>Primal Cuts</span><br><small>{$this->prime_cut_type}</small>";
            } else if ($c == 'Offal Cut') {
                return "<span class='label label-warning'>Offal Cut</span><br><small>{$this->offal_cut_type}</small>";
            }
            return $c;
        })->sortable();
        $grid->column('source_address', __('Section'))->sortable();
        $grid->column('original_weight', __('Weight'))->display(function ($w) {
            $current = floatval($this->current_weight);
            $original = floatval($w);
            $sold = $original - $current;
            return "<strong>{$original} Kgs</strong><br><small style='color:red'>Sold: {$sold} Kgs</small>";
        })->sortable();
        $grid->column('price', __('Price'))->display(function ($p) {
            return $p ? 'UGX ' . number_format($p) : 'N/A';
        })->sortable();
        $grid->column('is_sold', __('Status'))->display(function ($v) {
            if ($v == 'Yes') {
                return "<span class='label label-success'>Sold</span><br><small>{$this->buyer_name}</small>";
            }
            return "<span class='label label-default'>Available</span>";
        })->sortable();
        $grid->column('bar_code', __('Barcode'))->lightbox(['width' => 200, 'height' => 200]);

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('e_id', 'E-ID');
            $filter->like('bar_code', 'Bar code');
            $filter->like('buyer_name', 'Buyer');
            $filter->equal('cut_type', 'Cut Type')->select(['Prime Cut' => 'Primal Cuts', 'Offal Cut' => 'Offal Cut']);
            $filter->equal('is_sold', 'Is sold')->select(['Yes' => 'Yes', 'No' => 'No']);
            $filter->between('created_at', 'Date');
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(ButcherRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('slaughter_date', __('Slaughter date'));
        $show->field('bar_code', __('Bar code'));
        $show->field('qr_code', __('QR code'));
        $show->field('original_weight', __('Original weight'));
        $show->field('current_weight', __('Current weight'));
        $show->field('price', __('Price'));
        $show->field('is_sold', __('Is sold'));
        $show->field('buyer_name', __('Buyer name'));
        $show->field('notes', __('Notes'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new ButcherRecord());

        $form->select('slaughter_distribution_record_id', __('Distribution'))->options(
            SlaughterDistributionRecord::all()->pluck('source_address', 'id')
        )->rules('nullable|exists:slaughter_distribution_records,id');

        $form->text('e_id', __('E-ID'));
        $form->text('v_id', __('V-ID'));
        $form->decimal('original_weight', __('Original weight'))->rules('required|numeric|min:0');
        $form->decimal('current_weight', __('Current weight'))->default(0)->rules('numeric|min:0');
        $form->decimal('price', __('Price'))->default(0);
        $form->select('is_sold', __('Is sold'))->options(['No' => 'No', 'Yes' => 'Yes'])->default('No');
        $form->text('buyer_name', __('Buyer name'));
        $form->text('buyer_phone', __('Buyer phone'));
        $form->textarea('notes', __('Notes'));

        return $form;
    }
}
