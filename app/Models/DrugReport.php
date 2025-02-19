<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\fileExists;

class DrugReport extends Model
{
    use HasFactory;


    public static function do_process($r)
    {
        //set unlimited time limit 
        $start_date = Carbon::now();
        $end_date = Carbon::now();
        $start_date->subDays(30);
        $end_date->subDays(1);

        switch ($r->period_type) {
            case 'DAILY':
                if ($r->period === 'TODAY') {
                    $start_date = Carbon::today();
                    $end_date = Carbon::today();
                } else {
                    $start_date = Carbon::yesterday();
                    $end_date = Carbon::yesterday();
                }
                break;

            case 'WEEKLY':
                if ($r->period === 'THIS_WEEK') {
                    $start_date = Carbon::now()->startOfWeek();
                    $end_date = Carbon::now()->endOfWeek();
                } else {
                    $start_date = Carbon::now()->subWeek()->startOfWeek();
                    $end_date = Carbon::now()->subWeek()->endOfWeek();
                }
                break;

            case 'MONTHLY':
                if ($r->period === 'THIS_MONTH') {
                    $start_date = Carbon::now()->firstOfMonth();
                    $end_date = Carbon::now()->lastOfMonth();
                } else {
                    $start_date = Carbon::now()->subMonth()->firstOfMonth();
                    $end_date = Carbon::now()->subMonth()->lastOfMonth();
                }
                break;

            case 'QUARTERLY':
                if ($r->period === 'THIS_QUARTER') {
                    $start_date = Carbon::now()->firstOfQuarter();
                    $end_date = Carbon::now()->lastOfQuarter();
                } else {
                    $start_date = Carbon::now()->subQuarter()->firstOfQuarter();
                    $end_date = Carbon::now()->subQuarter()->lastOfQuarter();
                }
                break;

            case 'YEARLY':
                if ($r->period === 'THIS_YEAR') {
                    $start_date = Carbon::now()->startOfYear();
                    $end_date = Carbon::now()->endOfYear();
                } else {
                    $start_date = Carbon::now()->subYear()->startOfYear();
                    $end_date = Carbon::now()->subYear()->endOfYear();
                }
                break;

            case 'CUSTOM':
                $start_date = Carbon::parse($r->start_date)->startOfDay();
                $end_date = Carbon::parse($r->end_date)->endOfDay();
                break;
        }

        $r->start_date = $start_date;
        $r->end_date = $end_date;

        $farm = Farm::find($r->farm_id);
        if ($farm == null) {
            throw new \Exception("Farm not found");
        }
        $animals = Animal::where(
            [
                'farm_id' => $r->farm_id
            ]
        )->get();
        $animal_ids = $animals->pluck('id')->toArray();

        $data['total_animals'] = $animals->count();
        $data['total_goats'] = $animals->where('type', 'Goat')->count();
        $data['total_sheep'] = $animals->where('type', 'Sheep')->count();
        $data['total_cattle'] = $animals->where('type', 'Cattle')->count();
        $treatment_events_1 = Event::where([
            'type' => 'Treatment'
        ])->whereBetween('created_at', [$start_date, $end_date])
            ->whereIn('animal_id', $animal_ids)->get();

        $treatment_events_2 = Event::where([
            'type' => 'Batch Treatment'
        ])->whereBetween('created_at', [$start_date, $end_date])
            ->whereIn('animal_id', $animal_ids)->get();

        //merge 
        $treatment_events = $treatment_events_1->merge($treatment_events_2);
        $data['total_amount_spent'] = $treatment_events->sum('drug_worth');
        $owner_id = $farm->administrator_id;

        $drugs = DrugStockBatch::where([
            'administrator_id' => $owner_id
        ])->get();
        $unique_drug_category_ids = $drugs->pluck('drug_category_id')->unique()->toArray();

        $cats = DrugCategory::whereIn('id', $unique_drug_category_ids)->get();
        $drugs_in_stock = [];
        $drugs_running_out = [];
        $drugs_out_of_stock = [];
        $total_amount_invested = [];
        foreach ($cats as $cat) {
            $total_avergae = $drugs->where('drug_category_id', $cat->id)->avg('original_quantity');
            $current_quantity = $drugs->where('drug_category_id', $cat->id)->sum('current_quantity');
            $total_amount_invested[$cat->id] = DrugStockBatch::where([
                'drug_category_id' =>  $cat->id,
                'administrator_id' => $owner_id
            ])
                ->whereBetween('created_at', [$start_date, $end_date])
                ->sum('selling_price');
            $percentage = 0;
            if ($total_avergae > 0) {
                $percentage = ($current_quantity / $total_avergae) * 100;
                $percentage = round($percentage, 2);
            }
            $item = [];
            $item['category'] = $cat->name;
            $item['category_id'] = $cat->id;
            $item['total_avergae'] = $total_avergae;
            $item['current_quantity'] = $current_quantity;
            $item['percentage'] = $percentage;
            $item['unit'] = $cat->unit;
            if ($percentage > 30) {
                $drugs_in_stock[] = $item;
            } else if ($percentage > 0) {
                $drugs_running_out[] = $item;
            } else {
                $drugs_out_of_stock[] = $item;
            }
        }
        $data['drugs_in_stock'] = $drugs_in_stock;
        $data['drugs_running_out'] = $drugs_running_out;
        $data['drugs_out_of_stock'] = $drugs_out_of_stock;
        $data['most_invested_drugs'] = [];
        //sort $total_amount_invested
        arsort($total_amount_invested);

        foreach ($total_amount_invested as $key => $value) {
            //if zero skip
            if ($value == 0) {
                continue;
            }
            $drug = DrugCategory::find($key);
            $item = [];
            $item['category'] = $drug->name;
            $item['category_id'] = $drug->id;
            $item['total_amount_invested'] = $value;
            $data['most_invested_drugs'][] = $item;
        }
        arsort($total_amount_invested);
        //recent purchases
        $data['recent_purchases'] = DrugStockBatch::where([
            'administrator_id' => $owner_id
        ])
            ->whereBetween('created_at', [$start_date, $end_date])
            ->orderBy('created_at', 'DESC')
            ->get();

        $most_teated_animal_ids = [];
        $treatmentByDrugWorth = Event::select('animal_id', DB::raw('SUM(drug_worth) as total_worth'))
            ->whereBetween('created_at', [$start_date, $end_date])
            ->whereIn('animal_id', $animal_ids)
            ->groupBy('animal_id')
            ->orderByDesc('total_worth')
            ->limit(10)
            ->pluck('animal_id')
            ->toArray();

        $most_teated_animal_ids = $treatmentByDrugWorth;
        $most_teated_animals = [];
        foreach ($most_teated_animal_ids as $id) {
            $animal = Animal::find($id);
            $total_amount = Event::where([
                'animal_id' => $id
            ])->whereBetween('created_at', [$start_date, $end_date])->sum('drug_worth');
            $item['total_amount'] = $total_amount;
            $item['animal'] = $animal;
            $most_teated_animals[] = $item;
        }
        $data['most_teated_animals'] = $most_teated_animals;


        $r->title = "Drug Stock Report for the period " . Utils::my_date($start_date) . " to " . Utils::my_date($end_date);


        $pdf = App::make('dompdf.wrapper');
        $template_file = 'drugs-report-2';

        if ($r->design == 'design_1') {
            $template_file = 'drugs-report';
        } else if ($r->design == 'design_2') {
            $template_file = 'drugs-report-2';
        } else {
            $template_file = 'drugs-report-2';
        }
        $pdf->loadHTML(view($template_file, [
            'report' => $r,
            'data' => $data
        ]));

        //check if file pdf 
        if ($r->pdf_path != null) {
            if (fileExists(Utils::docs_root() . "/storage/" . $r->pdf_path)) {
                try {
                    unlink(Utils::docs_root() . "/storage/" . $r->pdf_path);
                } catch (\Throwable $th) {
                }
            }
        }

        $name  = "files/" . Utils::get_unique_text() . ".pdf";
        $source = Utils::docs_root() . "/storage/" . $name;
        $pdf->save($source);
        $r->pdf_path = $name;
        $r->pdf_generated = 'Yes';
        $r->data = json_encode($data);
        // $path = Utils::docs_root() . "/storage/" . $name;
        // $sql = "UPDATE farm_reports SET pdf = '$name', pdf_prepared = 'Yes', pdf_prepare_date = NOW() WHERE id = $r->id";
        $r->save();
        return $r;
    }

    //belongs to farm 
    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
