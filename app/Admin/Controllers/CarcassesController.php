<?php

namespace App\Admin\Controllers;

use App\Models\SlaughterRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Carbon\Carbon;

class CarcassesController extends AdminController
{
    protected $title = 'Carcasses';

    /**
     * Index interface
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title)
            ->description('Slaughtered carcass records')
            ->body($this->grid());
    }

    /**
     * Show interface
     */
    public function show($id, Content $content)
    {
        return $content
            ->title('Carcass Details')
            ->description('Complete carcass information')
            ->body($this->detail($id));
    }

    /**
     * Make a grid builder - Simplified view
     */
    protected function grid()
    {
        $grid = new Grid(new SlaughterRecord());

        $grid->model()->orderBy('created_at', 'desc');

        // Columns as specified
        $grid->column('created_at', 'Date Slaughtered')->display(function($date) {
            return Carbon::parse($date)->format('d M Y');
        })->sortable();

        $grid->column('e_id', 'EID')->sortable();

        $grid->column('post_dentition', 'Dentition')->display(function($dentition) {
            return $dentition ?? 'N/A';
        });

        $grid->column('post_weight', 'Carcase Weight')->display(function($weight) {
            return '<strong>' . number_format($weight ?? 0, 2) . ' kg</strong>';
        })->sortable();

        $grid->column('post_grade', 'Grade')->display(function($grade) {
            if(!$grade || $grade == 'Not Graded') {
                return '<span class="label" style="background:#999;">Not Graded</span>';
            }
            
            $colors = [
                'A' => '#2d862d',
                'B' => '#5cb85c',
                'C' => '#f0ad4e',
                'D' => '#d9534f',
                'E' => '#c9302c',
            ];
            
            $color = $colors[$grade] ?? '#999';
            return '<span class="label" style="background:' . $color . ';">' . $grade . '</span>';
        })->sortable();

        // Actions
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });

        // Filters
        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            
            $filter->like('e_id', 'EID');
            $filter->between('created_at', 'Date Slaughtered')->date();
            $filter->equal('post_grade', 'Grade')->select([
                'A' => 'Grade A',
                'B' => 'Grade B',
                'C' => 'Grade C',
                'D' => 'Grade D',
                'E' => 'Grade E',
                'Not Graded' => 'Not Graded',
            ]);
            $filter->where(function ($query) {
                $query->where('post_weight', '>=', $this->input);
            }, 'Min Weight (kg)');
        });

        // Disable create/delete
        $grid->disableCreateButton();
        $grid->disableBatchActions();

        return $grid;
    }

    /**
     * Make a show builder.
     */
    protected function detail($id)
    {
        $show = new Show(SlaughterRecord::findOrFail($id));

        $show->panel()->title('Carcass Details')->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableDelete();
        });

        $show->field('created_at', 'Date Slaughtered')->as(function($date) {
            return Carbon::parse($date)->format('d F Y');
        });
        $show->field('e_id', 'EID');
        $show->field('v_id', 'VID');
        $show->field('post_dentition', 'Dentition');
        $show->field('post_weight', 'Carcase Weight')->as(function($w) {
            return number_format($w, 2) . ' kg';
        });
        $show->field('post_grade', 'Grade');
        $show->field('post_age', 'Age');
        $show->field('sex', 'Sex');

        return $show;
    }
}
