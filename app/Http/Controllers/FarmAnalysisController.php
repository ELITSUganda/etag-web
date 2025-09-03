<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Farm;
use App\Models\Animal;
use App\Models\Event;
use App\Models\ArchivedAnimal;
use App\Models\Utils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * FarmAnalysisController - Comprehensive Farm KPI Calculator
 * 
 * KEY CALCULATION METHODS:
 * - Deaths/Mortality: Calculated from archived_animals where last_event = 'Mortality'
 * - Lactating Cows: Count unique animal_ids from milking events in date range
 * - Data Quality: Checks cattle only, validates null/length < 4 for key fields
 * - Treatment Cost: Uses drug_worth column, includes both Treatment & Batch Treatment
 * - Milk Pricing: Uses price column from milking events (not static)
 * - Number Formatting: xxx,xxx,xxx,xxx.xx format with 2 decimal precision
 * - All divisions rounded to 2 decimal places for consistency
 * 
 * EVENTS TABLE STRUCTURE UTILIZED:
 * - type: Event category (Milking, Treatment, Batch Treatment, etc.)
 * - milk: Milk quantity for milking events
 * - price: Event value/price (used for milk sales)
 * - drug_worth: Treatment cost for treatment events
 * - animal_id: Links events to specific animals
 * - created_at: Event timestamp for date range filtering
 */

class FarmAnalysisController extends Controller
{
    /**
     * Format numeric values with thousand separators and 2 decimal places
     * Format: xxx,xxx,xxx,xxx.xx
     */
    private function formatNumber($number)
    {
        if (!is_numeric($number)) {
            return '0.00';
        }
        return number_format((float)$number, 2, '.', ',');
    }

    /**
     * Endpoint to get farm analysis data
     */
    public function farm_analysis(Request $request)
    {
        // Validate the input request
        $validated = $request->validate([
            'farm_id' => 'required|integer',
            'range_from' => 'nullable|date',
            'range_to' => 'nullable|date',
        ]);

        // Set default date range if not provided and ensure Carbon instances
        $rangeFrom = isset($validated['range_from']) ? Carbon::parse($validated['range_from'])->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $rangeTo = isset($validated['range_to']) ? Carbon::parse($validated['range_to'])->endOfDay() : Carbon::now()->endOfDay();

        $days_difference = $rangeFrom->diffInDays($rangeTo);

        // Previous period comparison
        $prevRangeFrom = $rangeFrom->copy()->subDays($days_difference + 1);
        $prevRangeTo = $rangeTo->copy()->subDays($days_difference + 1);

        // Fetch farm details
        $farm = Farm::find($validated['farm_id']);
        if (!$farm) {
            return Utils::response([
                'status' => 0,
                'data' => [],
                'message' => 'Farm not found'
            ]);
        }

        //set all events type with Death to Mortality
        Event::where('type', 'Death')
            ->update(['type' => 'Mortality']);
        ArchivedAnimal::where('type', 'Death')
            ->update(['last_event' => 'Mortality']);

        // Get all data in bulk to minimize DB queries
        $animals = $this->getFarmAnimals($farm->id);
        $events = $this->getFarmEvents($validated['farm_id'], $rangeFrom, $rangeTo);

        // Get archived animals for current and previous periods
        $archivedAnimals = $this->getArchivedAnimals($validated['farm_id'], $rangeFrom, $rangeTo);
        $prevArchivedAnimals = $this->getArchivedAnimals($validated['farm_id'], $prevRangeFrom, $prevRangeTo);

        // Get previous period data
        $prevEvents = $this->getFarmEvents($validated['farm_id'], $prevRangeFrom, $prevRangeTo);

        // Calculate KPIs with comparison data
        $kpis = $this->compute_kpis($animals, $events, $prevEvents, $archivedAnimals, $prevArchivedAnimals, $rangeFrom, $rangeTo, $prevRangeFrom, $prevRangeTo);

        // Add enhanced dashboard items
        $kpis['dashboard_items'] = $this->generateDashboardItems($kpis, $animals, $events);

        $kpis['farm_id'] = $farm->id;
        $kpis['as_of'] = Carbon::now()->toIso8601String();
        $kpis['range_from'] = $rangeFrom->toIso8601String();
        $kpis['range_to'] = $rangeTo->toIso8601String();
        $kpis['prev_range_from'] = $prevRangeFrom->toIso8601String();
        $kpis['prev_range_to'] = $prevRangeTo->toIso8601String();


        return Utils::response([
            'status' => 1,
            'data' => [[
                'id' => 1,
                'last_update' => Carbon::now()->toIso8601String(),
                'data' => json_encode($kpis)
            ]],
            'message' => 'Success'
        ]);
    }

    /**
     * Get all animals for the farm
     */
    private function getFarmAnimals($farmId)
    {
        return Animal::where('farm_id', $farmId)
            ->get();
    }

    /**
     * Get events for the farm within date range
     */
    private function getFarmEvents($farmId, $from, $to)
    {
        return DB::table('events')
            ->where('farm_id', $farmId)
            ->whereBetween('created_at', [$from, $to])
            ->get();
    }

    /**
     * Get archived animals for the farm
     */
    private function getArchivedAnimals($farmId, $from = null, $to = null)
    {
        $farm = Farm::find($farmId);
        $administrator_id = null;
        if ($farm != null) {
            $administrator_id = $farm->administrator_id;
        }

        $query = DB::table('archived_animals')->where('administrator_id', $administrator_id);

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return $query->get();
    }

    /**
     * Compute all KPIs from the provided data with comparison to previous period
     */
    private function compute_kpis($animals, $events, $prevEvents, $archivedAnimals, $prevArchivedAnimals, $from, $to, $prevFrom, $prevTo)
    {
        return [
            // Herd Overview - current snapshot without comparison
            'herd_overview' => $this->computeHerdOverview($animals, $events, $prevEvents, $archivedAnimals, $prevArchivedAnimals, $from, $to, $prevFrom, $prevTo),

            // Animal Structure & Demographics - current snapshot without comparison
            'animal_demographics' => $this->computeAnimalDemographics($animals, $events),

            // Registration & Data Completeness - current snapshot without comparison
            'data_completeness' => $this->computeDataCompleteness($animals),

            // Reproduction & Fertility - event-based with comparison
            'reproduction_fertility' => $this->computeReproductionFertility($events, $prevEvents),

            // Health & Disease - event-based with comparison (but mortality from archived)
            'health_disease' => $this->computeHealthDisease($events, $prevEvents, $animals, $archivedAnimals, $prevArchivedAnimals),

            // Milk Production - event-based with comparison
            'milk_production' => $this->computeMilkProduction($events, $prevEvents, $animals),

            // Treatment Metrics - event-based with comparison
            'treatment_metrics' => $this->computeTreatmentMetrics($events, $prevEvents, $animals),

            // Financial Metrics - event-based with comparison
            'financial_metrics' => $this->computeFinancialMetrics($events, $prevEvents, $animals),
        ];
    }

    /**
     * Compute herd overview KPIs
     */
    private function computeHerdOverview($animals, $events, $prevEvents, $archivedAnimals, $prevArchivedAnimals, $from, $to, $prevFrom, $prevTo)
    {
        $totalAnimals = $animals->count();

        // Count by species
        $speciesCount = $animals->groupBy('type')->map->count();

        // Archived animals (with comparison) - all archived animals in period
        $archivedCount = $archivedAnimals->count();
        $prevArchivedCount = $prevArchivedAnimals->count();

        // Youngstock (calves < 1 year, cattle only)
        $youngstockCount = $animals->where('type', 'Cattle')
            ->filter(function ($animal) {
                if ($animal->dob == null || strlen($animal->dob) < 4) {
                    return false;
                }
                return $this->calculateAgeInMonths($animal->dob) < 12;
            })->count();

        // Deaths from archived animals where last_event = 'Mortality'
        $deaths = $archivedAnimals->where('last_event', 'Mortality')->count();
        $prevDeaths = $prevArchivedAnimals->where('last_event', 'Mortality')->count();

        // Births from events (still from events as births don't cause archiving)
        $births = $this->getEventCount($events, 'Calving');
        $prevBirths = $this->getEventCount($prevEvents, 'Calving');

        // Herd growth (births - deaths) with comparison
        $herdGrowth = $births - $deaths;
        $prevHerdGrowth = $prevBirths - $prevDeaths;

        // Data quality score
        $dataQualityScore = $this->calculateDataQualityScore($animals);

        return [
            'total_animals' => $totalAnimals,
            'species_count' => $speciesCount->isEmpty() ? (object)[] : $speciesCount,
            'archived_animals' => $this->compareValues($archivedCount, $prevArchivedCount, true),
            'youngstock_count' => $youngstockCount,
            'herd_growth' => $this->compareValues($herdGrowth, $prevHerdGrowth),
            'data_quality_score' => $dataQualityScore,
            'births' => $this->compareValues($births, $prevBirths),
            'deaths' => $this->compareValues($deaths, $prevDeaths, true),
        ];
    }

    /**
     * Compute animal demographics (cattle only)
     * Lactating cows calculated from unique animal_ids with milking events in date range
     */
    private function computeAnimalDemographics($animals, $events)
    {
        // For cattle only
        $cattle = $animals->where('type', 'Cattle');

        // Count by breed
        $breedCount = $cattle->groupBy('breed')->map->count();

        // Count by sex
        $sexCount = $cattle->groupBy('sex')->map->count();

        // Age bands (0-6m, 6-12m, 12-24m, >24m)
        $ageBands = [
            '0_6m' => 0,
            '6_12m' => 0,
            '12_24m' => 0,
            '24m_plus' => 0,
        ];

        foreach ($cattle as $animal) {
            $ageMonths = $this->calculateAgeInMonths($animal->dob);

            if ($ageMonths <= 6) {
                $ageBands['0_6m']++;
            } elseif ($ageMonths <= 12) {
                $ageBands['6_12m']++;
            } elseif ($ageMonths <= 24) {
                $ageBands['12_24m']++;
            } else {
                $ageBands['24m_plus']++;
            }
        }

        // Lactating cows: Count unique animal_ids from milking events in date range
        $lactatingCows = $events->where('type', 'Milking')
                               ->pluck('animal_id')
                               ->unique()
                               ->count();

        // Pregnant cows (keep existing logic)
        $pregnantCows = $cattle->where('sex', 'Female')
            ->where('is_pregnant', 'Yes')->count();

        return [
            'breed_count' => $breedCount->isEmpty() ? (object)[] : $breedCount,
            'sex_count' => $sexCount->isEmpty() ? (object)[] : $sexCount,
            'age_bands' => $ageBands,
            'lactating_cows' => $lactatingCows,
            'pregnant_cows' => $pregnantCows,
            'total_cattle' => $cattle->count(),
        ];
    }

    /**
     * Compute data completeness metrics for cattle only
     * Checks for null values or string length < 4 for key fields
     */
    private function computeDataCompleteness($animals)
    {
        // Filter to cattle only as per requirements
        $cattle = $animals->where('type', 'Cattle');
        $totalCattle = $cattle->count();

        if ($totalCattle === 0) {
            return [
                'missing_photo' => 0,
                'missing_eid' => 0,
                'missing_vid' => 0,
                'missing_species' => 0,
                'missing_sex' => 0,
                'missing_dob' => 0,
                'completeness_score' => 0.00,
            ];
        }

        // Count missing fields (null or string length < 4)
        $missingPhoto = $cattle->filter(function($animal) {
            return is_null($animal->photo) || strlen(trim($animal->photo ?? '')) < 4;
        })->count();

        $missingEid = $cattle->filter(function($animal) {
            return is_null($animal->e_id) || strlen(trim($animal->e_id ?? '')) < 4;
        })->count();

        $missingVid = $cattle->filter(function($animal) {
            return is_null($animal->v_id) || strlen(trim($animal->v_id ?? '')) < 4;
        })->count();

        $missingSpecies = $cattle->filter(function($animal) {
            return is_null($animal->type) || strlen(trim($animal->type ?? '')) < 4;
        })->count();

        $missingSex = $cattle->filter(function($animal) {
            return is_null($animal->sex) || strlen(trim($animal->sex ?? '')) < 4;
        })->count();

        $missingDob = $cattle->filter(function($animal) {
            return is_null($animal->dob) || strlen(trim($animal->dob ?? '')) < 4;
        })->count();

        // Calculate completeness score (round to 2 decimal places)
        $completeRecords = $cattle->filter(function($animal) {
            return !is_null($animal->photo) && strlen(trim($animal->photo ?? '')) >= 4 &&
                   !is_null($animal->e_id) && strlen(trim($animal->e_id ?? '')) >= 4 &&
                   !is_null($animal->v_id) && strlen(trim($animal->v_id ?? '')) >= 4 &&
                   !is_null($animal->type) && strlen(trim($animal->type ?? '')) >= 4 &&
                   !is_null($animal->sex) && strlen(trim($animal->sex ?? '')) >= 4 &&
                   !is_null($animal->dob) && strlen(trim($animal->dob ?? '')) >= 4;
        })->count();

        $completenessScore = round(($completeRecords / $totalCattle) * 100, 2);

        return [
            'missing_photo' => $missingPhoto,
            'missing_eid' => $missingEid,
            'missing_vid' => $missingVid,
            'missing_species' => $missingSpecies,
            'missing_sex' => $missingSex,
            'missing_dob' => $missingDob,
            'completeness_score' => $completenessScore,
        ];
    }

    /**
     * Compute reproduction and fertility metrics with comparison
     * All percentages and ratios rounded to 2 decimal places
     */
    private function computeReproductionFertility($events, $prevEvents)
    {
        // Services in period (AI vs Natural)
        $services = $events->where('type', 'Service');
        $aiServices = $services->where('service_type', 'Artificial insemination')->count();
        $naturalServices = $services->where('service_type', 'Natural service')->count();

        $prevServices = $prevEvents->where('type', 'Service');
        $prevAiServices = $prevServices->where('service_type', 'Artificial insemination')->count();
        $prevNaturalServices = $prevServices->where('service_type', 'Natural service')->count();

        // Pregnancy checks
        $pregnancyChecks = $events->where('type', 'Pregnancy check');
        $pregnant = $pregnancyChecks->where('is_present', 'Pregnant')->count();
        $notPregnant = $pregnancyChecks->where('is_present', 'Not Pregnant')->count();
        $retest = $pregnancyChecks->where('is_present', 'Retest')->count();

        $prevPregnancyChecks = $prevEvents->where('type', 'Pregnancy check');
        $prevPregnant = $prevPregnancyChecks->where('is_present', 'Pregnant')->count();
        $prevNotPregnant = $prevPregnancyChecks->where('is_present', 'Not Pregnant')->count();
        $prevRetest = $prevPregnancyChecks->where('is_present', 'Retest')->count();

        // Conception rate - round to 2 decimal places
        $conceptionRate = $services->count() > 0 ? round(($pregnant / $services->count()) * 100, 2) : 0.00;
        $prevConceptionRate = $prevServices->count() > 0 ? round(($prevPregnant / $prevServices->count()) * 100, 2) : 0.00;

        // Calvings in period
        $calvings = $events->where('type', 'Calving')->count();
        $prevCalvings = $prevEvents->where('type', 'Calving')->count();

        // Abortions in period
        $abortions = $events->where('type', 'Abortion')->count();
        $prevAbortions = $prevEvents->where('type', 'Abortion')->count();

        return [
            'services' => [
                'ai' => $this->compareValues($aiServices, $prevAiServices),
                'natural' => $this->compareValues($naturalServices, $prevNaturalServices),
                'total' => $this->compareValues($services->count(), $prevServices->count()),
            ],
            'pregnancy_checks' => [
                'pregnant' => $this->compareValues($pregnant, $prevPregnant),
                'not_pregnant' => $this->compareValues($notPregnant, $prevNotPregnant, true),
                'retest' => $this->compareValues($retest, $prevRetest, true),
                'total' => $this->compareValues($pregnancyChecks->count(), $prevPregnancyChecks->count()),
            ],
            'conception_rate' => $this->compareValues($conceptionRate, $prevConceptionRate),
            'calvings' => $this->compareValues($calvings, $prevCalvings),
            'abortions' => $this->compareValues($abortions, $prevAbortions, true),
        ];
    }

    /**
     * Compute health and disease metrics with comparison
     * Treatment events include both 'Treatment' and 'Batch Treatment'
     * Mortality calculated from archived animals with last_event = 'Mortality'
     */
    private function computeHealthDisease($events, $prevEvents, $animals, $archivedAnimals, $prevArchivedAnimals)
    {
        // Disease events
        $diseaseEvents = $events->where('type', 'Disease test');
        $diseaseCount = $diseaseEvents->count();
        $prevDiseaseCount = $prevEvents->where('type', 'Disease test')->count();

        // Treatment events (both Treatment and Batch Treatment)
        $treatmentEvents = $events->whereIn('type', ['Treatment', 'Batch Treatment']);
        $treatmentCount = $treatmentEvents->count();
        $prevTreatmentCount = $prevEvents->whereIn('type', ['Treatment', 'Batch Treatment'])->count();

        // Vaccination events
        $vaccinationEvents = $events->where('type', 'Vaccination');
        $vaccinationCount = $vaccinationEvents->count();
        $prevVaccinationCount = $prevEvents->where('type', 'Vaccination')->count();

        // Mortality from archived animals where last_event = 'Mortality'
        $mortalityCount = $archivedAnimals->where('last_event', 'Mortality')->count();
        $prevMortalityCount = $prevArchivedAnimals->where('last_event', 'Mortality')->count();

        // Disease incidence rate (round to 2 decimal places)
        $avgHeadcount = $animals->count();
        $diseaseIncidenceRate = $avgHeadcount > 0 ? round(($diseaseCount / $avgHeadcount) * 100, 2) : 0.00;

        $prevAvgHeadcount = $this->getFarmAnimalsAtDate($animals, $prevEvents->last()->created_at ?? Carbon::now()->subYear())->count();
        $prevDiseaseIncidenceRate = $prevAvgHeadcount > 0 ? round(($prevDiseaseCount / $prevAvgHeadcount) * 100, 2) : 0.00;

        return [
            'disease_events' => $this->compareValues($diseaseCount, $prevDiseaseCount, true),
            'treatment_events' => $this->compareValues($treatmentCount, $prevTreatmentCount, true),
            'vaccination_events' => $this->compareValues($vaccinationCount, $prevVaccinationCount),
            'mortality_events' => $this->compareValues($mortalityCount, $prevMortalityCount, true),
            'disease_incidence_rate' => $this->compareValues($diseaseIncidenceRate, $prevDiseaseIncidenceRate, true),
        ];
    }

    /**
     * Compute milk production metrics with comparison
     * Uses milk quantity from 'milk' column and price from 'price' column
     */
    private function computeMilkProduction($events, $prevEvents, $animals)
    {
        // Milking events - use 'milk' column for quantity and 'price' column for pricing
        $milkingEvents = $events->where('type', 'Milking');
        $totalMilk = 0;
        foreach ($milkingEvents as $event) {
            // Use milk column directly, fallback to extracting from detail if needed
            $milk = (float)($event->milk ?? $this->extractMilkQuantity($event->detail ?? '0'));
            $totalMilk += $milk;
        }
        $avgMilkPerEvent = $milkingEvents->count() > 0 ? round($totalMilk / $milkingEvents->count(), 2) : 0.00;

        $prevMilkingEvents = $prevEvents->where('type', 'Milking');
        $prevTotalMilk = 0;
        foreach ($prevMilkingEvents as $event) {
            $milk = (float)($event->milk ?? $this->extractMilkQuantity($event->detail ?? '0'));
            $prevTotalMilk += $milk;
        }
        $prevAvgMilkPerEvent = $prevMilkingEvents->count() > 0 ? round($prevTotalMilk / $prevMilkingEvents->count(), 2) : 0.00;

        // Lactating animals: Count unique animal_ids from milking events in date range
        $lactatingAnimals = $milkingEvents->pluck('animal_id')->unique()->count();
        $avgMilkPerLactatingAnimal = $lactatingAnimals > 0 ? round($totalMilk / $lactatingAnimals, 2) : 0.00;

        $prevLactatingAnimals = $prevMilkingEvents->pluck('animal_id')->unique()->count();
        $prevAvgMilkPerLactatingAnimal = $prevLactatingAnimals > 0 ? round($prevTotalMilk / $prevLactatingAnimals, 2) : 0.00;

        return [
            'total_milk_production' => $this->compareValues(round($totalMilk, 2), round($prevTotalMilk, 2)),
            'milking_events' => $this->compareValues($milkingEvents->count(), $prevMilkingEvents->count()),
            'avg_milk_per_event' => $this->compareValues($avgMilkPerEvent, $prevAvgMilkPerEvent),
            'lactating_animals' => $this->compareValues($lactatingAnimals, $prevLactatingAnimals),
            'avg_milk_per_lactating_animal' => $this->compareValues($avgMilkPerLactatingAnimal, $prevAvgMilkPerLactatingAnimal),
        ];
    }

    /**
     * Compute treatment metrics with comparison
     * Includes both 'Treatment' and 'Batch Treatment' events
     * Uses drug_worth column for cost calculations
     */
    private function computeTreatmentMetrics($events, $prevEvents, $animals)
    {
        // Treatment events (both Treatment and Batch Treatment)
        $treatmentEvents = $events->whereIn('type', ['Treatment', 'Batch Treatment']);
        $treatmentCount = $treatmentEvents->count();
        $prevTreatmentCount = $prevEvents->whereIn('type', ['Treatment', 'Batch Treatment'])->count();

        // Treatment cost from drug_worth column
        $treatmentCost = 0;
        foreach ($treatmentEvents as $event) {
            $cost = (float)($event->drug_worth ?? 0);
            $treatmentCost += $cost;
        }

        $prevTreatmentCost = 0;
        $prevTreatmentEvents = $prevEvents->whereIn('type', ['Treatment', 'Batch Treatment']);
        foreach ($prevTreatmentEvents as $event) {
            $cost = (float)($event->drug_worth ?? 0);
            $prevTreatmentCost += $cost;
        }

        // Average treatment cost (round to 2 decimal places)
        $avgTreatmentCost = $treatmentCount > 0 ? round($treatmentCost / $treatmentCount, 2) : 0.00;
        $prevAvgTreatmentCost = $prevTreatmentCount > 0 ? round($prevTreatmentCost / $prevTreatmentCount, 2) : 0.00;

        // Treatment rate (treatments per animal) - round to 2 decimal places
        $animalCount = $animals->count();
        $treatmentRate = $animalCount > 0 ? round($treatmentCount / $animalCount, 2) : 0.00;

        $prevAnimalCount = $this->getFarmAnimalsAtDate($animals, $prevEvents->last()->created_at ?? Carbon::now()->subYear())->count();
        $prevTreatmentRate = $prevAnimalCount > 0 ? round($prevTreatmentCount / $prevAnimalCount, 2) : 0.00;

        return [
            'treatment_count' => $this->compareValues($treatmentCount, $prevTreatmentCount, true),
            'treatment_cost' => $this->compareValues(round($treatmentCost, 2), round($prevTreatmentCost, 2), true),
            'avg_treatment_cost' => $this->compareValues($avgTreatmentCost, $prevAvgTreatmentCost, true),
            'treatment_rate' => $this->compareValues($treatmentRate, $prevTreatmentRate, true),
        ];
    }

    /**
     * Compute financial metrics with comparison
     * Uses price column from milking events and drug_worth from treatment events
     */
    private function computeFinancialMetrics($events, $prevEvents, $animals)
    {
        // Milk value - use price field from events table directly
        $milkValue = 0;
        $milkingEvents = $events->where('type', 'Milking');
        foreach ($milkingEvents as $event) {
            $price = (float)($event->price ?? 0);
            $milkValue += $price;
        }

        $prevMilkValue = 0;
        $prevMilkingEvents = $prevEvents->where('type', 'Milking');
        foreach ($prevMilkingEvents as $event) {
            $price = (float)($event->price ?? 0);
            $prevMilkValue += $price;
        }

        // Treatment cost from drug_worth column (both Treatment and Batch Treatment)
        $treatmentCost = 0;
        $treatmentEvents = $events->whereIn('type', ['Treatment', 'Batch Treatment']);
        foreach ($treatmentEvents as $event) {
            $cost = (float)($event->drug_worth ?? 0);
            $treatmentCost += $cost;
        }

        $prevTreatmentCost = 0;
        $prevTreatmentEvents = $prevEvents->whereIn('type', ['Treatment', 'Batch Treatment']);
        foreach ($prevTreatmentEvents as $event) {
            $cost = (float)($event->drug_worth ?? 0);
            $prevTreatmentCost += $cost;
        }

        // Net profit (milk value - treatment cost) - round to 2 decimal places
        $netProfit = round($milkValue - $treatmentCost, 2);
        $prevNetProfit = round($prevMilkValue - $prevTreatmentCost, 2);

        // Profit per animal - round to 2 decimal places
        $animalCount = $animals->count();
        $profitPerAnimal = $animalCount > 0 ? round($netProfit / $animalCount, 2) : 0.00;

        $prevAnimalCount = $this->getFarmAnimalsAtDate($animals, $prevEvents->last()->created_at ?? Carbon::now()->subYear())->count();
        $prevProfitPerAnimal = $prevAnimalCount > 0 ? round($prevNetProfit / $prevAnimalCount, 2) : 0.00;

        return [
            'milk_value' => $this->compareValues(round($milkValue, 2), round($prevMilkValue, 2)),
            'treatment_cost' => $this->compareValues(round($treatmentCost, 2), round($prevTreatmentCost, 2), true),
            'net_profit' => $this->compareValues($netProfit, $prevNetProfit),
            'profit_per_animal' => $this->compareValues($profitPerAnimal, $prevProfitPerAnimal),
        ];
    }

    /**
     * Calculate age in months from date of birth
     */
    private function calculateAgeInMonths($dob)
    {
        if (empty($dob)) return 0;

        try {
            $birthDate = Carbon::parse($dob);
            $currentDate = Carbon::now();

            return $currentDate->diffInMonths($birthDate);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get count of specific event type
     */
    private function getEventCount($events, $eventType)
    {
        return $events->where('type', $eventType)->count();
    }

    /**
     * Calculate data quality score for cattle only
     * Checks for null values or string length < 4 for key fields
     * Returns percentage rounded to 2 decimal places
     */
    private function calculateDataQualityScore($animals)
    {
        // Filter to cattle only
        $cattle = $animals->where('type', 'Cattle');
        $totalCattle = $cattle->count();
        
        if ($totalCattle === 0) return 0.00;

        $completeRecords = $cattle->filter(function ($animal) {
            return !is_null($animal->photo) && strlen(trim($animal->photo ?? '')) >= 4 &&
                   !is_null($animal->e_id) && strlen(trim($animal->e_id ?? '')) >= 4 &&
                   !is_null($animal->v_id) && strlen(trim($animal->v_id ?? '')) >= 4 &&
                   !is_null($animal->type) && strlen(trim($animal->type ?? '')) >= 4 &&
                   !is_null($animal->sex) && strlen(trim($animal->sex ?? '')) >= 4 &&
                   !is_null($animal->dob) && strlen(trim($animal->dob ?? '')) >= 4;
        })->count();

        return round(($completeRecords / $totalCattle) * 100, 2);
    }

    /**
     * Get farm animals at a specific date
     */
    private function getFarmAnimalsAtDate($animals, $date)
    {
        return $animals->filter(function ($animal) use ($date) {
            try {
                return Carbon::parse($animal->created_at)->lte(Carbon::parse($date));
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    /**
     * Compare two values and return with status
     */
    private function compareValues($current, $previous, $invert = false)
    {
        // Handle cases where previous data might not be available
        if (!is_numeric($previous)) {
            $previous = 0;
        }

        if (!is_numeric($current)) {
            $current = 0;
        }

        $diff = $current - $previous;

        if ($diff > 0) {
            $status = $invert ? 'bad' : 'good';
        } elseif ($diff < 0) {
            $status = $invert ? 'good' : 'bad';
        } else {
            $status = 'fair';
        }

        return [
            'value' => $current,
            'prev_value' => $previous,
            'diff' => $diff,
            'status' => $status
        ];
    }

    /**
     * Extract milk quantity from event detail field
     */
    private function extractMilkQuantity($detail)
    {
        // Try to extract number from detail field
        // Common formats: "10 liters", "10L", "10", "Milked 10 liters"
        preg_match('/(\d+(?:\.\d+)?)\s*(?:liters?|l|L)?/', $detail, $matches);
        return isset($matches[1]) ? floatval($matches[1]) : 0;
    }

    /**
     * Extract treatment cost from event detail field
     */
    private function extractTreatmentCost($detail, $priceField = 0)
    {
        // First try to use price field if available
        if ($priceField > 0) {
            return floatval($priceField);
        }

        // Extract cost from detail field
        // Common formats: "Cost: 5000", "$50", "UGX 5000"
        preg_match('/(?:cost|price|worth)[:\s]*(?:ugx|usd|[sUGX$])\s*(\d+(?:\.\d+)?)/i', $detail, $matches);
        if (isset($matches[1])) {
            return floatval($matches[1]);
        }

        // Try to extract any number as fallback
        preg_match('/(\d+(?:\.\d+)?)/', $detail, $matches);
        return isset($matches[1]) ? floatval($matches[1]) : 0;
    }

    /**
     * Generate enhanced dashboard items for mobile app
     * Uses formatted numbers and correct data sources
     */
    private function generateDashboardItems($kpis, $animals, $events)
    {
        $items = [];

        // Total Animals (High Priority)
        $speciesBreakdown = $this->buildSpeciesBreakdown($kpis['herd_overview']['species_count'] ?? []);
        $items[] = [
            'id' => 'total_animals',
            'name' => 'Total Animals',
            'count' => $this->formatNumber($kpis['herd_overview']['total_animals'] ?? 0),
            'description' => $speciesBreakdown,
            'icon' => 'pets',
            'type' => 'total_animals',
            'is_clickable' => true,
            'route' => '/animals',
            'is_high_priority' => true,
            'trend' => null,
            'trend_value' => null,
        ];

        // Cattle (High Priority)
        $genderBreakdown = $this->buildGenderBreakdown($kpis['animal_demographics']['sex_count'] ?? []);
        $items[] = [
            'id' => 'cattle',
            'name' => 'Cattle',
            'count' => $this->formatNumber($kpis['animal_demographics']['total_cattle'] ?? 0),
            'description' => $genderBreakdown,
            'icon' => 'agriculture',
            'type' => 'cattle',
            'is_clickable' => true,
            'route' => '/animals',
            'route_data' => ['filter' => 'cattle'],
            'is_high_priority' => true,
            'trend' => null,
            'trend_value' => null,
        ];

        // Milk Production (High Priority)
        $milkData = $kpis['milk_production']['total_milk_production'] ?? null;
        $items[] = [
            'id' => 'milk_production',
            'name' => 'Milk Production',
            'count' => $this->formatNumber($milkData['value'] ?? 0) . ' L',
            'description' => 'This period total',
            'icon' => 'water_drop',
            'type' => 'milk_production',
            'is_clickable' => false,
            'is_high_priority' => true,
            'trend' => $this->getTrendDirection($milkData),
            'trend_value' => $this->getTrendValue($milkData),
        ];

        // Lactating Cows (from milking events)
        $items[] = [
            'id' => 'lactating_cows',
            'name' => 'Lactating Cows',
            'count' => $this->formatNumber($kpis['animal_demographics']['lactating_cows'] ?? 0),
            'description' => 'Currently producing milk',
            'icon' => 'local_drink',
            'type' => 'lactating_cows',
            'is_clickable' => false,
            'is_high_priority' => false,
            'trend' => null,
            'trend_value' => null,
        ];

        // Pregnant Cows
        $items[] = [
            'id' => 'pregnant_cows',
            'name' => 'Pregnant Cows',
            'count' => $this->formatNumber($kpis['animal_demographics']['pregnant_cows'] ?? 0),
            'description' => 'Expected calvings',
            'icon' => 'pregnant_woman',
            'type' => 'pregnant_cows',
            'is_clickable' => false,
            'is_high_priority' => false,
            'trend' => null,
            'trend_value' => null,
        ];

        // Young Stock
        $ageBandsData = $kpis['animal_demographics']['age_bands'] ?? [];
        $ageBreakdown = $this->buildAgeBreakdown($ageBandsData);
        $herdGrowthData = $kpis['herd_overview']['herd_growth'] ?? null;
        $items[] = [
            'id' => 'youngstock',
            'name' => 'Young Stock',
            'count' => $this->formatNumber($kpis['herd_overview']['youngstock_count'] ?? 0),
            'description' => $ageBreakdown,
            'icon' => 'child_friendly',
            'type' => 'young_stock',
            'is_clickable' => false,
            'is_high_priority' => false,
            'trend' => $this->getTrendDirection($herdGrowthData),
            'trend_value' => $this->getTrendValue($herdGrowthData),
        ];

        // Births
        $birthsData = $kpis['herd_overview']['births'] ?? null;
        $items[] = [
            'id' => 'births',
            'name' => 'Births',
            'count' => $this->formatNumber($birthsData['value'] ?? 0),
            'description' => 'This period',
            'icon' => 'baby_changing_station',
            'type' => 'births',
            'is_clickable' => false,
            'is_high_priority' => false,
            'trend' => $this->getTrendDirection($birthsData),
            'trend_value' => $this->getTrendValue($birthsData),
        ];

        // Deaths (from archived animals)
        $deathsData = $kpis['herd_overview']['deaths'] ?? null;
        $items[] = [
            'id' => 'deaths',
            'name' => 'Deaths',
            'count' => $this->formatNumber($deathsData['value'] ?? 0),
            'description' => 'This period',
            'icon' => 'sentiment_very_dissatisfied',
            'type' => 'deaths',
            'is_clickable' => false,
            'is_high_priority' => false,
            'trend' => $this->getTrendDirection($deathsData),
            'trend_value' => $this->getTrendValue($deathsData),
        ];

        return $items;
    }

    private function buildSpeciesBreakdown($speciesCount)
    {
        if (empty($speciesCount)) {
            return 'No breakdown available';
        }

        $breakdown = [];
        foreach ($speciesCount as $species => $count) {
            if ($count > 0) {
                $breakdown[] = "$species: $count";
            }
        }

        return !empty($breakdown) ? implode(', ', $breakdown) : 'No animals';
    }

    private function buildGenderBreakdown($sexCount)
    {
        if (empty($sexCount)) {
            return 'No breakdown available';
        }

        $breakdown = [];
        foreach ($sexCount as $sex => $count) {
            if ($count > 0) {
                $breakdown[] = "$sex: $count";
            }
        }

        return !empty($breakdown) ? implode(', ', $breakdown) : 'No breakdown';
    }

    private function buildAgeBreakdown($ageBands)
    {
        if (empty($ageBands)) {
            return 'Animals under 2 years';
        }

        $breakdown = [];
        $ageLabels = [
            '0_6m' => '0-6m',
            '6_12m' => '6-12m',
            '12_24m' => '12-24m',
            '24m_plus' => '24m+',
        ];

        foreach ($ageBands as $key => $count) {
            if ($count > 0 && isset($ageLabels[$key])) {
                $breakdown[] = $ageLabels[$key] . ": $count";
            }
        }

        return !empty($breakdown) ? implode(', ', $breakdown) : 'Animals under 2 years';
    }

    private function getTrendDirection($comparisonData)
    {
        if (!is_array($comparisonData) || !isset($comparisonData['diff'])) {
            return null;
        }

        $diff = $comparisonData['diff'];
        if ($diff > 0) return 'up';
        if ($diff < 0) return 'down';
        return 'neutral';
    }

    private function getTrendValue($comparisonData)
    {
        if (!is_array($comparisonData) || !isset($comparisonData['diff'])) {
            return null;
        }

        $diff = $comparisonData['diff'];
        if ($diff == 0) return null;
        
        $sign = $diff >= 0 ? '+' : '';
        return "$sign$diff";
    }

    // ===============================================
    // CONSOLIDATED DASHBOARD API ENDPOINTS
    // ===============================================

    /**
     * Get ALL KPI/COUNT data in single endpoint for dashboard numbers/metrics
     * GET /api/farm-analysis/{farm_id}/dashboard-kpis
     */
    public function dashboardKpis(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'required|integer',
            'range_from' => 'nullable|date',
            'range_to' => 'nullable|date',
        ]);

        $farmId = $validated['farm_id'];
        $rangeFrom = isset($validated['range_from']) ? Carbon::parse($validated['range_from'])->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $rangeTo = isset($validated['range_to']) ? Carbon::parse($validated['range_to'])->endOfDay() : Carbon::now()->endOfDay();

        try {
            // Get base data
            $animals = $this->getFarmAnimals($farmId);
            $events = $this->getFarmEvents($farmId, $rangeFrom, $rangeTo);
            $archivedAnimals = $this->getArchivedAnimals($farmId, $rangeFrom, $rangeTo);

            // Calculate previous period for trends
            $daysDiff = $rangeFrom->diffInDays($rangeTo);
            $prevRangeFrom = $rangeFrom->copy()->subDays($daysDiff + 1);
            $prevRangeTo = $rangeTo->copy()->subDays($daysDiff + 1);
            $prevEvents = $this->getFarmEvents($farmId, $prevRangeFrom, $prevRangeTo);
            $prevArchivedAnimals = $this->getArchivedAnimals($farmId, $prevRangeFrom, $prevRangeTo);

            // Calculate ALL KPIs and metrics in one place
            $kpis = $this->compute_kpis($animals, $events, $prevEvents, $archivedAnimals, $prevArchivedAnimals, $rangeFrom, $rangeTo, $prevRangeFrom, $prevRangeTo);

            // Additional dashboard-specific calculations
            $totalAnimals = count($animals);
            $lactatingCows = $this->getLactatingCows($events);
            $lactatingPercentage = $totalAnimals > 0 ? round(($lactatingCows / $totalAnimals) * 100, 2) : 0;
            
            // Milk calculations
            $milkEvents = collect($events)->where('type', 'Milking');
            $dailyMilk = $milkEvents->sum('milk') / max($daysDiff, 1);
            $milkPerCow = $lactatingCows > 0 ? $dailyMilk / $lactatingCows : 0;

            // Reproduction calculations
            $services = collect($events)->whereIn('type', ['AI', 'Natural mating'])->count();
            $pregnantAnimals = DB::table('pregnant_animals')
                ->where('farm_id', $farmId)
                ->where('current_status', 'Pregnant')
                ->count();
            $conceptionRate = $services > 0 ? round(($pregnantAnimals / $services) * 100, 2) : 0;

            // Disease incidence (per 100 animals per month)
            $diseaseEvents = collect($events)->whereNotNull('disease_text')->count();
            $diseaseIncidence = $totalAnimals > 0 ? round(($diseaseEvents / $totalAnimals) * 100 * (30 / max($daysDiff, 1)), 2) : 0;

            // Vaccination coverage
            $vaccinatedCount = DB::table('farm_vaccination_records')
                ->where('farm_id', $farmId)
                ->whereBetween('created_at', [$rangeFrom, $rangeTo])
                ->sum('number_of_animals_vaccinated');
            $vaccCoverage = $totalAnimals > 0 ? min(round(($vaccinatedCount / $totalAnimals) * 100, 2), 100) : 0;

            // Mortality rate
            $deaths = collect($archivedAnimals)->where('last_event', 'Mortality')->count();
            $mortalityRate = $totalAnimals > 0 ? round(($deaths / ($totalAnimals + $deaths)) * 100, 2) : 0;

            // Data quality score
            $dataQuality = $this->calculateDataQuality($animals);

            // Calculate trends
            $prevLactating = $this->getLactatingCows($prevEvents);
            $prevDeaths = collect($prevArchivedAnimals)->where('last_event', 'Mortality')->count();
            $growthThisMonth = $totalAnimals - ($totalAnimals - $prevLactating + $prevDeaths);

            // Species breakdown for demographics
            $speciesBreakdown = DB::table('animals')
                ->where('farm_id', $farmId)
                ->whereNull('deleted_at')
                ->selectRaw('
                    type,
                    COUNT(*) as count
                ')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type')
                ->toArray();

            // Normalize species names and get counts
            $cattle = $speciesBreakdown['Cattle'] ?? $speciesBreakdown['cattle'] ?? 0;
            $goats = $speciesBreakdown['Goats'] ?? $speciesBreakdown['goat'] ?? $speciesBreakdown['Goat'] ?? 0;
            $sheep = $speciesBreakdown['Sheep'] ?? $speciesBreakdown['sheep'] ?? 0;
            $pigs = $speciesBreakdown['Pigs'] ?? $speciesBreakdown['pig'] ?? $speciesBreakdown['Pig'] ?? 0;

            // Calculate age bands
            $animals_with_dob = DB::table('animals')
                ->where('farm_id', $farmId)
                ->whereNull('deleted_at')
                ->whereNotNull('dob')
                ->select('dob', 'sex', 'breed')
                ->get();

            $ageBands = [
                'calves_0_6_months' => 0,
                'young_6_12_months' => 0,
                'growing_12_24_months' => 0,
                'mature_over_24_months' => 0
            ];

            $genderDistribution = ['male' => 0, 'female' => 0];
            $breedCounts = [];

            foreach ($animals_with_dob as $animal) {
                // Age calculation
                $dob = Carbon::parse($animal->dob);
                $ageInMonths = $dob->diffInMonths(now());

                if ($ageInMonths <= 6) {
                    $ageBands['calves_0_6_months']++;
                } elseif ($ageInMonths <= 12) {
                    $ageBands['young_6_12_months']++;
                } elseif ($ageInMonths <= 24) {
                    $ageBands['growing_12_24_months']++;
                } else {
                    $ageBands['mature_over_24_months']++;
                }

                // Gender distribution
                $sex = strtolower($animal->sex ?? '');
                if (in_array($sex, ['male', 'm', 'bull'])) {
                    $genderDistribution['male']++;
                } elseif (in_array($sex, ['female', 'f', 'cow'])) {
                    $genderDistribution['female']++;
                }

                // Breed counting
                if (!empty($animal->breed)) {
                    $breed = trim($animal->breed);
                    $breedCounts[$breed] = ($breedCounts[$breed] ?? 0) + 1;
                }
            }

            // Top 5 breeds
            arsort($breedCounts);
            $topBreeds = array_map(function($breed, $count) {
                return ['breed' => $breed, 'count' => $count];
            }, array_keys($breedCounts), $breedCounts);
            $topBreeds = array_slice($topBreeds, 0, 5);

            // Reproduction funnel data
            $pdChecksDone = DB::table('events')
                ->where('farm_id', $farmId)
                ->whereNotNull('pregnancy_check_results')
                ->whereBetween('created_at', [$rangeFrom, $rangeTo])
                ->count();

            $dueForCalving = DB::table('pregnant_animals')
                ->where('farm_id', $farmId)
                ->where('current_status', 'Pregnant')
                ->whereBetween('expected_calving_date', [now(), now()->addDays(60)])
                ->count();

            $servicesPerConception = $pregnantAnimals > 0 ? round($services / $pregnantAnimals, 2) : 0;
            $pdCompletionRate = $services > 0 ? round(($pdChecksDone / $services) * 100, 2) : 0;

            $abortions = DB::table('pregnant_animals')
                ->where('farm_id', $farmId)
                ->where('did_animal_abort', 'Yes')
                ->whereBetween('updated_at', [$rangeFrom, $rangeTo])
                ->count();
            $totalPregnancies = $pregnantAnimals + $abortions;
            $abortionRate = $totalPregnancies > 0 ? round(($abortions / $totalPregnancies) * 100, 2) : 0;

            // Upcoming events
            $calvingsNext30Days = DB::table('pregnant_animals')
                ->where('farm_id', $farmId)
                ->where('current_status', 'Pregnant')
                ->whereBetween('expected_calving_date', [now(), now()->addDays(30)])
                ->count();

            $pdChecksDue = DB::table('animals')
                ->where('farm_id', $farmId)
                ->where('is_pregnant', 'No')
                ->where('sex', 'Female')
                ->whereRaw('DATEDIFF(NOW(), service_date) >= 60')
                ->whereRaw('service_date IS NOT NULL')
                ->count();

            // RETURN ALL DATA IN ONE CONSOLIDATED RESPONSE
            return response()->json([
                'success' => true,
                'data' => [
                    // MAIN KPI DATA (for KPI tiles/cards)
                    'kpis' => [
                        'total_animals' => $totalAnimals,
                        'lactating_cows' => $lactatingCows,
                        'lactating_percentage' => $lactatingPercentage,
                        'daily_milk_liters' => round($dailyMilk, 2),
                        'milk_per_cow' => round($milkPerCow, 2),
                        'conception_rate' => $conceptionRate,
                        'disease_incidence' => $diseaseIncidence,
                        'vaccination_coverage' => $vaccCoverage,
                        'mortality_rate' => $mortalityRate,
                        'data_quality_score' => $dataQuality,
                        'growth_this_month' => max($growthThisMonth, 0),
                        'vaccination_due' => $this->getVaccinationDue($farmId),
                        'missing_tags' => $this->getMissingTagsCount($animals)
                    ],

                    // ORIGINAL FARM ANALYSIS DATA (formatted numbers)
                    'farm_analysis' => $kpis,

                    // DEMOGRAPHICS COUNTS
                    'demographics' => [
                        'species_breakdown' => [
                            'cattle' => $cattle,
                            'goats' => $goats,
                            'sheep' => $sheep,
                            'pigs' => $pigs
                        ],
                        'age_bands' => $ageBands,
                        'gender_distribution' => $genderDistribution,
                        'top_breeds' => $topBreeds,
                        'total_animals' => array_sum($speciesBreakdown)
                    ],

                    // REPRODUCTION FUNNEL COUNTS
                    'reproduction_funnel' => [
                        'funnel_data' => [
                            'services_given' => $services,
                            'pd_checks_done' => $pdChecksDone,
                            'currently_pregnant' => $pregnantAnimals,
                            'due_for_calving' => $dueForCalving
                        ],
                        'performance_metrics' => [
                            'services_per_conception' => $servicesPerConception,
                            'conception_rate' => $conceptionRate,
                            'pd_completion_rate' => $pdCompletionRate,
                            'abortion_rate' => $abortionRate
                        ],
                        'upcoming_events' => [
                            'calvings_next_30_days' => $calvingsNext30Days,
                            'pd_checks_due' => $pdChecksDue,
                            'services_scheduled' => 0
                        ]
                    ]
                ],
                'metadata' => [
                    'farm_id' => $farmId,
                    'generated_at' => now()->toISOString(),
                    'date_range' => [
                        'from' => $rangeFrom->toDateString(),
                        'to' => $rangeTo->toDateString()
                    ],
                    'endpoint_type' => 'dashboard_kpis_consolidated'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating dashboard KPI data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ALL GRAPH data in single endpoint for dashboard charts/visualizations
     * GET /api/farm-analysis/{farm_id}/dashboard-graphs
     */
    public function dashboardGraphs(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'required|integer',
            'range_from' => 'nullable|date',
            'range_to' => 'nullable|date',
        ]);

        $farmId = $validated['farm_id'];
        $rangeFrom = isset($validated['range_from']) ? Carbon::parse($validated['range_from'])->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $rangeTo = isset($validated['range_to']) ? Carbon::parse($validated['range_to'])->endOfDay() : Carbon::now()->endOfDay();

        try {
            // Get all base data for graphs
            $animals = $this->getFarmAnimals($farmId);
            $events = $this->getFarmEvents($farmId, $rangeFrom, $rangeTo);
            $archivedAnimals = $this->getArchivedAnimals($farmId, $rangeFrom, $rangeTo);

            // GRAPH 1: MILK PRODUCTION TREND DATA (30 days)
            $milkTrendData = [];
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subDays(29);
            
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dayMilk = DB::table('events')
                    ->where('farm_id', $farmId)
                    ->where('type', 'Milking')
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('milk');

                $lactatingCows = $this->getLactatingCows(
                    DB::table('events')
                        ->where('farm_id', $farmId)
                        ->where('created_at', '<=', $date->endOfDay())
                        ->get()
                        ->toArray()
                );

                $avgPerCow = $lactatingCows > 0 ? $dayMilk / $lactatingCows : 0;

                $milkTrendData[] = [
                    'date' => $date->format('Y-m-d'),
                    'total_milk' => round($dayMilk, 2),
                    'lactating_cows' => $lactatingCows,
                    'avg_per_cow' => round($avgPerCow, 2),
                    'day_name' => $date->format('l')
                ];
            }

            // GRAPH 2: SPECIES DEMOGRAPHICS PIE CHART DATA
            $speciesData = DB::table('animals')
                ->where('farm_id', $farmId)
                ->whereNull('deleted_at')
                ->selectRaw('
                    CASE 
                        WHEN LOWER(type) IN ("cattle", "cow") THEN "Cattle"
                        WHEN LOWER(type) IN ("goat", "goats") THEN "Goats"
                        WHEN LOWER(type) IN ("sheep") THEN "Sheep"
                        WHEN LOWER(type) IN ("pig", "pigs") THEN "Pigs"
                        ELSE "Other"
                    END as species,
                    COUNT(*) as count
                ')
                ->groupBy('species')
                ->get()
                ->map(function($item) {
                    return [
                        'species' => $item->species,
                        'count' => $item->count,
                        'percentage' => 0 // Will be calculated
                    ];
                })->toArray();

            // Calculate percentages for species
            $totalSpeciesCount = array_sum(array_column($speciesData, 'count'));
            foreach ($speciesData as &$species) {
                $species['percentage'] = $totalSpeciesCount > 0 ? round(($species['count'] / $totalSpeciesCount) * 100, 2) : 0;
            }

            // GRAPH 3: AGE DISTRIBUTION BAR CHART DATA
            $ageDistribution = [];
            $animals_with_dob = DB::table('animals')
                ->where('farm_id', $farmId)
                ->whereNull('deleted_at')
                ->whereNotNull('dob')
                ->select('dob')
                ->get();

            $ageBands = [
                'Calves (0-6 months)' => 0,
                'Young (6-12 months)' => 0,
                'Growing (12-24 months)' => 0,
                'Mature (24+ months)' => 0
            ];

            foreach ($animals_with_dob as $animal) {
                $dob = Carbon::parse($animal->dob);
                $ageInMonths = $dob->diffInMonths(now());

                if ($ageInMonths <= 6) {
                    $ageBands['Calves (0-6 months)']++;
                } elseif ($ageInMonths <= 12) {
                    $ageBands['Young (6-12 months)']++;
                } elseif ($ageInMonths <= 24) {
                    $ageBands['Growing (12-24 months)']++;
                } else {
                    $ageBands['Mature (24+ months)']++;
                }
            }

            // Convert to chart format
            foreach ($ageBands as $band => $count) {
                $ageDistribution[] = [
                    'age_band' => $band,
                    'count' => $count,
                    'percentage' => $animals_with_dob->count() > 0 ? round(($count / $animals_with_dob->count()) * 100, 2) : 0
                ];
            }

            // GRAPH 4: REPRODUCTION FUNNEL CHART DATA
            $services = DB::table('events')
                ->where('farm_id', $farmId)
                ->whereIn('type', ['AI', 'Natural mating'])
                ->whereBetween('created_at', [$rangeFrom, $rangeTo])
                ->count();

            $pdChecksDone = DB::table('events')
                ->where('farm_id', $farmId)
                ->whereNotNull('pregnancy_check_results')
                ->whereBetween('created_at', [$rangeFrom, $rangeTo])
                ->count();

            $pregnantAnimals = DB::table('pregnant_animals')
                ->where('farm_id', $farmId)
                ->where('current_status', 'Pregnant')
                ->count();

            $dueForCalving = DB::table('pregnant_animals')
                ->where('farm_id', $farmId)
                ->where('current_status', 'Pregnant')
                ->whereBetween('expected_calving_date', [now(), now()->addDays(60)])
                ->count();

            $reproductionFunnelData = [
                ['stage' => 'Services Given', 'count' => $services, 'percentage' => 100],
                ['stage' => 'PD Checks Done', 'count' => $pdChecksDone, 'percentage' => $services > 0 ? round(($pdChecksDone / $services) * 100, 2) : 0],
                ['stage' => 'Currently Pregnant', 'count' => $pregnantAnimals, 'percentage' => $services > 0 ? round(($pregnantAnimals / $services) * 100, 2) : 0],
                ['stage' => 'Due for Calving', 'count' => $dueForCalving, 'percentage' => $services > 0 ? round(($dueForCalving / $services) * 100, 2) : 0]
            ];

            // GRAPH 5: HEALTH STATUS DISTRIBUTION (Disease Events by Type)
            $healthEvents = DB::table('events')
                ->where('farm_id', $farmId)
                ->whereNotNull('disease_text')
                ->whereBetween('created_at', [$rangeFrom, $rangeTo])
                ->selectRaw('
                    COALESCE(disease_text, "Unknown Disease") as disease,
                    COUNT(*) as count
                ')
                ->groupBy('disease_text')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'disease' => $item->disease,
                        'count' => $item->count
                    ];
                })->toArray();

            // GRAPH 6: VACCINATION STATUS PIE CHART
            $totalAnimals = count($animals);
            $vaccinatedCount = DB::table('farm_vaccination_records')
                ->where('farm_id', $farmId)
                ->whereBetween('created_at', [$rangeFrom->subMonths(3), $rangeTo]) // Last 3 months
                ->sum('number_of_animals_vaccinated');

            $vaccinationStatus = [
                ['status' => 'Vaccinated', 'count' => min($vaccinatedCount, $totalAnimals), 'color' => '#4CAF50'],
                ['status' => 'Not Vaccinated', 'count' => max(0, $totalAnimals - $vaccinatedCount), 'color' => '#F44336']
            ];

            // GRAPH 7: MONTHLY MILK TREND (Last 12 months)
            $monthlyMilkTrend = [];
            for ($i = 11; $i >= 0; $i--) {
                $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
                $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
                
                $monthlyMilk = DB::table('events')
                    ->where('farm_id', $farmId)
                    ->where('type', 'Milking')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->sum('milk');

                $monthlyMilkTrend[] = [
                    'month' => $monthStart->format('M Y'),
                    'month_short' => $monthStart->format('M'),
                    'total_milk' => round($monthlyMilk, 2),
                    'avg_per_day' => round($monthlyMilk / $monthStart->daysInMonth, 2)
                ];
            }

            // RETURN ALL GRAPH DATA IN ONE CONSOLIDATED RESPONSE
            return response()->json([
                'success' => true,
                'data' => [
                    // MILK PRODUCTION TREND (Line Chart - 30 days)
                    'milk_trend' => [
                        'chart_type' => 'line',
                        'title' => 'Daily Milk Production (30 Days)',
                        'data' => $milkTrendData,
                        'summary' => [
                            'total_days' => count($milkTrendData),
                            'avg_daily_milk' => round(array_sum(array_column($milkTrendData, 'total_milk')) / count($milkTrendData), 2),
                            'avg_lactating_cows' => round(array_sum(array_column($milkTrendData, 'lactating_cows')) / count($milkTrendData), 0)
                        ]
                    ],

                    // SPECIES DEMOGRAPHICS (Pie Chart)
                    'species_demographics' => [
                        'chart_type' => 'pie',
                        'title' => 'Animal Species Distribution',
                        'data' => $speciesData,
                        'total_count' => $totalSpeciesCount
                    ],

                    // AGE DISTRIBUTION (Bar Chart)
                    'age_distribution' => [
                        'chart_type' => 'bar',
                        'title' => 'Age Group Distribution',
                        'data' => $ageDistribution,
                        'total_animals_with_dob' => $animals_with_dob->count()
                    ],

                    // REPRODUCTION FUNNEL (Funnel/Bar Chart)
                    'reproduction_funnel' => [
                        'chart_type' => 'funnel',
                        'title' => 'Reproduction Performance Funnel',
                        'data' => $reproductionFunnelData,
                        'conversion_rates' => [
                            'service_to_pd' => $services > 0 ? round(($pdChecksDone / $services) * 100, 2) : 0,
                            'service_to_pregnant' => $services > 0 ? round(($pregnantAnimals / $services) * 100, 2) : 0,
                            'pregnant_to_calving' => $pregnantAnimals > 0 ? round(($dueForCalving / $pregnantAnimals) * 100, 2) : 0
                        ]
                    ],

                    // HEALTH EVENTS (Bar Chart)
                    'health_distribution' => [
                        'chart_type' => 'bar',
                        'title' => 'Disease Events Distribution',
                        'data' => $healthEvents,
                        'total_disease_events' => array_sum(array_column($healthEvents, 'count'))
                    ],

                    // VACCINATION STATUS (Pie Chart)
                    'vaccination_status' => [
                        'chart_type' => 'pie',
                        'title' => 'Vaccination Coverage Status',
                        'data' => $vaccinationStatus,
                        'coverage_percentage' => $totalAnimals > 0 ? round(($vaccinatedCount / $totalAnimals) * 100, 2) : 0
                    ],

                    // MONTHLY MILK TREND (Line Chart - 12 months)
                    'monthly_milk_trend' => [
                        'chart_type' => 'line',
                        'title' => 'Monthly Milk Production Trend (12 Months)',
                        'data' => $monthlyMilkTrend,
                        'total_12_months' => array_sum(array_column($monthlyMilkTrend, 'total_milk'))
                    ]
                ],
                'metadata' => [
                    'farm_id' => $farmId,
                    'generated_at' => now()->toISOString(),
                    'date_range' => [
                        'from' => $rangeFrom->toDateString(),
                        'to' => $rangeTo->toDateString()
                    ],
                    'total_graphs' => 7,
                    'endpoint_type' => 'dashboard_graphs_consolidated'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating dashboard graph data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper methods for calculations
    private function getLactatingCows($events)
    {
        // Count animals that have had milking events or calving events recently
        $lactatingAnimals = collect($events)
            ->filter(function($event) {
                return in_array($event->type, ['Milking', 'Calving']) || 
                       (isset($event->pregnancy_check_results) && $event->pregnancy_check_results === 'Lactating');
            })
            ->pluck('animal_id')
            ->unique()
            ->count();
        
        return $lactatingAnimals;
    }

    private function calculateDataQuality($animals)
    {
        $totalAnimals = count($animals);
        if ($totalAnimals === 0) return 100;

        $quality_score = 0;
        $complete_profiles = 0;

        foreach ($animals as $animal) {
            $score = 0;
            
            // Basic info (40 points)
            if (!empty($animal->tag_number)) $score += 10;
            if (!empty($animal->name)) $score += 5;
            if (!empty($animal->dob)) $score += 15;
            if (!empty($animal->sex)) $score += 10;
            
            // Detailed info (35 points)
            if (!empty($animal->breed)) $score += 10;
            if (!empty($animal->sire)) $score += 5;
            if (!empty($animal->dam)) $score += 5;
            if (!empty($animal->color)) $score += 5;
            if (!empty($animal->registration_number)) $score += 10;
            
            // Health info (25 points)
            if (!empty($animal->vaccination_status)) $score += 10;
            if (!empty($animal->health_status)) $score += 15;
            
            if ($score >= 80) $complete_profiles++;
            $quality_score += $score;
        }

        return round($quality_score / $totalAnimals, 1);
    }

    private function getVaccinationDue($farmId)
    {
        // Get animals that haven't been vaccinated in the last 6 months
        $due_count = DB::table('animals')
            ->leftJoin('farm_vaccination_records', function($join) use ($farmId) {
                $join->on('animals.id', '=', 'farm_vaccination_records.animal_id')
                     ->where('farm_vaccination_records.farm_id', $farmId)
                     ->where('farm_vaccination_records.created_at', '>', now()->subMonths(6));
            })
            ->where('animals.farm_id', $farmId)
            ->whereNull('animals.deleted_at')
            ->whereNull('farm_vaccination_records.id')
            ->count();

        return $due_count;
    }

    private function getMissingTagsCount($animals)
    {
        return collect($animals)->filter(function($animal) {
            return empty($animal->tag_number) || $animal->tag_number === null;
        })->count();
    }
}
