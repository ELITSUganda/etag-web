<?php

namespace App\Admin\Controllers;

use App\Models\CentralTagBatch;
use App\Models\DistrictTagDistributionBatch;
use App\Models\Location;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DistrictTagDistributionBatchController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'District Tag Distribution Batches';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DistrictTagDistributionBatch());
        $grid->disableBatchActions();


        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('batch_name', 'Batch Name');
            $filter->like('district_name', 'District Name');
            $filter->equal('district_id', 'District')->select(
                Location::get_districts()->pluck('name', 'id')
            );
            $filter->between('created_at', 'Created Date')->date();
            $central_recs = [];
            foreach (CentralTagBatch::where([])->get() as $key => $val) {
                $central_recs[$val->id] = $val->batch_name . ", VID: " . $val->vid_range_start . " - " . $val->vid_range_end . " (" . $val->available_quantity . " tags)";
            }

            $filter->equal('central_tag_batch_id', 'Central Tag Batch')->select(
                $central_recs
            );
        });


        $grid->quickSearch(
            'batch_name',
            'batch_description',
            'district_name',
            'district_code'
        )->placeholder('Search by batch name', 'batch description', 'district name', 'district code');
        $grid->model()->orderBy('created_at', 'desc');
        $grid->column('id', __('Id'))->sortable()->hide();
        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                $d = \Carbon\Carbon::parse($created_at);
                if ($d == null) {
                    return '';
                }
                return $d->format('d M Y');
            })
            ->sortable();
        $grid->column('batch_name', __('Batch Name'))
            ->sortable()
            ->filter('like');

        $grid->column('batch_description', __('Batch Description'))->hide();
        $grid->column('vid_range_start', __('VID Range'))
            ->display(function ($vid_range_start) {
                return $this->vid_range_start . ' - ' . $this->vid_range_end;
            })->sortable();
        $grid->column('eid_range_start', __('Eid Range'))
            ->display(function ($eid_range_start) {
                return $this->eid_range_start . ' - ' . $this->eid_range_end;
            })->sortable();
        $grid->column('district_name', __('District'));
        $grid->column('district_code', __('District code'))->hide();
        $grid->column('district_id', __('District'))
            ->display(function ($district_id) {
                if ($this->district == null) {
                    return 'N/A';
                }
                return $this->district->name ?? 'N/A';
            })->sortable();
        $grid->column('total_distributed_quantity', __('Total Distributed Quantity'))
            ->display(function ($total_distributed_quantity) {
                return number_format($total_distributed_quantity, 0, '.', ',') . ' tags';
            })->sortable();
        $grid->column('selling_price', __('Selling Price'))
            ->display(function ($selling_price) {
                return 'UGX ' . number_format($selling_price, 0, '.', ',');
            })->sortable();
        $grid->column('available_quantity', __('Available quantity'))
            ->display(function ($available_quantity) {
                return number_format($available_quantity, 0, '.', ',') . ' tags';
            })->sortable();
        $grid->column('details', __('Details'))->hide();

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
        $show = new Show(DistrictTagDistributionBatch::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('batch_name', __('Batch name'));
        $show->field('batch_description', __('Batch description'));
        $show->field('vid_range_start', __('Vid range start'));
        $show->field('vid_range_end', __('Vid range end'));
        $show->field('eid_range_start', __('Eid range start'));
        $show->field('eid_range_end', __('Eid range end'));
        $show->field('district_name', __('District name'));
        $show->field('district_code', __('District code'));
        $show->field('district_id', __('District id'));
        $show->field('total_distributed_quantity', __('Total distributed quantity'));
        $show->field('available_quantity', __('Available quantity'));
        $show->field('selling_price', __('Selling price'));
        $show->field('details', __('Details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DistrictTagDistributionBatch());

        $central_recs = [];
        foreach (CentralTagBatch::where('available_quantity', '>', 0)->get() as $key => $val) {
            $central_recs[$val->id] = $val->batch_name . ", VID: " . $val->vid_range_start . " - " . $val->vid_range_end . " (" . $val->available_quantity . " tags)";
        }

        $default_central_tag_id = null;
        if (isset($_GET['central_tag_batch_id'])) {
            $rec = CentralTagBatch::find($_GET['central_tag_batch_id']);
            if ($rec != null) {
                $default_central_tag_id = $rec->id;
            }
        }

        //selet central_tag_batch_id
        $form->select('central_tag_batch_id', 'Central tags batch')
            ->options($central_recs)
            ->default($default_central_tag_id)
            ->rules('required')
            ->required();

        $form->decimal('vid_range_start', __('Vid Range Start'))
            ->rules('required')
            ->required();
        $form->decimal('vid_range_end', __('Vid Range End'))
            ->rules('required')
            ->required();

        $form->decimal('eid_range_start', __('Eid range start'))
            ->rules('required')
            ->required();

        $form->decimal('eid_range_end', __('Eid range end'))
            ->rules('required')
            ->required();

        $districts = [];
        foreach (Location::get_districts() as $key => $value) {
            $districts[$value->id] = $value->name . " - #" . $value->id;
        }
        $form->select('district_id', __('Select District'))
            ->options($districts)
            ->rules('required')
            ->required();


        $form->decimal('total_distributed_quantity', __('Total Distributed Quantity'))
            ->rules('required')
            ->required();
        $form->decimal('selling_price', __('Selling price (UGX)'))
            ->rules('required')
            ->required();
        $form->text('details', __('Details'));

        return $form;
    }
}
