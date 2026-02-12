<?php

namespace App\Admin\Controllers;

use App\Models\SlaughterRecord;
use App\Models\SlaughterDistributionRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CarcassQuartersController extends AdminController
{
    protected $title = 'Carcass Quarters';

    /**
     * Index interface
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title)
            ->description('Quarter records with weight breakdown')
            ->body($this->grid());
    }

    /**
     * Make a grid builder - Shows quarters grouped by EID
     */
    protected function grid()
    {
        $grid = new Grid(new SlaughterRecord());

        // Only show records that have quarters created
        $grid->model()
            ->whereHas('distributions', function($q) {
                $q->whereIn('source_address', [
                    'Fore-1/4 - Right', 
                    'Fore-1/4 - Left',
                    'Hind-1/4 - Right', 
                    'Hind-1/4 - Left'
                ]);
            })
            ->orderBy('created_at', 'desc');

        // EID Column
        $grid->column('e_id', 'EID')->sortable();

        // Fore Right Quarter
        $grid->column('fore_right', 'Fore Right (kg)')->display(function() {
            $quarter = SlaughterDistributionRecord::where('source_id', $this->id)
                ->where('source_address', 'Fore-1/4 - Right')
                ->first();
            return $quarter ? number_format($quarter->original_weight, 2) : '—';
        });

        // Fore Left Quarter
        $grid->column('fore_left', 'Fore Left (kg)')->display(function() {
            $quarter = SlaughterDistributionRecord::where('source_id', $this->id)
                ->where('source_address', 'Fore-1/4 - Left')
                ->first();
            return $quarter ? number_format($quarter->original_weight, 2) : '—';
        });

        // Hind Right Quarter
        $grid->column('hind_right', 'Hind Right (kg)')->display(function() {
            $quarter = SlaughterDistributionRecord::where('source_id', $this->id)
                ->where('source_address', 'Hind-1/4 - Right')
                ->first();
            return $quarter ? number_format($quarter->original_weight, 2) : '—';
        });

        // Hind Left Quarter
        $grid->column('hind_left', 'Hind Left (kg)')->display(function() {
            $quarter = SlaughterDistributionRecord::where('source_id', $this->id)
                ->where('source_address', 'Hind-1/4 - Left')
                ->first();
            return $quarter ? number_format($quarter->original_weight, 2) : '—';
        });

        // Total Weight
        $grid->column('total_quarters', 'Total (kg)')->display(function() {
            $total = SlaughterDistributionRecord::where('source_id', $this->id)
                ->whereIn('source_address', [
                    'Fore-1/4 - Right', 
                    'Fore-1/4 - Left',
                    'Hind-1/4 - Right', 
                    'Hind-1/4 - Left'
                ])
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
        $grid->disableExport();

        return $grid;
    }
}
