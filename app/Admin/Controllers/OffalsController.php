<?php

namespace App\Admin\Controllers;

use App\Models\SlaughterRecord;
use App\Models\SlaughterDistributionRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Carbon\Carbon;

class OffalsController extends AdminController
{
    protected $title = 'Offals';

    /**
     * Index interface
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title)
            ->description('Offal items breakdown by carcass')
            ->body($this->grid());
    }

    /**
     * Make a grid builder - Shows offal items by EID
     */
    protected function grid()
    {
        $grid = new Grid(new SlaughterRecord());

        // Only show records that have offal items
        $grid->model()
            ->whereHas('distributions', function($q) {
                $q->where('source_address', 'like', 'Offal%');
            })
            ->orderBy('created_at', 'desc');

        // EID Column
        $grid->column('e_id', 'EID')->sortable();

        // Heart
        $grid->column('heart', 'Heart (kg)')->display(function() {
            $offal = SlaughterDistributionRecord::where('source_id', $this->id)
                ->where('source_address', 'like', '%Heart%')
                ->sum('original_weight');
            return $offal > 0 ? number_format($offal, 2) : '—';
        });

        // Kidneys
        $grid->column('kidneys', 'Kidneys (kg)')->display(function() {
            $offal = SlaughterDistributionRecord::where('source_id', $this->id)
                ->where('source_address', 'like', '%Kidney%')
                ->orWhere('source_address', 'like', '%Kidneys%')
                ->where('source_id', $this->id)
                ->sum('original_weight');
            return $offal > 0 ? number_format($offal, 2) : '—';
        });

        // Liver
        $grid->column('liver', 'Liver (kg)')->display(function() {
            $offal = SlaughterDistributionRecord::where('source_id', $this->id)
                ->where('source_address', 'like', '%Liver%')
                ->sum('original_weight');
            return $offal > 0 ? number_format($offal, 2) : '—';
        });

        // Other offals (Tongue, Tripe, Lungs, Tail, Head, Feet, etc.)
        $grid->column('other_offals', 'Other (kg)')->display(function() {
            $offal = SlaughterDistributionRecord::where('source_id', $this->id)
                ->where('source_address', 'like', 'Offal%')
                ->where('source_address', 'not like', '%Heart%')
                ->where('source_address', 'not like', '%Kidney%')
                ->where('source_address', 'not like', '%Kidneys%')
                ->where('source_address', 'not like', '%Liver%')
                ->sum('original_weight');
            return $offal > 0 ? number_format($offal, 2) : '—';
        });

        // Total
        $grid->column('total_offals', 'Total (kg)')->display(function() {
            $total = SlaughterDistributionRecord::where('source_id', $this->id)
                ->where('source_address', 'like', 'Offal%')
                ->sum('original_weight');
            return '<strong>' . number_format($total, 2) . '</strong>';
        });

        // Actions
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            
            // Add expand button to see all offal items
            $actions->add(new \Encore\Admin\Grid\Actions\Expand(function() {
                $offals = SlaughterDistributionRecord::where('source_id', $this->id)
                    ->where('source_address', 'like', 'Offal%')
                    ->get();

                if($offals->isEmpty()) {
                    return '<div style="padding:15px;">No offal items found</div>';
                }

                $html = '<div style="padding:15px;background:#f9f9f9;">';
                $html .= '<h4 style="color:#6B3C00;margin-top:0;">Complete Offal Breakdown - EID: ' . $this->e_id . '</h4>';
                $html .= '<table class="table table-bordered" style="background:#fff;">';
                $html .= '<thead style="background:#6B3C00;color:#fff;">';
                $html .= '<tr><th>Item</th><th>Weight (kg)</th></tr>';
                $html .= '</thead><tbody>';
                
                foreach($offals as $offal) {
                    $html .= '<tr>';
                    $html .= '<td><strong>' . $offal->source_address . '</strong></td>';
                    $html .= '<td>' . number_format($offal->original_weight, 2) . '</td>';
                    $html .= '</tr>';
                }
                
                $html .= '</tbody></table></div>';
                
                return $html;
            }));
        });

        // Filters
        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            $filter->like('e_id', 'EID');
            $filter->between('created_at', 'Date')->date();
        });

        // Disable create/batch actions
        $grid->disableCreateButton();
        $grid->disableBatchActions();

        return $grid;
    }
}
