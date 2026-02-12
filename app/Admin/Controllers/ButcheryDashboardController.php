<?php

namespace App\Admin\Controllers;

use App\Models\SlaughterDistributionRecord;
use App\Models\SlaughterRecord;
use App\Models\PackagingRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ButcheryDashboardController extends AdminController
{
    public function index(Content $content)
    {
        return $content
            ->title('National Butchery Observatory')
            ->description('Analytics & Inspection Overview')
            ->body($this->dashboard());
    }

    protected function dashboard()
    {
        // ============================================
        // NATIONAL STATISTICS
        // ============================================
        
        $totalSlaughters = SlaughterRecord::count();
        $totalCarcassWeight = SlaughterRecord::sum('post_weight') ?? 0;
        $avgCarcassWeight = $totalSlaughters > 0 ? round($totalCarcassWeight / $totalSlaughters, 1) : 0;
        
        // Time-based Analysis
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $yearStart = Carbon::now()->startOfYear();
        
        $todayCount = SlaughterRecord::whereDate('created_at', $today)->count();
        $weekCount = SlaughterRecord::where('created_at', '>=', $weekStart)->count();
        $monthCount = SlaughterRecord::where('created_at', '>=', $monthStart)->count();
        $yearCount = SlaughterRecord::where('created_at', '>=', $yearStart)->count();
        
        // ============================================
        // QUALITY & GRADE DISTRIBUTION
        // ============================================
        
        $gradeA = SlaughterRecord::where('post_grade', 'Grade A')->count();
        $gradeB = SlaughterRecord::where('post_grade', 'Grade B')->count();
        $gradeC = SlaughterRecord::where('post_grade', 'Grade C')->count();
        $gradeD = SlaughterRecord::where('post_grade', 'Grade D')->count();
        $gradeE = SlaughterRecord::where('post_grade', 'Grade E')->count();
        $notGraded = SlaughterRecord::whereNull('post_grade')
            ->orWhere('post_grade', '')
            ->orWhere('post_grade', 'null')
            ->count();
        
        $gradeTotal = $gradeA + $gradeB + $gradeC + $gradeD + $gradeE + $notGraded;
        $qualityRate = $gradeTotal > 0 ? round((($gradeA + $gradeB) / $gradeTotal) * 100, 1) : 0;
        
        // Average weight by grade
        $gradeAAvg = SlaughterRecord::where('post_grade', 'Grade A')->avg('post_weight') ?? 0;
        $gradeBAvg = SlaughterRecord::where('post_grade', 'Grade B')->avg('post_weight') ?? 0;
        $gradeCAvg = SlaughterRecord::where('post_grade', 'Grade C')->avg('post_weight') ?? 0;
        
        // ============================================
        // INSPECTION & HEALTH ANALYSIS
        // ============================================
        
        // Ante-mortem findings
        $withAnteFindings = SlaughterRecord::where(function($q) {
            $q->whereNotNull('has_post_info')
              ->where('has_post_info', '!=', '')
              ->where('has_post_info', '!=', 'No')
              ->where('has_post_info', '!=', 'null');
        })->count();
        
        // Post-mortem findings
        $withPostFindings = SlaughterRecord::where(function($q) {
            $q->whereNotNull('post_other')
              ->where('post_other', '!=', '')
              ->where('post_other', '!=', 'null');
        })->count();
        
        $totalInspected = $totalSlaughters;
        $clearInspections = $totalInspected - ($withAnteFindings + $withPostFindings);
        $clearRate = $totalInspected > 0 ? round(($clearInspections / $totalInspected) * 100, 1) : 0;
        
        // Recent findings for review
        $recentWithFindings = SlaughterRecord::where(function($q) {
            $q->where(function($q1) {
                $q1->whereNotNull('has_post_info')
                   ->where('has_post_info', '!=', '')
                   ->where('has_post_info', '!=', 'No')
                   ->where('has_post_info', '!=', 'null');
            })->orWhere(function($q2) {
                $q2->whereNotNull('post_other')
                   ->where('post_other', '!=', '')
                   ->where('post_other', '!=', 'null');
            });
        })->orderBy('created_at', 'DESC')->limit(10)->get();
        
        // ============================================
        // PROCESSING EFFICIENCY
        // ============================================
        
        $totalQuarters = SlaughterDistributionRecord::whereIn('source_address', [
            'Fore-1/4 - Right', 'Fore-1/4 - Left', 
            'Hind-1/4 - Right', 'Hind-1/4 - Left'
        ])->count();
        
        $totalPrimalCuts = SlaughterDistributionRecord::where(function($q) {
            $q->where('source_address', 'like', 'Fore-%')
              ->orWhere('source_address', 'like', 'Hind-%');
        })->whereNotIn('source_address', [
            'Fore-1/4 - Right', 'Fore-1/4 - Left', 
            'Hind-1/4 - Right', 'Hind-1/4 - Left'
        ])->count();
        
        $totalOffal = SlaughterDistributionRecord::where('source_address', 'like', 'Offal%')->count();
        
        $quartersWeight = SlaughterDistributionRecord::whereIn('source_address', [
            'Fore-1/4 - Right', 'Fore-1/4 - Left', 
            'Hind-1/4 - Right', 'Hind-1/4 - Left'
        ])->sum('original_weight') ?? 0;
        
        $primalWeight = SlaughterDistributionRecord::where(function($q) {
            $q->where('source_address', 'like', 'Fore-%')
              ->orWhere('source_address', 'like', 'Hind-%');
        })->whereNotIn('source_address', [
            'Fore-1/4 - Right', 'Fore-1/4 - Left', 
            'Hind-1/4 - Right', 'Hind-1/4 - Left'
        ])->sum('original_weight') ?? 0;
        
        $offalWeight = SlaughterDistributionRecord::where('source_address', 'like', 'Offal%')
            ->sum('original_weight') ?? 0;
        
        $totalProcessedWeight = $quartersWeight + $primalWeight + $offalWeight;
        $utilizationRate = $totalCarcassWeight > 0 ? round(($totalProcessedWeight / $totalCarcassWeight) * 100, 1) : 0;
        
        // ============================================
        // PACKAGING & TRACEABILITY
        // ============================================
        
        $totalPackages = PackagingRecord::count();
        $packagesWeight = PackagingRecord::sum('total_weight') ?? 0;
        $packagesWithBarcode = PackagingRecord::whereNotNull('barcode')
            ->where('barcode', '!=', '')
            ->count();
        $traceabilityRate = $totalPackages > 0 ? round(($packagesWithBarcode / $totalPackages) * 100, 1) : 0;
        
        // ============================================
        // COMPLETION & WORKFLOW
        // ============================================
        
        $completedRecords = SlaughterRecord::where('breed', 'Done')->count();
        $ongoingRecords = SlaughterRecord::where('breed', '!=', 'Done')->count();
        $completionRate = $totalSlaughters > 0 ? round(($completedRecords / $totalSlaughters) * 100, 1) : 0;
        
        $carcassesWithQuarters = SlaughterRecord::whereHas('distributions', function($q) {
            $q->whereIn('source_address', [
                'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                'Hind-1/4 - Right', 'Hind-1/4 - Left'
            ]);
        })->count();
        
        $carcassesWithCuts = SlaughterRecord::whereHas('distributions', function($q) {
            $q->where(function($q2) {
                $q2->where('source_address', 'like', 'Fore-%')
                   ->orWhere('source_address', 'like', 'Hind-%');
            })->whereNotIn('source_address', [
                'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                'Hind-1/4 - Right', 'Hind-1/4 - Left'
            ]);
        })->count();
        
        $carcassesWithPackaging = SlaughterRecord::whereHas('packagingRecords')->count();
        
        // ============================================
        // TREND ANALYSIS (Last 30 days)
        // ============================================
        
        $last30Days = SlaughterRecord::where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
        
        // ============================================
        // TOP FACILITIES BY VOLUME
        // ============================================
        
        $topFacilities = SlaughterRecord::select('destination_slaughter_house', 
            DB::raw('COUNT(*) as total'), 
            DB::raw('SUM(post_weight) as total_weight'))
            ->whereNotNull('destination_slaughter_house')
            ->where('destination_slaughter_house', '!=', '')
            ->groupBy('destination_slaughter_house')
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get();
        
        // ============================================
        // MONTHLY PERFORMANCE COMPARISON
        // ============================================
        
        $currentMonth = Carbon::now()->format('Y-m');
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');
        
        $currentMonthCount = SlaughterRecord::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth])->count();
        $lastMonthCount = SlaughterRecord::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$lastMonth])->count();
        $monthChange = $lastMonthCount > 0 ? round((($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1) : 0;
        
        // ============================================
        // ANIMAL DEMOGRAPHICS
        // ============================================
        
        $maleCount = SlaughterRecord::where('sex', 'Male')->count();
        $femaleCount = SlaughterRecord::where('sex', 'Female')->count();
        $unknownSex = $totalSlaughters - ($maleCount + $femaleCount);
        
        // Age distribution (if available)
        $ageDistribution = SlaughterRecord::select('post_age', DB::raw('COUNT(*) as count'))
            ->whereNotNull('post_age')
            ->where('post_age', '!=', '')
            ->groupBy('post_age')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get();

        return view('admin.butchery-dashboard', compact(
            'totalSlaughters', 'totalCarcassWeight', 'avgCarcassWeight',
            'todayCount', 'weekCount', 'monthCount', 'yearCount',
            'gradeA', 'gradeB', 'gradeC', 'gradeD', 'gradeE', 'notGraded', 'qualityRate',
            'gradeAAvg', 'gradeBAvg', 'gradeCAvg',
            'withAnteFindings', 'withPostFindings', 'clearInspections', 'clearRate', 'recentWithFindings',
            'totalQuarters', 'totalPrimalCuts', 'totalOffal',
            'quartersWeight', 'primalWeight', 'offalWeight', 'utilizationRate',
            'totalPackages', 'packagesWeight', 'packagesWithBarcode', 'traceabilityRate',
            'completedRecords', 'ongoingRecords', 'completionRate',
            'carcassesWithQuarters', 'carcassesWithCuts', 'carcassesWithPackaging',
            'last30Days', 'topFacilities',
            'currentMonthCount', 'lastMonthCount', 'monthChange',
            'maleCount', 'femaleCount', 'unknownSex', 'ageDistribution'
        ));
    }
}

