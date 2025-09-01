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

class FarmAnalysisController extends Controller
{
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
            return response()->json(['error' => 'Farm not found'], 404);
        }

        // Get all data in bulk to minimize DB queries
        $animals = $this->getFarmAnimals($validated['farm_id']);
        $events = $this->getFarmEvents($validated['farm_id'], $rangeFrom, $rangeTo);

        // Get previous period data
        $prevEvents = $this->getFarmEvents($validated['farm_id'], $prevRangeFrom, $prevRangeTo);
        $prevArchivedAnimals = $this->getArchivedAnimals($validated['farm_id'], $prevRangeFrom, $prevRangeTo);

        // Calculate KPIs with comparison data
        $kpis = $this->compute_kpis($animals, $events, $prevEvents, $prevArchivedAnimals, $rangeFrom, $rangeTo, $prevRangeFrom, $prevRangeTo);

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
// 000024311,  00002457
    /**
     * Get all animals for the farm
     */
    private function getFarmAnimals($farmId)
    {
        return DB::table('animals')
            ->where('farm_id', $farmId)
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
    private function compute_kpis($animals, $events, $prevEvents, $prevArchivedAnimals, $from, $to, $prevFrom, $prevTo)
    {
        return [
            // Herd Overview - current snapshot without comparison
            'herd_overview' => $this->computeHerdOverview($animals, $events, $prevEvents, $prevArchivedAnimals, $from, $to, $prevFrom, $prevTo),

            // Animal Structure & Demographics - current snapshot without comparison
            'animal_demographics' => $this->computeAnimalDemographics($animals),

            // Registration & Data Completeness - current snapshot without comparison
            'data_completeness' => $this->computeDataCompleteness($animals),

            // Reproduction & Fertility - event-based with comparison
            'reproduction_fertility' => $this->computeReproductionFertility($events, $prevEvents),

            // Health & Disease - event-based with comparison
            'health_disease' => $this->computeHealthDisease($events, $prevEvents, $animals),

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
    private function computeHerdOverview($animals, $events, $prevEvents, $prevArchivedAnimals, $from, $to, $prevFrom, $prevTo)
    {
        $totalAnimals = $animals->count();

        // Count by species
        $speciesCount = $animals->groupBy('type')->map->count();

        // Archived animals (with comparison)
        $archivedCount = $this->getArchivedAnimals($animals->first()->farm_id ?? 0, $from, $to)->count();
        $prevArchivedCount = $prevArchivedAnimals->count();

        // Youngstock (calves < 1 year, cattle only)
        $youngstockCount = $animals->where('type', 'Cattle')
            ->filter(function ($animal) {
                return $this->calculateAgeInMonths($animal->dob) < 12;
            })->count();

        // Herd growth (births - deaths) with comparison
        $births = $this->getEventCount($events, 'Calving');
        $deaths = $this->getEventCount($events, 'Mortality');
        $herdGrowth = $births - $deaths;

        $prevBirths = $this->getEventCount($prevEvents, 'Calving');
        $prevDeaths = $this->getEventCount($prevEvents, 'Mortality');
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
     */
    private function computeAnimalDemographics($animals)
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

        // Cattle production status
        $lactatingCows = $cattle->where('sex', 'Female')
            ->where('lactation_status', 'Lactating')->count();

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
     * Compute data completeness metrics
     */
    private function computeDataCompleteness($animals)
    {
        $totalAnimals = $animals->count();

        // Animals missing photo
        $missingPhoto = $animals->where('photo', '')->count();

        // Animals missing E-ID or V-ID
        $missingEid = $animals->where('e_id', '')->count();
        $missingVid = $animals->where('v_id', '')->count();

        // Animals missing species, sex, or DoB
        $missingSpecies = $animals->where('type', '')->count();
        $missingSex = $animals->where('sex', '')->count();
        $missingDob = $animals->where('dob', '')->count();

        return [
            'missing_photo' => $missingPhoto,
            'missing_eid' => $missingEid,
            'missing_vid' => $missingVid,
            'missing_species' => $missingSpecies,
            'missing_sex' => $missingSex,
            'missing_dob' => $missingDob,
            'completeness_score' => $this->calculateDataQualityScore($animals),
        ];
    }

    /**
     * Compute reproduction and fertility metrics with comparison
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

        // Conception rate
        $conceptionRate = $services->count() > 0 ? ($pregnant / $services->count()) * 100 : 0;
        $prevConceptionRate = $prevServices->count() > 0 ? ($prevPregnant / $prevServices->count()) * 100 : 0;

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
            'conception_rate' => $this->compareValues(round($conceptionRate, 2), round($prevConceptionRate, 2)),
            'calvings' => $this->compareValues($calvings, $prevCalvings),
            'abortions' => $this->compareValues($abortions, $prevAbortions, true),
        ];
    }

    /**
     * Compute health and disease metrics with comparison
     */
    private function computeHealthDisease($events, $prevEvents, $animals)
    {
        // Disease events
        $diseaseEvents = $events->where('type', 'Disease test');
        $diseaseCount = $diseaseEvents->count();
        $prevDiseaseCount = $prevEvents->where('type', 'Disease test')->count();

        // Treatment events
        $treatmentEvents = $events->where('type', 'Treatment');
        $treatmentCount = $treatmentEvents->count();
        $prevTreatmentCount = $prevEvents->where('type', 'Treatment')->count();

        // Vaccination events
        $vaccinationEvents = $events->where('type', 'Vaccination');
        $vaccinationCount = $vaccinationEvents->count();
        $prevVaccinationCount = $prevEvents->where('type', 'Vaccination')->count();

        // Mortality events
        $mortalityEvents = $events->where('type', 'Mortality');
        $mortalityCount = $mortalityEvents->count();
        $prevMortalityCount = $prevEvents->where('type', 'Mortality')->count();

        // Disease incidence rate
        $avgHeadcount = $animals->count();
        $diseaseIncidenceRate = $avgHeadcount > 0 ? ($diseaseCount / $avgHeadcount) * 100 : 0;

        $prevAvgHeadcount = $this->getFarmAnimalsAtDate($animals, $prevEvents->last()->created_at ?? Carbon::now()->subYear())->count();
        $prevDiseaseIncidenceRate = $prevAvgHeadcount > 0 ? ($prevDiseaseCount / $prevAvgHeadcount) * 100 : 0;

        return [
            'disease_events' => $this->compareValues($diseaseCount, $prevDiseaseCount, true),
            'treatment_events' => $this->compareValues($treatmentCount, $prevTreatmentCount, true),
            'vaccination_events' => $this->compareValues($vaccinationCount, $prevVaccinationCount),
            'mortality_events' => $this->compareValues($mortalityCount, $prevMortalityCount, true),
            'disease_incidence_rate' => $this->compareValues(round($diseaseIncidenceRate, 2), round($prevDiseaseIncidenceRate, 2), true),
        ];
    }

    /**
     * Compute milk production metrics with comparison
     */
    private function computeMilkProduction($events, $prevEvents, $animals)
    {
        // Milking events - use existing detail field to extract milk quantity
        $milkingEvents = $events->where('type', 'Milking');
        $totalMilk = 0;
        foreach ($milkingEvents as $event) {
            // Extract milk quantity from detail field (format: "X liters" or just number)
            $milk = $this->extractMilkQuantity($event->detail ?? '0');
            $totalMilk += $milk;
        }
        $avgMilkPerEvent = $milkingEvents->count() > 0 ? $totalMilk / $milkingEvents->count() : 0;

        $prevMilkingEvents = $prevEvents->where('type', 'Milking');
        $prevTotalMilk = 0;
        foreach ($prevMilkingEvents as $event) {
            $milk = $this->extractMilkQuantity($event->detail ?? '0');
            $prevTotalMilk += $milk;
        }
        $prevAvgMilkPerEvent = $prevMilkingEvents->count() > 0 ? $prevTotalMilk / $prevMilkingEvents->count() : 0;

        // Lactating animals (cattle only)
        $lactatingAnimals = $animals->where('type', 'Cattle')
            ->where('lactation_status', 'Lactating')->count();
        $avgMilkPerLactatingAnimal = $lactatingAnimals > 0 ? $totalMilk / $lactatingAnimals : 0;

        $prevLactatingAnimals = $this->getFarmAnimalsAtDate($animals, $prevEvents->last()->created_at ?? Carbon::now()->subYear())
            ->where('type', 'Cattle')
            ->where('lactation_status', 'Lactating')->count();
        $prevAvgMilkPerLactatingAnimal = $prevLactatingAnimals > 0 ? $prevTotalMilk / $prevLactatingAnimals : 0;

        return [
            'total_milk_production' => $this->compareValues($totalMilk, $prevTotalMilk),
            'milking_events' => $this->compareValues($milkingEvents->count(), $prevMilkingEvents->count()),
            'avg_milk_per_event' => $this->compareValues(round($avgMilkPerEvent, 2), round($prevAvgMilkPerEvent, 2)),
            'lactating_animals' => $this->compareValues($lactatingAnimals, $prevLactatingAnimals),
            'avg_milk_per_lactating_animal' => $this->compareValues(round($avgMilkPerLactatingAnimal, 2), round($prevAvgMilkPerLactatingAnimal, 2)),
        ];
    }

    /**
     * Compute treatment metrics with comparison
     */
    private function computeTreatmentMetrics($events, $prevEvents, $animals)
    {
        // Treatment events
        $treatmentEvents = $events->where('type', 'Treatment');
        $treatmentCount = $treatmentEvents->count();
        $prevTreatmentCount = $prevEvents->where('type', 'Treatment')->count();

        // Treatment cost - use helper method
        $treatmentCost = 0;
        foreach ($treatmentEvents as $event) {
            $cost = $this->extractTreatmentCost($event->detail ?? '', $event->price ?? 0);
            $treatmentCost += $cost;
        }

        $prevTreatmentCost = 0;
        $prevTreatmentEvents = $prevEvents->where('type', 'Treatment');
        foreach ($prevTreatmentEvents as $event) {
            $cost = $this->extractTreatmentCost($event->detail ?? '', $event->price ?? 0);
            $prevTreatmentCost += $cost;
        }

        // Average treatment cost
        $avgTreatmentCost = $treatmentCount > 0 ? $treatmentCost / $treatmentCount : 0;
        $prevAvgTreatmentCost = $prevTreatmentCount > 0 ? $prevTreatmentCost / $prevTreatmentCount : 0;

        // Treatment rate (treatments per animal)
        $animalCount = $animals->count();
        $treatmentRate = $animalCount > 0 ? $treatmentCount / $animalCount : 0;

        $prevAnimalCount = $this->getFarmAnimalsAtDate($animals, $prevEvents->last()->created_at ?? Carbon::now()->subYear())->count();
        $prevTreatmentRate = $prevAnimalCount > 0 ? $prevTreatmentCount / $prevAnimalCount : 0;

        return [
            'treatment_count' => $this->compareValues($treatmentCount, $prevTreatmentCount, true),
            'treatment_cost' => $this->compareValues($treatmentCost, $prevTreatmentCost, true),
            'avg_treatment_cost' => $this->compareValues(round($avgTreatmentCost, 2), round($prevAvgTreatmentCost, 2), true),
            'treatment_rate' => $this->compareValues(round($treatmentRate, 2), round($prevTreatmentRate, 2), true),
        ];
    }

    /**
     * Compute financial metrics with comparison
     */
    private function computeFinancialMetrics($events, $prevEvents, $animals)
    {
        // Milk value - use price field from events table and extract milk quantity from detail
        $milkValue = 0;
        $milkingEvents = $events->where('type', 'Milking');
        foreach ($milkingEvents as $event) {
            $milkQuantity = $this->extractMilkQuantity($event->detail ?? '0');
            $pricePerLiter = ($event->price ?? 1000) / 1000; // Convert from UGX to reasonable unit
            $milkValue += $milkQuantity * $pricePerLiter;
        }

        $prevMilkValue = 0;
        $prevMilkingEvents = $prevEvents->where('type', 'Milking');
        foreach ($prevMilkingEvents as $event) {
            $milkQuantity = $this->extractMilkQuantity($event->detail ?? '0');
            $pricePerLiter = ($event->price ?? 1000) / 1000;
            $prevMilkValue += $milkQuantity * $pricePerLiter;
        }

        // Treatment cost - use price field or extract from detail
        $treatmentCost = 0;
        $treatmentEvents = $events->where('type', 'Treatment');
        foreach ($treatmentEvents as $event) {
            $cost = $this->extractTreatmentCost($event->detail ?? '', $event->price ?? 0);
            $treatmentCost += $cost;
        }

        $prevTreatmentCost = 0;
        $prevTreatmentEvents = $prevEvents->where('type', 'Treatment');
        foreach ($prevTreatmentEvents as $event) {
            $cost = $this->extractTreatmentCost($event->detail ?? '', $event->price ?? 0);
            $prevTreatmentCost += $cost;
        }

        // Net profit (milk value - treatment cost)
        $netProfit = $milkValue - $treatmentCost;
        $prevNetProfit = $prevMilkValue - $prevTreatmentCost;

        // Profit per animal
        $animalCount = $animals->count();
        $profitPerAnimal = $animalCount > 0 ? $netProfit / $animalCount : 0;

        $prevAnimalCount = $this->getFarmAnimalsAtDate($animals, $prevEvents->last()->created_at ?? Carbon::now()->subYear())->count();
        $prevProfitPerAnimal = $prevAnimalCount > 0 ? $prevNetProfit / $prevAnimalCount : 0;

        return [
            'milk_value' => $this->compareValues($milkValue, $prevMilkValue),
            'treatment_cost' => $this->compareValues($treatmentCost, $prevTreatmentCost, true),
            'net_profit' => $this->compareValues($netProfit, $prevNetProfit),
            'profit_per_animal' => $this->compareValues(round($profitPerAnimal, 2), round($prevProfitPerAnimal, 2)),
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
     * Calculate data quality score
     */
    private function calculateDataQualityScore($animals)
    {
        $totalAnimals = $animals->count();
        if ($totalAnimals === 0) return 0;

        $completeRecords = 0;

        foreach ($animals as $animal) {
            // Check if animal has all required data
            if (
                !empty($animal->photo) &&
                !empty($animal->e_id) &&
                !empty($animal->v_id) &&
                !empty($animal->type) &&
                !empty($animal->sex) &&
                !empty($animal->dob)
            ) {
                $completeRecords++;
            }
        }

        return ($completeRecords / $totalAnimals) * 100;
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
     */
    private function generateDashboardItems($kpis, $animals, $events)
    {
        $items = [];

        // Total Animals (High Priority)
        $speciesBreakdown = $this->buildSpeciesBreakdown($kpis['herd_overview']['species_count'] ?? []);
        $items[] = [
            'id' => 'total_animals',
            'name' => 'Total Animals',
            'count' => strval($kpis['herd_overview']['total_animals'] ?? 0),
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
            'count' => strval($kpis['animal_demographics']['total_cattle'] ?? 0),
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
            'count' => ($milkData['current'] ?? 0) . ' L',
            'description' => 'This period total',
            'icon' => 'water_drop',
            'type' => 'milk_production',
            'is_clickable' => false,
            'is_high_priority' => true,
            'trend' => $this->getTrendDirection($milkData),
            'trend_value' => $this->getTrendValue($milkData),
        ];

        // Lactating Cows
        $items[] = [
            'id' => 'lactating_cows',
            'name' => 'Lactating Cows',
            'count' => strval($kpis['animal_demographics']['lactating_cows'] ?? 0),
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
            'count' => strval($kpis['animal_demographics']['pregnant_cows'] ?? 0),
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
            'count' => strval($kpis['herd_overview']['youngstock_count'] ?? 0),
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
            'count' => strval($birthsData['current'] ?? 0),
            'description' => 'This period',
            'icon' => 'baby_changing_station',
            'type' => 'births',
            'is_clickable' => false,
            'is_high_priority' => false,
            'trend' => $this->getTrendDirection($birthsData),
            'trend_value' => $this->getTrendValue($birthsData),
        ];

        // Deaths
        $deathsData = $kpis['herd_overview']['deaths'] ?? null;
        $items[] = [
            'id' => 'deaths',
            'name' => 'Deaths',
            'count' => strval($deathsData['current'] ?? 0),
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
        if (!is_array($comparisonData) || !isset($comparisonData['change'])) {
            return null;
        }

        $change = $comparisonData['change'];
        if ($change > 0) return 'up';
        if ($change < 0) return 'down';
        return 'neutral';
    }

    private function getTrendValue($comparisonData)
    {
        if (!is_array($comparisonData) || !isset($comparisonData['percentage'])) {
            return null;
        }

        $percentage = round($comparisonData['percentage'], 1);
        $change = $comparisonData['change'] ?? 0;
        $sign = $change >= 0 ? '+' : '';
        
        return "$sign{$percentage}%";
    }
}
