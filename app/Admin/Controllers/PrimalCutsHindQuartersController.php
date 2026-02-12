<?php

namespace App\Admin\Controllers;

use App\Models\SlaughterRecord;
use App\Models\SlaughterDistributionRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Carbon\Carbon;

class PrimalCutsHindQuartersController extends AdminController
{
    protected $title = 'Primal Cuts - Hind Quarters';

    /**
     * Index interface
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title)
            ->description('Hind quarter primal cuts breakdown')
            ->body($this->grid());
    }

    /**
     * Make a grid builder - Shows hind quarter cuts grouped by EID
     */
    protected function grid()
    {
        $grid = new Grid(new SlaughterRecord());

        // Only show records that have hind quarter cuts
        $grid->model()
            ->whereHas('distributions', function($q) {
                $q->where('source_address', 'like', 'Hind-%')
                  ->whereNotIn('source_address', ['Hind-1/4 - Right', 'Hind-1/4 - Left']);
            })
            ->orderBy('created_at', 'desc');

        // Header with EID
        $grid->column('e_id', 'EID')->expand(function ($model) {
            // Get all hind quarter cuts for this carcass
            $cuts = SlaughterDistributionRecord::where('source_id', $model->id)
                ->where('source_address', 'like', 'Hind-%')
                ->whereNotIn('source_address', ['Hind-1/4 - Right', 'Hind-1/4 - Left'])
                ->get();

            // Group by cut name and side
            $cutData = [];
            foreach($cuts as $cut) {
                // Extract cut name and side from source_address
                $parts = explode(' - ', $cut->source_address);
                $cutName = $parts[0] ?? $cut->source_address;
                $side = isset($parts[1]) && (strpos($parts[1], 'Right') !== false) ? 'Right' : 'Left';
                
                // Clean cut name (remove "Hind-" prefix)
                $cutName = str_replace('Hind-', '', $cutName);
                
                if(!isset($cutData[$cutName])) {
                    $cutData[$cutName] = ['left' => 0, 'right' => 0];
                }
                
                if($side === 'Left') {
                    $cutData[$cutName]['left'] += $cut->original_weight;
                } else {
                    $cutData[$cutName]['right'] += $cut->original_weight;
                }
            }

            // Build HTML table
            $html = '<div style="padding:15px;background:#f9f9f9;">';
            $html .= '<h4 style="color:#6B3C00;margin-top:0;">Primal Cuts Record - EID: ' . $model->e_id . '</h4>';
            $html .= '<table class="table table-bordered" style="background:#fff;">';
            $html .= '<thead style="background:#6B3C00;color:#fff;">';
            $html .= '<tr><th>Primal Cut</th><th>HL (Kg)</th><th>HR (Kg)</th><th>Total (Kg)</th></tr>';
            $html .= '</thead><tbody>';
            
            $grandTotal = 0;
            foreach($cutData as $cutName => $weights) {
                $total = $weights['left'] + $weights['right'];
                $grandTotal += $total;
                
                $html .= '<tr>';
                $html .= '<td><strong>' . ucwords(str_replace('-', ' ', $cutName)) . '</strong></td>';
                $html .= '<td>' . number_format($weights['left'], 2) . '</td>';
                $html .= '<td>' . number_format($weights['right'], 2) . '</td>';
                $html .= '<td><strong>' . number_format($total, 2) . '</strong></td>';
                $html .= '</tr>';
            }
            
            $html .= '<tr style="background:#f5f5f5;font-weight:bold;">';
            $html .= '<td>TOTAL</td><td colspan="2"></td><td>' . number_format($grandTotal, 2) . '</td>';
            $html .= '</tr>';
            
            $html .= '</tbody></table></div>';
            
            return $html;
        })->sortable();

        // Summary columns
        $grid->column('total_hind_cuts', 'Total Cuts')->display(function() {
            $count = SlaughterDistributionRecord::where('source_id', $this->id)
                ->where('source_address', 'like', 'Hind-%')
                ->whereNotIn('source_address', ['Hind-1/4 - Right', 'Hind-1/4 - Left'])
                ->count();
            return $count;
        });

        $grid->column('total_hind_weight', 'Total Weight (kg)')->display(function() {
            $total = SlaughterDistributionRecord::where('source_id', $this->id)
                ->where('source_address', 'like', 'Hind-%')
                ->whereNotIn('source_address', ['Hind-1/4 - Right', 'Hind-1/4 - Left'])
                ->sum('original_weight');
            return '<strong>' . number_format($total, 2) . '</strong>';
        });

        // Actions
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
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
