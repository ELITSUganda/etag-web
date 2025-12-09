<?php

namespace App\Admin\Controllers;

use App\Models\PackagingRecord;
use App\Models\SlaughterRecord;
use App\Models\SlaughterDistributionRecord;
use App\Services\PackagingRecordPdfService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Carbon\Carbon;

class PackagingRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Packaging Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PackagingRecord());

        $grid->column('id', __('ID'))->sortable();
        
        $grid->column('package_code', __('Package Code'))->display(function() {
            return '<span style="font-weight: bold; color: #2c5f2d;">' . $this->package_code . '</span>';
        });
        
        $grid->column('v_id', __('V-ID'))->sortable();
        $grid->column('e_id', __('E-ID'))->sortable();
        
        $grid->column('package_type', __('Type'))->display(function ($type) {
            $color = $type === 'Prime Cut' ? '#4CAF50' : '#FF9800';
            return "<span style='background-color: {$color}; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;'>{$type}</span>";
        });
        
        $grid->column('total_weight', __('Total Weight (kg)'))
            ->display(function ($weight) {
                return number_format($weight, 2);
            })
            ->sortable();
        
        $grid->column('packaging_date', __('Package Date'))->sortable();
        
        $grid->column('expiry_date', __('Expiry Date'))
            ->display(function ($date) {
                $expiry = Carbon::parse($date);
                $now = Carbon::now();
                
                if ($expiry->isPast()) {
                    return "<span style='color: #d32f2f; font-weight: bold;'>{$expiry->format('d-M-Y')} (Expired)</span>";
                } elseif ($expiry->diffInDays($now) <= 2) {
                    return "<span style='color: #f57c00; font-weight: bold;'>{$expiry->format('d-M-Y')} ({$expiry->diffInDays($now)}d left)</span>";
                } else {
                    return $expiry->format('d-M-Y');
                }
            })
            ->sortable();
        
        $grid->column('status', __('Status'))->display(function ($status) {
            $colors = [
                'Active' => '#4CAF50',
                'Sold' => '#2196F3',
                'Expired' => '#f44336',
                'Discarded' => '#9E9E9E',
            ];
            $color = $colors[$status] ?? '#000';
            return "<span style='color: {$color}; font-weight: bold;'>{$status}</span>";
        })->sortable();
        
        $grid->column('pdf_generated', __('PDF'))->display(function ($generated) {
            if ($generated === 'Yes') {
                return '<a href="' . url($this->pdf_file_path) . '" target="_blank" style="color: #2196F3;">
                    <i class="fa fa-file-pdf-o"></i> View PDF
                </a>';
            }
            return '<span style="color: #999;">Not Generated</span>';
        });
        
        $grid->column('packager.name', __('Packaged By'));
        
        $grid->column('created_at', __('Created'))->display(function ($date) {
            return Carbon::parse($date)->format('d-M-Y H:i');
        })->sortable();

        // Filters
        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            
            $filter->like('v_id', 'V-ID');
            $filter->like('e_id', 'E-ID');
            $filter->equal('package_type', 'Package Type')->select([
                'Prime Cut' => 'Prime Cut',
                'Offal' => 'Offal'
            ]);
            $filter->equal('status', 'Status')->select([
                'Active' => 'Active',
                'Sold' => 'Sold',
                'Expired' => 'Expired',
                'Discarded' => 'Discarded'
            ]);
            $filter->between('packaging_date', 'Package Date')->date();
            $filter->between('expiry_date', 'Expiry Date')->date();
        });

        $grid->actions(function ($actions) {
            $actions->add(new \Encore\Admin\Grid\Actions\Delete);
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
        $show = new Show(PackagingRecord::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('package_code', __('Package Code'));
        
        $show->divider();
        $show->panel()->title('Animal Information');
        
        $show->field('v_id', __('V-ID'));
        $show->field('e_id', __('E-ID'));
        $show->field('lhc', __('LHC'));
        $show->field('breed', __('Breed'));
        $show->field('sex', __('Sex'));
        
        $show->divider();
        $show->panel()->title('Package Information');
        
        $show->field('package_type', __('Package Type'));
        $show->field('total_weight', __('Total Weight (kg)'))->as(function ($weight) {
            return number_format($weight, 2);
        });
        $show->field('packaging_date', __('Packaging Date'));
        $show->field('expiry_date', __('Expiry Date'));
        $show->field('packager.name', __('Packaged By'));
        $show->field('status', __('Status'));
        
        $show->divider();
        $show->panel()->title('Cut/Offal Breakdown');
        
        $show->field('cut_breakdown', __('Breakdown'))->unescape()->as(function () {
            $breakdown = $this->getCutBreakdown();
            if (empty($breakdown)) {
                return '<p style="color: #999;">No cuts/offals recorded</p>';
            }
            
            $html = '<table class="table table-bordered"><thead><tr><th>Cut/Offal Name</th><th>Weight (kg)</th></tr></thead><tbody>';
            foreach ($breakdown as $name => $weight) {
                $html .= "<tr><td>{$name}</td><td>" . number_format($weight, 2) . "</td></tr>";
            }
            $html .= '</tbody></table>';
            return $html;
        });
        
        $show->divider();
        $show->panel()->title('PDF Label');
        
        $show->field('pdf_generated', __('PDF Generated'));
        $show->field('pdf_file_path', __('PDF File'))->unescape()->as(function ($path) {
            if ($path && file_exists(public_path($path))) {
                return '<a href="' . url($path) . '" target="_blank" class="btn btn-primary">
                    <i class="fa fa-file-pdf-o"></i> View/Download PDF
                </a>';
            }
            return '<span style="color: #999;">PDF not generated</span>';
        });
        
        $show->field('barcode', __('Barcode'));
        $show->field('qr_code_link', __('QR Code Link'));
        
        $show->divider();
        $show->field('notes', __('Notes'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PackagingRecord());

        $form->divider('Slaughter Information');
        
        $form->select('slaughter_record_id', __('Slaughter Record'))
            ->options(function ($id) {
                if ($id) {
                    $record = SlaughterRecord::find($id);
                    return [$record->id => $record->v_id . ' - ' . $record->e_id];
                }
                return SlaughterRecord::latest()->take(100)->pluck('v_id', 'id');
            })
            ->ajax('/admin/api/slaughter-records')
            ->rules('required');
        
        $form->select('slaughter_distribution_record_id', __('Distribution Record (Optional)'))
            ->options(function ($id) {
                if ($id) {
                    $record = SlaughterDistributionRecord::find($id);
                    return [$record->id => $record->e_id . ' - ' . $record->source_address];
                }
                return [];
            })
            ->help('Select distribution record to link barcode/QR code');
        
        $form->divider('Package Details');
        
        $form->select('package_type', __('Package Type'))
            ->options([
                'Prime Cut' => 'Prime Cut',
                'Offal' => 'Offal'
            ])
            ->rules('required')
            ->help('Select Prime Cut or Offal');
        
        $form->date('packaging_date', __('Packaging Date'))
            ->default(date('Y-m-d'))
            ->rules('required');
        
        $form->number('shelf_life_days', __('Shelf Life (Days)'))
            ->default(7)
            ->rules('required|min:1|max:365')
            ->help('Number of days before expiry');
        
        $form->divider('Prime Cut Weights (kg) - Only for Prime Cut type');
        
        foreach (PackagingRecord::getPrimeCutLabels() as $label => $field) {
            $form->decimal($field, __($label))->default(0);
        }
        
        $form->divider('Offal Weights (kg) - Only for Offal type');
        
        foreach (PackagingRecord::getOffalLabels() as $label => $field) {
            $form->decimal($field, __($label))->default(0);
        }
        
        $form->divider('Additional Information');
        
        $form->select('packaged_by', __('Packaged By'))
            ->options(\Encore\Admin\Auth\Database\Administrator::all()->pluck('name', 'id'))
            ->default(admin()->user()->id)
            ->rules('required');
        
        $form->select('status', __('Status'))
            ->options([
                'Active' => 'Active',
                'Sold' => 'Sold',
                'Expired' => 'Expired',
                'Discarded' => 'Discarded'
            ])
            ->default('Active');
        
        $form->textarea('notes', __('Notes'));
        
        // Saving callback
        $form->saving(function (Form $form) {
            // Auto-populate animal info from slaughter record
            if ($form->slaughter_record_id) {
                $slaughter = SlaughterRecord::find($form->slaughter_record_id);
                if ($slaughter) {
                    $form->v_id = $slaughter->v_id;
                    $form->e_id = $slaughter->e_id;
                    $form->lhc = $slaughter->lhc;
                    $form->breed = $slaughter->breed;
                    $form->sex = $slaughter->sex;
                }
            }
            
            // Auto-populate barcode/QR from distribution record
            if ($form->slaughter_distribution_record_id) {
                $dist = SlaughterDistributionRecord::find($form->slaughter_distribution_record_id);
                if ($dist) {
                    $form->barcode = $dist->bar_code;
                    $form->qr_code_link = $dist->qr_code;
                    $form->animal_id = $dist->animal_id;
                }
            }
            
            // Calculate expiry date
            if ($form->packaging_date && $form->shelf_life_days) {
                $form->expiry_date = Carbon::parse($form->packaging_date)->addDays($form->shelf_life_days);
            }
        });
        
        // Saved callback - Generate PDF
        $form->saved(function (Form $form) {
            $record = PackagingRecord::find($form->model()->id);
            if ($record) {
                try {
                    $pdfService = new PackagingRecordPdfService();
                    $pdfService->generateLabel($record);
                } catch (\Exception $e) {
                    admin_toastr('PDF generation failed: ' . $e->getMessage(), 'warning');
                }
            }
        });

        return $form;
    }
}
