<?php

namespace App\Admin\Controllers;

use App\Models\SlaughterRecord;
use App\Models\SlaughterDistributionRecord;
use App\Models\PackagingRecord;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;

class ButcheryRecordController extends AdminController
{
    protected $title = 'Butchery Records';

    /**
     * Index interface with custom dashboard view
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title)
            ->description('Complete butchery operations overview')
            ->body($this->grid());
    }

    protected function grid()
    {
        $grid = new Grid(new SlaughterRecord());
        
        // ============================================
        // STATISTICS HEADER
        // ============================================
        $grid->header(function ($query) {
            // Time-based statistics
            $today = Carbon::today();
            $weekStart = Carbon::now()->startOfWeek();
            $monthStart = Carbon::now()->startOfMonth();
            
            $todayCount = SlaughterRecord::whereDate('created_at', $today)->count();
            $weekCount = SlaughterRecord::where('created_at', '>=', $weekStart)->count();
            $monthCount = SlaughterRecord::where('created_at', '>=', $monthStart)->count();
            $totalSlaughters = SlaughterRecord::count();
            
            // Carcass Statistics
            $totalCarcassWeight = SlaughterRecord::sum('post_weight') ?? 0;
            $avgCarcassWeight = $totalSlaughters > 0 ? round($totalCarcassWeight / $totalSlaughters, 1) : 0;
            
            // Grade Distribution & Quality
            $gradeA = SlaughterRecord::where('post_grade', 'Grade A')->count();
            $gradeB = SlaughterRecord::where('post_grade', 'Grade B')->count();
            $gradeC = SlaughterRecord::where('post_grade', 'Grade C')->count();
            $gradeOther = SlaughterRecord::whereNotIn('post_grade', ['Grade A', 'Grade B', 'Grade C'])
                ->whereNotNull('post_grade')
                ->where('post_grade', '!=', '')
                ->count();
            $notGraded = SlaughterRecord::where(function($q) {
                $q->whereNull('post_grade')->orWhere('post_grade', '');
            })->count();
            
            $gradeTotal = $gradeA + $gradeB + $gradeC + $gradeOther + $notGraded;
            $qualityRate = $gradeTotal > 0 ? round((($gradeA + $gradeB) / $gradeTotal) * 100) : 0;
            
            // Processing Statistics - Quarters
            $totalQuarters = SlaughterDistributionRecord::whereIn('source_address', [
                'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                'Hind-1/4 - Right', 'Hind-1/4 - Left'
            ])->count();
            $quartersWeight = SlaughterDistributionRecord::whereIn('source_address', [
                'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                'Hind-1/4 - Right', 'Hind-1/4 - Left'
            ])->sum('original_weight') ?? 0;
            
            // Processing Statistics - Primal Cuts
            $primalCuts = SlaughterDistributionRecord::where(function($q) {
                $q->where('source_address', 'like', 'Fore-%')
                  ->orWhere('source_address', 'like', 'Hind-%');
            })->whereNotIn('source_address', [
                'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                'Hind-1/4 - Right', 'Hind-1/4 - Left'
            ])->count();
            $primalWeight = SlaughterDistributionRecord::where(function($q) {
                $q->where('source_address', 'like', 'Fore-%')
                  ->orWhere('source_address', 'like', 'Hind-%');
            })->whereNotIn('source_address', [
                'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                'Hind-1/4 - Right', 'Hind-1/4 - Left'
            ])->sum('original_weight') ?? 0;
            
            // Processing Statistics - Offal
            $offalCuts = SlaughterDistributionRecord::where('source_address', 'like', 'Offal%')->count();
            $offalWeight = SlaughterDistributionRecord::where('source_address', 'like', 'Offal%')
                ->sum('original_weight') ?? 0;
            
            // Packaging Statistics
            $totalPackages = PackagingRecord::count();
            $packagesWeight = PackagingRecord::sum('total_weight') ?? 0;
            $packagesWithBarcode = PackagingRecord::whereNotNull('barcode')
                ->where('barcode', '!=', '')
                ->count();
            
            // Inspection & Compliance Statistics
            $withAnteFindings = SlaughterRecord::where(function($q) {
                $q->whereNotNull('has_post_info')
                  ->where('has_post_info', '!=', '')
                  ->where('has_post_info', '!=', 'No')
                  ->where('has_post_info', '!=', 'null');
            })->count();
            $withPostFindings = SlaughterRecord::where(function($q) {
                $q->whereNotNull('post_other')
                  ->where('post_other', '!=', '')
                  ->where('post_other', '!=', 'null');
            })->count();
            $inspectionRate = $totalSlaughters > 0 ? round((($totalSlaughters - ($withAnteFindings + $withPostFindings)) / $totalSlaughters) * 100) : 0;
            
            // Processing Completion
            $completedRecords = SlaughterRecord::where('breed', 'Done')->count();
            $ongoingRecords = SlaughterRecord::where('breed', '!=', 'Done')->count();
            $completionRate = $totalSlaughters > 0 ? round(($completedRecords / $totalSlaughters) * 100) : 0;
            
            // Yield Analysis
            $utilizationRate = $totalCarcassWeight > 0 ? round((($quartersWeight + $primalWeight + $offalWeight) / $totalCarcassWeight) * 100, 1) : 0;
            
            // Top Performing Slaughter Houses
            $topHouses = SlaughterRecord::select('destination_slaughter_house', DB::raw('COUNT(*) as total'))
                ->whereNotNull('destination_slaughter_house')
                ->groupBy('destination_slaughter_house')
                ->orderBy('total', 'DESC')
                ->limit(5)
                ->get();
            
            // Recent High-Grade Carcasses
            $recentHighGrade = SlaughterRecord::whereIn('post_grade', ['Grade A', 'Grade B'])
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get();
            
            // Most Common Cut Types
            $topCutTypes = SlaughterDistributionRecord::select('source_address', DB::raw('COUNT(*) as total'))
                ->whereNotIn('source_address', [
                    'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                    'Hind-1/4 - Right', 'Hind-1/4 - Left'
                ])
                ->groupBy('source_address')
                ->orderBy('total', 'DESC')
                ->limit(8)
                ->get();

            return view('admin.butchery-records-stats', compact(
                'todayCount', 'weekCount', 'monthCount', 'totalSlaughters',
                'totalCarcassWeight', 'avgCarcassWeight',
                'gradeA', 'gradeB', 'gradeC', 'gradeOther', 'notGraded', 'qualityRate',
                'totalQuarters', 'quartersWeight', 'primalCuts', 'primalWeight', 
                'offalCuts', 'offalWeight',
                'totalPackages', 'packagesWeight', 'packagesWithBarcode',
                'withAnteFindings', 'withPostFindings', 'inspectionRate',
                'completedRecords', 'ongoingRecords', 'completionRate',
                'utilizationRate', 'topHouses', 'recentHighGrade', 'topCutTypes'
            ));
        });
        
        // ============================================
        // FILTERS AND SEARCH
        // ============================================
        $grid->quickSearch('e_id', 'v_id', 'lhc')->placeholder('Search E-ID, V-ID, LHC');
        
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('e_id', 'E-ID');
            $filter->like('v_id', 'V-ID');
            $filter->between('created_at', 'Slaughter Date')->date();
            $filter->equal('post_grade', 'Carcass Grade')->select([
                'Grade A' => 'Grade A',
                'Grade B' => 'Grade B',
                'Grade C' => 'Grade C',
                'Grade D' => 'Grade D',
                'Grade E' => 'Grade E',
            ]);
            $filter->equal('sex', 'Sex')->select([
                'Male' => 'Male',
                'Female' => 'Female',
            ]);
            $filter->where(function ($query) {
                $query->whereHas('distributions', function($q) {
                    // Has cuts created
                });
            }, 'Has Cuts')->checkbox([
                1 => 'Yes'
            ]);
            $filter->where(function ($query) {
                $query->whereHas('packagingRecords', function($q) {
                    // Has packaging
                });
            }, 'Has Packaging')->checkbox([
                1 => 'Yes'
            ]);
        });

        $grid->model()->orderBy('id', 'DESC');

        // ============================================
        // GRID COLUMNS
        // ============================================
        
        // Date
        $grid->column('created_at', __('Date'))
            ->display(function ($f) {
                return Carbon::parse($f)->format('d M Y');
            })
            ->width(100)
            ->sortable();

        // Carcass ID
        $grid->column('e_id', __('E-ID'))
            ->display(function ($f) {
                return '<strong>' . $f . '</strong>';
            })
            ->width(120)
            ->sortable();

        // Animal Info
        $grid->column('animal_info', __('Animal'))
            ->display(function () {
                $sex = $this->sex ?? '-';
                $age = $this->post_age ?? '-';
                $dentition = $this->post_dentition ?? '-';
                return "<small>Sex: {$sex}<br>Age: {$age}<br>Dentition: {$dentition}</small>";
            })
            ->width(120);

        // Carcass Weight & Grade
        $grid->column('carcass_info', __('Carcass'))
            ->display(function () {
                $weight = $this->post_weight ?? 'N/A';
                $grade = $this->post_grade ?? 'Not Graded';
                $gradeColor = '#6B3C00';
                if($grade == 'Grade A') $gradeColor = '#6B3C00';
                elseif($grade == 'Grade B') $gradeColor = '#8B5A1B';
                elseif($grade == 'Grade C') $gradeColor = '#A67C3D';
                
                return "<div><strong>{$weight} kg</strong></div>" .
                       "<div style='color:{$gradeColor};font-size:11px;'>{$grade}</div>";
            })
            ->width(110);

        // Quarters Created
        $grid->column('quarters', __('Quarters'))
            ->display(function () {
                $count = SlaughterDistributionRecord::where('source_id', $this->id)
                    ->whereIn('source_address', [
                        'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                        'Hind-1/4 - Right', 'Hind-1/4 - Left'
                    ])
                    ->count();
                $weight = SlaughterDistributionRecord::where('source_id', $this->id)
                    ->whereIn('source_address', [
                        'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                        'Hind-1/4 - Right', 'Hind-1/4 - Left'
                    ])
                    ->sum('original_weight') ?? 0;
                
                if($count > 0) {
                    return "<div><strong>{$count}</strong> quarters</div><small>" . number_format($weight, 1) . " kg</small>";
                }
                return '<span style="color:#999;">—</span>';
            })
            ->width(100);

        // Primal Cuts
        $grid->column('primal_cuts', __('Primal Cuts'))
            ->display(function () {
                $count = SlaughterDistributionRecord::where('source_id', $this->id)
                    ->where(function($q) {
                        $q->where('source_address', 'like', 'Fore-%')
                          ->orWhere('source_address', 'like', 'Hind-%');
                    })
                    ->whereNotIn('source_address', [
                        'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                        'Hind-1/4 - Right', 'Hind-1/4 - Left'
                    ])
                    ->count();
                $weight = SlaughterDistributionRecord::where('source_id', $this->id)
                    ->where(function($q) {
                        $q->where('source_address', 'like', 'Fore-%')
                          ->orWhere('source_address', 'like', 'Hind-%');
                    })
                    ->whereNotIn('source_address', [
                        'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                        'Hind-1/4 - Right', 'Hind-1/4 - Left'
                    ])
                    ->sum('original_weight') ?? 0;
                
                if($count > 0) {
                    return "<div><strong>{$count}</strong> cuts</div><small>" . number_format($weight, 1) . " kg</small>";
                }
                return '<span style="color:#999;">—</span>';
            })
            ->width(100);

        // Offal
        $grid->column('offal', __('Offal'))
            ->display(function () {
                $count = SlaughterDistributionRecord::where('source_id', $this->id)
                    ->where('source_address', 'like', 'Offal%')
                    ->count();
                $weight = SlaughterDistributionRecord::where('source_id', $this->id)
                    ->where('source_address', 'like', 'Offal%')
                    ->sum('original_weight') ?? 0;
                
                if($count > 0) {
                    return "<div><strong>{$count}</strong> items</div><small>" . number_format($weight, 1) . " kg</small>";
                }
                return '<span style="color:#999;">—</span>';
            })
            ->width(100);

        // Packaging
        $grid->column('packaging', __('Packaging'))
            ->display(function () {
                $count = PackagingRecord::where('slaughter_record_id', $this->id)->count();
                $weight = PackagingRecord::where('slaughter_record_id', $this->id)
                    ->sum('total_weight') ?? 0;
                
                if($count > 0) {
                    return "<div><strong>{$count}</strong> packages</div><small>" . number_format($weight, 1) . " kg</small>";
                }
                return '<span style="color:#999;">—</span>';
            })
            ->width(100);

        // Inspection Findings
        $grid->column('inspection', __('Inspection'))
            ->display(function () {
                $ante = $this->has_post_info;
                $post = $this->post_other;
                
                $anteStatus = (empty($ante) || $ante == 'No' || $ante == 'null') ? false : true;
                $postStatus = (empty($post) || $post == 'null') ? false : true;
                
                if(!$anteStatus && !$postStatus) {
                    return '<small style="color:#6B3C00;">✓ Clear</small>';
                } else {
                    $findings = [];
                    if($anteStatus) $findings[] = 'Ante';
                    if($postStatus) $findings[] = 'Post';
                    return '<small style="color:#d9534f;">⚠ ' . implode(', ', $findings) . '</small>';
                }
            })
            ->width(90);

        // Progress Status
        $grid->column('status', __('Status'))
            ->display(function () {
                $isDone = strtolower($this->breed) == 'done';
                $color = $isDone ? '#6B3C00' : '#999';
                $text = $isDone ? 'Completed' : 'Ongoing';
                return "<span style='color:{$color};font-weight:600;font-size:11px;'>{$text}</span>";
            })
            ->width(90)
            ->sortable();

        // Slaughtered By
        $grid->column('administrator_id', __('By'))
            ->display(function ($f) {
                $u = Administrator::find($f);
                return $u ? '<small>' . $u->name . '</small>' : '<small>N/A</small>';
            })
            ->width(120);

        // Actions
        $grid->column('actions', __('Actions'))
            ->display(function () {
                $detailsUrl = admin_url('butchery-records/' . $this->id);
                return '<a href="' . $detailsUrl . '" class="btn btn-sm btn-primary">
                    <i class="fa fa-eye"></i> Details
                </a>';
            })
            ->width(100);

        // Hide unnecessary columns
        $grid->column('v_id')->hide();
        $grid->column('lhc')->hide();
        $grid->column('destination_slaughter_house')->hide();

        // Disable bulk actions and configure grid
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
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
        $show = new Show(SlaughterRecord::findOrFail($id));

        $show->panel()
            ->title('Butchery Record Details')
            ->tools(function ($tools) use ($id) {
                $tools->disableEdit();
                $tools->disableDelete();
                $tools->append('<a href="' . admin_url('slaughter-records/' . $id) . '" class="btn btn-sm btn-primary" target="_blank">
                    <i class="fa fa-file-text-o"></i> Full Report
                </a>');
            });

        // Basic Information
        $show->divider('Carcass Information');
        $show->field('e_id', 'E-ID');
        $show->field('v_id', 'V-ID');
        $show->field('lhc', 'LHC');
        $show->field('created_at', 'Slaughter Date')->as(function ($f) {
            return Carbon::parse($f)->format('d F Y');
        });
        $show->field('sex', 'Sex');
        $show->field('post_age', 'Age');
        $show->field('post_dentition', 'Dentition');
        
        $show->divider('Carcass Assessment');
        $show->field('post_weight', 'Carcass Weight')->as(function ($f) {
            return $f . ' kg';
        });
        $show->field('post_fat', 'Fat Measurement')->as(function ($f) {
            return $f ?? 'N/A';
        });
        $show->field('post_grade', 'Carcass Grade');
        $show->field('available_weight', 'Available Weight')->as(function ($f) {
            return $f . ' kg';
        });

        $show->divider('Processing Summary');
        
        // Quarters
        $show->field('quarters_summary', 'Quarters')->unescape()->as(function () use ($id) {
            $quarters = SlaughterDistributionRecord::where('source_id', $id)
                ->whereIn('source_address', [
                    'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                    'Hind-1/4 - Right', 'Hind-1/4 - Left'
                ])
                ->get();
            
            if($quarters->isEmpty()) {
                return '<em style="color:#999;">No quarters created</em>';
            }
            
            $html = '<table class="table table-condensed table-bordered" style="max-width:600px;">';
            $html .= '<tr><th>Quarter</th><th>Weight</th><th>Created</th></tr>';
            foreach($quarters as $q) {
                $html .= '<tr>';
                $html .= '<td>' . $q->source_address . '</td>';
                $html .= '<td>' . $q->original_weight . ' kg</td>';
                $html .= '<td>' . Carbon::parse($q->created_at)->format('d M Y H:i') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
            return $html;
        });

        // Primal Cuts
        $show->field('primal_summary', 'Primal Cuts')->unescape()->as(function () use ($id) {
            $cuts = SlaughterDistributionRecord::where('source_id', $id)
                ->where(function($q) {
                    $q->where('source_address', 'like', 'Fore-%')
                      ->orWhere('source_address', 'like', 'Hind-%');
                })
                ->whereNotIn('source_address', [
                    'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                    'Hind-1/4 - Right', 'Hind-1/4 - Left'
                ])
                ->get();
            
            if($cuts->isEmpty()) {
                return '<em style="color:#999;">No primal cuts created</em>';
            }
            
            $html = '<div style="max-height:300px;overflow-y:auto;">';
            $html .= '<table class="table table-condensed table-bordered" style="max-width:600px;">';
            $html .= '<tr><th>Cut Type</th><th>Weight</th></tr>';
            foreach($cuts as $c) {
                $html .= '<tr>';
                $html .= '<td>' . $c->source_address . '</td>';
                $html .= '<td>' . $c->original_weight . ' kg</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
            $html .= '</div>';
            return $html;
        });

        // Offal
        $show->field('offal_summary', 'Offal')->unescape()->as(function () use ($id) {
            $offal = SlaughterDistributionRecord::where('source_id', $id)
                ->where('source_address', 'like', 'Offal%')
                ->get();
            
            if($offal->isEmpty()) {
                return '<em style="color:#999;">No offal records</em>';
            }
            
            $html = '<table class="table table-condensed table-bordered" style="max-width:600px;">';
            $html .= '<tr><th>Item</th><th>Weight</th></tr>';
            foreach($offal as $o) {
                $html .= '<tr>';
                $html .= '<td>' . $o->source_address . '</td>';
                $html .= '<td>' . $o->original_weight . ' kg</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
            return $html;
        });

        // Packaging
        $show->field('packaging_summary', 'Packaging')->unescape()->as(function () use ($id) {
            $packages = PackagingRecord::where('slaughter_record_id', $id)->get();
            
            if($packages->isEmpty()) {
                return '<em style="color:#999;">No packages created</em>';
            }
            
            $html = '<div style="max-height:300px;overflow-y:auto;">';
            $html .= '<table class="table table-condensed table-bordered" style="max-width:600px;">';
            $html .= '<tr><th>Package Type</th><th>Weight</th><th>Barcode</th><th>Date</th></tr>';
            foreach($packages as $p) {
                $html .= '<tr>';
                $html .= '<td>' . $p->package_type . '</td>';
                $html .= '<td>' . $p->total_weight . ' kg</td>';
                $html .= '<td>' . ($p->barcode ?? '—') . '</td>';
                $html .= '<td>' . Carbon::parse($p->packaging_date)->format('d M Y') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
            $html .= '</div>';
            return $html;
        });

        $show->divider('Inspection & Compliance');
        $show->field('has_post_info', 'Ante-mortem Findings')->as(function ($f) {
            if(empty($f) || $f == 'No' || $f == 'null') {
                return 'No findings';
            }
            return $f;
        });
        $show->field('post_other', 'Post-mortem Findings')->as(function ($f) {
            if(empty($f) || $f == 'null') {
                return 'No findings';
            }
            return $f;
        });
        
        $show->field('administrator_id', 'Slaughtered By')->as(function ($f) {
            $u = Administrator::find($f);
            return $u ? $u->name : 'N/A';
        });

        return $show;
    }

    // Relationships for filtering
    public function distributions()
    {
        return $this->hasMany(SlaughterDistributionRecord::class, 'source_id');
    }

    public function packagingRecords()
    {
        return $this->hasMany(PackagingRecord::class, 'slaughter_record_id');
    }
}
