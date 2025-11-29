<?php

namespace App\Admin\Controllers;

use App\Models\ButcherRecord;
use App\Models\SlaughterDistributionRecord;
use App\Models\SlaughterRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;

class ButcheryDashboardController extends AdminController
{
    public function index(Content $content)
    {
        return $content
            ->title('Butchery Dashboard')
            ->description('Overview of butchery operations')
            ->body($this->dashboard());
    }

    protected function dashboard()
    {
        // Slaughter Statistics
        $totalSlaughters = SlaughterRecord::count();
        $completedSlaughters = SlaughterRecord::where('breed', 'Done')->count();
        $ongoingSlaughters = SlaughterRecord::where('breed', '!=', 'Done')->count();
        $totalCarcassWeight = SlaughterRecord::sum(DB::raw('CAST(post_weight AS DECIMAL(10,2))'));
        $availableCarcassWeight = SlaughterRecord::sum(DB::raw('CAST(available_weight AS DECIMAL(10,2))'));
        
        // Distribution Statistics  
        $totalDistributions = SlaughterDistributionRecord::count();
        $totalDistributedWeight = SlaughterDistributionRecord::sum(DB::raw('CAST(original_weight AS DECIMAL(10,2))'));
        $remainingDistWeight = SlaughterDistributionRecord::sum(DB::raw('CAST(current_weight AS DECIMAL(10,2))'));
        
        // Butcher Records Statistics
        $totalButcherRecords = ButcherRecord::count();
        $soldRecords = ButcherRecord::where('is_sold', 'Yes')->count();
        $availableRecords = ButcherRecord::where('is_sold', 'No')->count();
        
        // Cut Types
        $primeCuts = ButcherRecord::where('cut_type', 'Prime Cut')->count();
        $offalCuts = ButcherRecord::where('cut_type', 'Offal Cut')->count();
        $soldPrimeCuts = ButcherRecord::where('cut_type', 'Prime Cut')->where('is_sold', 'Yes')->count();
        $soldOffalCuts = ButcherRecord::where('cut_type', 'Offal Cut')->where('is_sold', 'Yes')->count();
        
        // Financial Statistics
        $totalRevenue = ButcherRecord::where('is_sold', 'Yes')
            ->sum(DB::raw('CAST(sold_price AS DECIMAL(10,2))'));
        $primeRevenue = ButcherRecord::where('cut_type', 'Prime Cut')
            ->where('is_sold', 'Yes')
            ->sum(DB::raw('CAST(sold_price AS DECIMAL(10,2))'));
        $offalRevenue = ButcherRecord::where('cut_type', 'Offal Cut')
            ->where('is_sold', 'Yes')
            ->sum(DB::raw('CAST(sold_price AS DECIMAL(10,2))'));
        
        // Weight Statistics
        $totalButcherWeight = ButcherRecord::sum(DB::raw('CAST(original_weight AS DECIMAL(10,2))'));
        $soldWeight = ButcherRecord::where('is_sold', 'Yes')
            ->sum(DB::raw('CAST(original_weight AS DECIMAL(10,2)) - CAST(current_weight AS DECIMAL(10,2))'));
        $availableWeight = ButcherRecord::where('is_sold', 'No')
            ->sum(DB::raw('CAST(current_weight AS DECIMAL(10,2))'));
        
        // Recent Records
        $recentSlaughters = SlaughterRecord::orderBy('created_at', 'desc')->limit(10)->get();
        $recentSales = ButcherRecord::where('is_sold', 'Yes')
            ->orderBy('sold_date', 'desc')
            ->limit(5)
            ->get();
        
        // Top Cut Types
        $topPrimeCuts = ButcherRecord::select('prime_cut_type', DB::raw('count(*) as total'))
            ->where('cut_type', 'Prime Cut')
            ->whereNotNull('prime_cut_type')
            ->groupBy('prime_cut_type')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
            
        $topOffalCuts = ButcherRecord::select('offal_cut_type', DB::raw('count(*) as total'))
            ->where('cut_type', 'Offal Cut')
            ->whereNotNull('offal_cut_type')
            ->groupBy('offal_cut_type')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return view('admin.butchery-dashboard', compact(
            'totalSlaughters',
            'completedSlaughters',
            'ongoingSlaughters',
            'totalCarcassWeight',
            'availableCarcassWeight',
            'totalDistributions',
            'totalDistributedWeight',
            'remainingDistWeight',
            'totalButcherRecords',
            'soldRecords',
            'availableRecords',
            'primeCuts',
            'offalCuts',
            'soldPrimeCuts',
            'soldOffalCuts',
            'totalRevenue',
            'primeRevenue',
            'offalRevenue',
            'totalButcherWeight',
            'soldWeight',
            'availableWeight',
            'recentSlaughters',
            'recentSales',
            'topPrimeCuts',
            'topOffalCuts'
        ));
    }
}
