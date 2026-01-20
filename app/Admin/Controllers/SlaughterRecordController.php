<?php

namespace App\Admin\Controllers;

use App\Models\SlaughterRecord;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SlaughterRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Slaughter Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SlaughterRecord());
        
        // Search and Filters
        $grid->quickSearch('e_id', 'v_id', 'lhc')->placeholder('Search by E-ID, V-ID or LHC');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('e_id', 'E-ID');
            $filter->like('v_id', 'V-ID');
            $filter->like('lhc', 'LHC');
            $filter->between('created_at', 'Slaughter Date');
            $filter->equal('post_grade', 'Meat Grade')->select([
                'Grade A' => 'Grade A',
                'Grade B' => 'Grade B',
                'Grade C' => 'Grade C',
                'Grade D' => 'Grade D',
                'Grade E' => 'Grade E',
            ]);
            $filter->equal('carcus_owen_assigned', 'Carcass Assignment')->select([
                'Yes' => 'Assigned',
                'No' => 'Not Assigned',
            ]);
            $filter->equal('sex', 'Sex')->select([
                'Male' => 'Male',
                'Female' => 'Female',
            ]);
        });

        $grid->model()->orderBy('id', 'DESC');

        // 1. Slaughter Date
        $grid->column('created_at', __('Slaughter Date'))
            ->display(function ($f) {
                return Carbon::parse($f)->format('d M Y');
            })
            ->sortable();

        // 2. E-ID
        $grid->column('e_id', __('E-ID'))
            ->sortable();

        // 3. Slaughtered By
        $grid->column('administrator_id', __('Slaughtered By'))
            ->display(function ($f) {
                $u = Administrator::find($f);
                if ($u == null) {
                    return 'N/A';
                }
                return $u->name;
            })
            ->sortable();

        // 4. Ante-mortem Findings
        $grid->column('has_post_info', __('Ante-mortem Findings'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null' || strtolower($f) == 'no') {
                    return 'No findings';
                }
                
                // Try to parse as JSON array
                $data = json_decode($f);
                if ($data !== null && is_array($data) && count($data) > 0) {
                    $findings = array_filter($data);
                    if (count($findings) > 0) {
                        $text = implode(', ', $findings);
                        return '<span title="' . htmlspecialchars($text) . '">' . 
                               htmlspecialchars(substr($text, 0, 60)) . 
                               (strlen($text) > 60 ? '...' : '') . '</span>';
                    }
                }
                
                // If just "Yes", show generic message
                if (strtolower($f) == 'yes') {
                    return 'Findings recorded';
                }
                
                // Otherwise display as is
                return htmlspecialchars($f);
            });

        // 5. Post-mortem Findings
        $grid->column('post_other', __('Post-mortem Findings'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null') {
                    return 'No findings';
                }
                
                // Try to parse as JSON
                $data = json_decode($f);
                if ($data !== null && is_array($data) && count($data) > 0) {
                    $findings = array_filter($data);
                    if (count($findings) > 0) {
                        $text = implode(', ', $findings);
                        return '<span title="' . htmlspecialchars($text) . '">' . 
                               htmlspecialchars(substr($text, 0, 50)) . 
                               (strlen($text) > 50 ? '...' : '') . '</span>';
                    }
                }
                
                // If not JSON, display as is
                $text = trim($f);
                return '<span title="' . htmlspecialchars($text) . '">' . 
                       htmlspecialchars(substr($text, 0, 50)) . 
                       (strlen($text) > 50 ? '...' : '') . '</span>';
            });

        // 6. Dentition
        $grid->column('post_dentition', __('Dentition'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null') {
                    return '-';
                }
                return htmlspecialchars($f);
            })
            ->sortable();

        // 7. Carcass Weight
        $grid->column('post_weight', __('Carcass Weight (kg)'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null') {
                    return 'N/A';
                }
                return $f;
            })
            ->sortable();

        // 8. Carcass Grade
        $grid->column('post_grade', __('Carcass Grade'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null') {
                    return 'Not Graded';
                }
                return htmlspecialchars($f);
            })
            ->sortable();

        // 9. Assigned To
        $grid->column('carcus_owen_name', __('Assigned To'))
            ->display(function ($f) {
                if (empty($f) || $f == 'null') {
                    return '-';
                }
                return $f;
            })
            ->sortable();

        // 10. Status
        $grid->column('breed', __('Status'))
            ->display(function ($f) {
                $isDone = strtolower($f) == 'done';
                return $isDone ? 'Completed' : 'Ongoing';
            })
            ->sortable();

        // 11. Number of Cuts
        $grid->column('cuts_count', __('Cuts'))
            ->display(function () {
                $count = \App\Models\SlaughterDistributionRecord::where('source_id', $this->id)->count();
                return '<span class="badge badge-info">' . $count . '</span>';
            })
            ->sortable();

        // 12. Show Details Column
        $grid->column('details', __('Details'))
            ->display(function () {
                return '<a href="' . admin_url('slaughter-records/' . $this->id) . '" class="btn btn-sm btn-primary">
                    <i class="fa fa-file-text-o"></i> Show Details
                </a>';
            });

        // Additional Details (Hidden - can be shown via column selector)
        $grid->column('v_id', __('V-ID'))->hide();
        $grid->column('lhc', __('LHC'))->hide();
        $grid->column('sex', __('Sex'))->hide();
        $grid->column('post_age', __('Age'))->hide();
        $grid->column('post_fat', __('Fat (mm)'))->hide();
        $grid->column('dob', __('Date of Birth'))->hide();
        $grid->column('fmd', __('Last FMD'))->hide();
        $grid->column('available_weight', __('Available Weight'))->hide();
        $grid->column('destination_slaughter_house', __('Slaughter House'))->hide();

        // Actions
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableExport();
        
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return View
     */
    protected function detail($id)
    {
        $record = SlaughterRecord::findOrFail($id);
        
        // Get all quarters (distribution records)
        $quarters = \App\Models\SlaughterDistributionRecord::where('source_id', $id)
            ->whereIn('source_address', [
                'Fore-1/4 - Right',
                'Fore-1/4 - Left',
                'Hind-1/4 - Right',
                'Hind-1/4 - Left'
            ])
            ->get();
        
        // Get prime cuts grouped by quarter
        $primeCuts = \App\Models\SlaughterDistributionRecord::where('source_id', $id)
            ->where('source_address', 'like', '%-%')
            ->where('source_address', 'not like', 'Fore-1/4%')
            ->where('source_address', 'not like', 'Hind-1/4%')
            ->where('source_address', 'not like', 'Offal%')
            ->get();
        
        // Get offal cuts
        $offalCuts = \App\Models\SlaughterDistributionRecord::where('source_address', 'like', 'Offal%')
            ->where('source_id', $id)
            ->get();

        // Calculate quarter weights
        $foreRight = $quarters->where('source_address', 'Fore-1/4 - Right')->first();
        $foreLeft = $quarters->where('source_address', 'Fore-1/4 - Left')->first();
        $hindRight = $quarters->where('source_address', 'Hind-1/4 - Right')->first();
        $hindLeft = $quarters->where('source_address', 'Hind-1/4 - Left')->first();
        
        $foreRightWeight = $foreRight ? $foreRight->original_weight : 0;
        $foreLeftWeight = $foreLeft ? $foreLeft->original_weight : 0;
        $hindRightWeight = $hindRight ? $hindRight->original_weight : 0;
        $hindLeftWeight = $hindLeft ? $hindLeft->original_weight : 0;
        
        $qTotal = $foreRightWeight + $foreLeftWeight + $hindRightWeight + $hindLeftWeight;

        // Return custom Blade view with all data
        return view('admin.slaughter-record-report', compact(
            'record',
            'quarters',
            'primeCuts',
            'offalCuts',
            'foreRightWeight',
            'foreLeftWeight',
            'hindRightWeight',
            'hindLeftWeight',
            'qTotal'
        ));

        /* Original Laravel-Admin Show implementation (commented out)
        $show = new Show($record);

        // === Carcass Information ===
        $show->panel()
            ->title('Carcass Information')
            ->style('primary')
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableDelete();
            });

        $show->divider();

        $show->field('v_id', __('V-ID'))->badge('success');
        $show->field('e_id', __('E-ID'))->badge('info');
        $show->field('lhc', __('LHC'))->badge('warning');
        $show->field('bar_code', __('Bar Code'));
        
        $show->divider();
        
        $show->field('breed', __('Breed'));
        $show->field('sex', __('Sex'));
        $show->field('dob', __('Date of Birth'));
        $show->field('post_age', __('Age'));
        $show->field('post_dentition', __('Dentition'));
        
        $show->divider();
        
        $show->field('destination_slaughter_house', __('Slaughter House'));
        $show->field('created_at', __('Slaughter Date'));
        $show->field('administrator_id', __('Administrator ID'));
        
        // === Post-Slaughter Details ===
        $show->panel()
            ->title('Post-Slaughter Assessment')
            ->style('success');
            
        $show->field('post_weight', __('Carcass Weight (KGs)'))->badge('danger');
        $show->field('available_weight', __('Available Weight (KGs)'))->badge('warning');
        $show->field('post_grade', __('Grade'));
        $show->field('post_animal', __('Animal Type'));
        $show->field('post_fat', __('Fat Score'));
        $show->field('post_other', __('Other Notes'))->unescape();

        // Build Quarter Table HTML
        $foreRight = $quarters->where('source_address', 'Fore-1/4 - Right')->first();
        $foreLeft = $quarters->where('source_address', 'Fore-1/4 - Left')->first();
        $hindRight = $quarters->where('source_address', 'Hind-1/4 - Right')->first();
        $hindLeft = $quarters->where('source_address', 'Hind-1/4 - Left')->first();
        
        $foreRightWeight = $foreRight ? $foreRight->original_weight : 0;
        $foreLeftWeight = $foreLeft ? $foreLeft->original_weight : 0;
        $hindRightWeight = $hindRight ? $hindRight->original_weight : 0;
        $hindLeftWeight = $hindLeft ? $hindLeft->original_weight : 0;
        
        $qTotal = $foreRightWeight + $foreLeftWeight + $hindRightWeight + $hindLeftWeight;
        
        $quarterHtml = '<div style="margin-top: 20px;"><div class="box box-info"><div class="box-header with-border"><h3 class="box-title">Quarter Record</h3></div><div class="box-body">';
        $quarterHtml .= '<table class="table table-bordered"><thead class="bg-primary"><tr>';
        $quarterHtml .= '<th>EID: ' . $record->e_id . '</th><th colspan="4" class="text-center">Weight in KGs</th><th>Totals</th>';
        $quarterHtml .= '</tr><tr><th></th><th>Fore Right</th><th>Fore Left</th><th>Hind Right</th><th>Hind Left</th><th></th>';
        $quarterHtml .= '</tr></thead><tbody><tr><td><strong>Quarters</strong></td>';
        $quarterHtml .= '<td class="text-center"><span class="badge badge-success">' . $foreRightWeight . '</span></td>';
        $quarterHtml .= '<td class="text-center"><span class="badge badge-success">' . $foreLeftWeight . '</span></td>';
        $quarterHtml .= '<td class="text-center"><span class="badge badge-success">' . $hindRightWeight . '</span></td>';
        $quarterHtml .= '<td class="text-center"><span class="badge badge-success">' . $hindLeftWeight . '</span></td>';
        $quarterHtml .= '<td class="text-center"><strong class="text-danger">' . $qTotal . '</strong></td>';
        $quarterHtml .= '</tr></tbody></table></div></div></div>';
        
        $show->divider();
        $show->html($quarterHtml);

        // Build Fore Quarters Primal Cuts HTML
        $foreCuts = ['Beef boneless', 'Beef Stew', 'Bones', 'Brisket', 'Chops', 'Chuck ribs', 'Family Steak', 'Fore rib', 'Leg Cut'];
        $foreHtml = '<div style="margin-top: 20px;"><div class="box box-warning"><div class="box-header with-border"><h3 class="box-title">Fore Quarters - Primal Cuts Record</h3></div><div class="box-body">';
        $foreHtml .= '<table class="table table-bordered"><thead class="bg-warning"><tr><th>Primal Cuts</th><th>FL KGs</th><th>FR KGs</th><th>Totals</th></tr></thead><tbody>';
        
        $totalFL = 0;
        $totalFR = 0;
        foreach ($foreCuts as $cut) {
            $fl = $primeCuts->where('source_address', 'like', "%Fore%Left%$cut%")->first();
            $fr = $primeCuts->where('source_address', 'like', "%Fore%Right%$cut%")->first();
            $flW = $fl ? $fl->original_weight : 0;
            $frW = $fr ? $fr->original_weight : 0;
            $totalFL += $flW;
            $totalFR += $frW;
            $foreHtml .= '<tr><td><strong>' . $cut . '</strong></td>';
            $foreHtml .= '<td class="text-center">' . ($flW > 0 ? '<span class="badge badge-primary">' . $flW . '</span>' : '-') . '</td>';
            $foreHtml .= '<td class="text-center">' . ($frW > 0 ? '<span class="badge badge-primary">' . $frW . '</span>' : '-') . '</td>';
            $foreHtml .= '<td class="text-center">' . ($flW + $frW) . '</td></tr>';
        }
        $foreHtml .= '<tr class="bg-light"><td><strong>TOTALS</strong></td>';
        $foreHtml .= '<td class="text-center"><strong class="text-danger">' . $totalFL . '</strong></td>';
        $foreHtml .= '<td class="text-center"><strong class="text-danger">' . $totalFR . '</strong></td>';
        $foreHtml .= '<td class="text-center"><strong class="text-danger">' . ($totalFL + $totalFR) . '</strong></td></tr>';
        $foreHtml .= '</tbody></table></div></div></div>';
        
        $show->divider();
        $show->html($foreHtml);

        // Build Hind Quarters Primal Cuts HTML
        $hindCuts = ['Fillet', 'Oxtail', 'Rib eye', 'Rolled loin', 'Rump', 'Silver side', 'Sirloin / Striploin', 'T Bone', 'Topside /Beef Roast', 'Veal Steak'];
        $hindHtml = '<div style="margin-top: 20px;"><div class="box box-danger"><div class="box-header with-border"><h3 class="box-title">Hind Quarters - Primal Cuts Record</h3></div><div class="box-body">';
        $hindHtml .= '<table class="table table-bordered"><thead class="bg-danger text-white"><tr><th>Primal Cuts</th><th>HL KGs</th><th>HR KGs</th><th>Totals</th></tr></thead><tbody>';
        
        $totalHL = 0;
        $totalHR = 0;
        foreach ($hindCuts as $cut) {
            $hl = $primeCuts->where('source_address', 'like', "%Hind%Left%$cut%")->first();
            $hr = $primeCuts->where('source_address', 'like', "%Hind%Right%$cut%")->first();
            $hlW = $hl ? $hl->original_weight : 0;
            $hrW = $hr ? $hr->original_weight : 0;
            $totalHL += $hlW;
            $totalHR += $hrW;
            $hindHtml .= '<tr><td><strong>' . $cut . '</strong></td>';
            $hindHtml .= '<td class="text-center">' . ($hlW > 0 ? '<span class="badge badge-success">' . $hlW . '</span>' : '-') . '</td>';
            $hindHtml .= '<td class="text-center">' . ($hrW > 0 ? '<span class="badge badge-success">' . $hrW . '</span>' : '-') . '</td>';
            $hindHtml .= '<td class="text-center">' . ($hlW + $hrW) . '</td></tr>';
        }
        $hindHtml .= '<tr class="bg-light"><td><strong>TOTALS</strong></td>';
        $hindHtml .= '<td class="text-center"><strong class="text-danger">' . $totalHL . '</strong></td>';
        $hindHtml .= '<td class="text-center"><strong class="text-danger">' . $totalHR . '</strong></td>';
        $hindHtml .= '<td class="text-center"><strong class="text-danger">' . ($totalHL + $totalHR) . '</strong></td></tr>';
        $hindHtml .= '</tbody></table></div></div></div>';
        
        $show->divider();
        $show->html($hindHtml);

        // Build Offals HTML
        $offalTypes = ['Heart', 'Kidneys', 'Liver', 'Brain', 'Tongue', 'Tripe', 'Tail', 'Head', 'Other'];
        $offalHtml = '<div style="margin-top: 20px;"><div class="box box-success"><div class="box-header with-border"><h3 class="box-title">Offals Record</h3></div><div class="box-body">';
        $offalHtml .= '<table class="table table-bordered"><thead class="bg-success text-white"><tr><th>EID: ' . $record->e_id . '</th>';
        foreach ($offalTypes as $type) {
            $offalHtml .= '<th>' . $type . '</th>';
        }
        $offalHtml .= '<th>Totals</th></tr></thead><tbody><tr><td><strong>Weight (KGs)</strong></td>';
        
        $offalTotal = 0;
        foreach ($offalTypes as $type) {
            $offal = $offalCuts->where('source_address', 'like', "%$type%")->first();
            $weight = $offal ? $offal->original_weight : 0;
            $offalTotal += $weight;
            $offalHtml .= '<td class="text-center">' . ($weight > 0 ? '<span class="badge badge-info">' . $weight . '</span>' : '-') . '</td>';
        }
        $offalHtml .= '<td class="text-center"><strong class="text-danger">' . $offalTotal . '</strong></td></tr></tbody></table></div></div></div>';
        
        $show->divider();
        $show->html($offalHtml);

        return $show;
        */
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SlaughterRecord());

        $form->textarea('lhc', __('Lhc'));
        $form->textarea('v_id', __('V id'));
        $form->textarea('e_id', __('E id'));
        $form->textarea('breed', __('Breed'));
        $form->textarea('sex', __('Sex'));
        $form->textarea('dob', __('Dob'));
        $form->textarea('fmd', __('Fmd'));
        $form->textarea('destination_slaughter_house', __('Destination slaughter house'));
        $form->textarea('details', __('Details'));
        $form->number('administrator_id', __('Administrator id'))->default(14);
        $form->textarea('type', __('Type'));
        $form->textarea('bar_code', __('Bar code'));
        $form->textarea('post_grade', __('Post grade'));
        $form->textarea('post_animal', __('Post animal'));
        $form->textarea('post_age', __('Post age'));
        $form->textarea('post_dentition', __('Post dentition'));
        $form->textarea('post_weight', __('Post weight'));
        $form->textarea('post_fat', __('Post fat'));
        $form->textarea('post_other', __('Post other'));
        $form->textarea('has_post_info', __('Has post info'));
        $form->textarea('available_weight', __('Available weight'));

        return $form;
    }
}
