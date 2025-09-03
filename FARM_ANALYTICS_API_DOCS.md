# Farm Analytics Dashboard API Documentation

## Overview
This API provides comprehensive analytics data for farmer-facing dashboard graphs. Built as an extension of the existing `FarmAnalysisController`.

## Base URL
```
GET /api/farm-analysis/{farm_id}
```

## Authentication
- Same authentication as existing API endpoints
- Farm-level data isolation enforced

## Endpoints

### 1. KPI Dashboard Grid
**Endpoint:** `GET /api/farm-analysis/{farm_id}/kpi-grid`

**Description:** Returns 8 key performance indicators for dashboard tiles

**Parameters:**
- `range_from` (optional): Start date (YYYY-MM-DD)
- `range_to` (optional): End date (YYYY-MM-DD)

**Response Example:**
```json
{
  "success": true,
  "data": {
    "total_animals": 245,
    "lactating_cows": 67,
    "lactating_percentage": 27.3,
    "daily_milk_liters": 890.5,
    "milk_per_cow": 13.3,
    "conception_rate": 68.2,
    "disease_incidence": 4.7,
    "vaccination_coverage": 84.1,
    "mortality_rate": 2.1,
    "data_quality_score": 91.5,
    "growth_this_month": 12,
    "vaccination_due": 18,
    "missing_tags": 8
  }
}
```

### 2. Milk Production Trend
**Endpoint:** `GET /api/farm-analysis/{farm_id}/milk-trend`

**Description:** Daily milk production data with moving averages

**Parameters:**
- `days` (optional): Number of days (7-90, default: 30)

**Response Example:**
```json
{
  "success": true,
  "data": {
    "period": "30_days",
    "total_production": 26750.5,
    "average_daily": 891.7,
    "trend_direction": "increasing",
    "peak_production": 945.2,
    "low_production": 823.1,
    "daily_data": [
      {
        "date": "2025-08-02",
        "total_liters": 875.5,
        "lactating_cows": 65,
        "average_per_cow": 13.5,
        "seven_day_moving_average": 882.1
      }
    ]
  }
}
```

### 3. Reproduction Performance Funnel
**Endpoint:** `GET /api/farm-analysis/{farm_id}/reproduction-funnel`

**Description:** Service-to-calving performance funnel

**Parameters:**
- `range_from` (optional): Start date (YYYY-MM-DD)
- `range_to` (optional): End date (YYYY-MM-DD)

**Response Example:**
```json
{
  "success": true,
  "data": {
    "funnel_data": {
      "services_given": 127,
      "pd_checks_done": 98,
      "currently_pregnant": 67,
      "due_for_calving": 23
    },
    "performance_metrics": {
      "services_per_conception": 1.9,
      "conception_rate": 67.8,
      "pd_completion_rate": 77.2,
      "abortion_rate": 3.2
    },
    "upcoming_events": {
      "calvings_next_30_days": 12,
      "pd_checks_due": 8,
      "services_scheduled": 5
    }
  }
}
```

### 4. Herd Demographics Analysis
**Endpoint:** `GET /api/farm-analysis/{farm_id}/demographics`

**Description:** Species, age, gender, and breed distribution

**Response Example:**
```json
{
  "success": true,
  "data": {
    "species_breakdown": {
      "cattle": 127,
      "goats": 89,
      "sheep": 34,
      "pigs": 15
    },
    "age_bands": {
      "calves_0_6_months": 45,
      "young_6_12_months": 38,
      "growing_12_24_months": 67,
      "mature_over_24_months": 115
    },
    "gender_distribution": {
      "male": 78,
      "female": 187
    },
    "top_breeds": [
      {"breed": "Friesian", "count": 45},
      {"breed": "Ankole", "count": 38}
    ],
    "total_animals": 265
  }
}
```

## Error Handling

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description here"
}
```

**Common Error Codes:**
- `400` - Invalid parameters
- `404` - Farm not found
- `500` - Internal server error

## Data Sources

### Database Tables Used:
- `animals` - Animal records and demographics
- `events` - All farm events (milking, breeding, treatments)
- `pregnant_animals` - Pregnancy tracking
- `archived_animals` - Historical records including deaths
- `farm_vaccination_records` - Vaccination history

### Key Calculations:
- **Lactating Cows:** Unique animals with milking events in date range
- **Conception Rate:** Pregnant animals / Total services × 100
- **Disease Incidence:** Cases per 100 animals per month
- **Data Quality:** Percentage of complete animal records
- **Moving Averages:** 7-day rolling averages for trend smoothing

## Performance Notes

- **Caching:** Recommended cache times:
  - KPI Grid: 5 minutes
  - Milk Trend: 15 minutes
  - Demographics: 1 hour
  
- **Database Optimization:** Queries use indexed fields (`farm_id`, `created_at`, `animal_id`)

- **Rate Limiting:** Standard API rate limits apply

## Flutter Integration

### Using with fl_chart:

```dart
// KPI Grid
FutureBuilder<Map<String, dynamic>>(
  future: ApiService.getKpiGrid(farmId),
  builder: (context, snapshot) {
    if (snapshot.hasData) {
      return KpiGrid(data: snapshot.data);
    }
    return CircularProgressIndicator();
  },
)

// Milk Trend Chart
LineChart(
  LineChartData(
    lineBarsData: [
      LineChartBarData(
        spots: dailyData.map((d) => 
          FlSpot(d['x'], d['total_liters'])
        ).toList(),
      ),
    ],
  ),
)
```

## Testing

Use the provided test script:
```bash
php test_farm_analytics.php
```

Or test individual endpoints:
```bash
curl "http://localhost:8888/etag-web/public/api/farm-analysis/1/kpi-grid"
```
