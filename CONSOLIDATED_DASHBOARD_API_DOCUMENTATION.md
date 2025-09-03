# Consolidated Dashboard API Documentation

## Overview
As requested by the user, we have consolidated multiple graph endpoints into just **2 endpoints** for the farmer-facing analytics dashboard.

## API Endpoints

### 1. Dashboard KPIs Endpoint
**URL:** `GET /api/farm-analysis/{farm_id}/dashboard-kpis`

**Purpose:** Returns ALL numerical KPIs, metrics, and counts in a single response

**Parameters:**
- `farm_id` (required): The farm ID
- `range_from` (optional): Start date for calculations (defaults to 1 month ago)
- `range_to` (optional): End date for calculations (defaults to today)

**Response Structure:**
```json
{
    "success": true,
    "data": {
        "kpis": {
            "total_animals": 150,
            "lactating_cows": 45,
            "lactating_percentage": 30.0,
            "daily_milk_liters": 125.5,
            "milk_per_cow": 2.79,
            "conception_rate": 65.5,
            "disease_incidence": 2.1,
            "vaccination_coverage": 85.2,
            "mortality_rate": 1.2,
            "data_quality_score": 78.5,
            "growth_this_month": 5,
            "vaccination_due": 12,
            "missing_tags": 8
        },
        "farm_analysis": { /* Original formatted farm analysis data */ },
        "demographics": {
            "species_breakdown": { "cattle": 120, "goats": 25, "sheep": 5 },
            "age_bands": { /* Age distribution counts */ },
            "gender_distribution": { "male": 45, "female": 105 },
            "top_breeds": [/* Top 5 breeds with counts */]
        },
        "reproduction_funnel": {
            "funnel_data": { /* Service, PD, pregnancy counts */ },
            "performance_metrics": { /* Rates and percentages */ },
            "upcoming_events": { /* Upcoming calvings, PD checks */ }
        }
    },
    "metadata": { /* Generation details */ }
}
```

### 2. Dashboard Graphs Endpoint
**URL:** `GET /api/farm-analysis/{farm_id}/dashboard-graphs`

**Purpose:** Returns ALL graph data for visualization in a single response

**Parameters:**
- `farm_id` (required): The farm ID
- `range_from` (optional): Start date for calculations (defaults to 1 month ago)
- `range_to` (optional): End date for calculations (defaults to today)

**Response Structure:**
```json
{
    "success": true,
    "data": {
        "milk_trend": {
            "chart_type": "line",
            "title": "Daily Milk Production (30 Days)",
            "data": [/* 30 days of milk data */],
            "summary": { /* Averages and totals */ }
        },
        "species_demographics": {
            "chart_type": "pie",
            "title": "Animal Species Distribution",
            "data": [/* Species breakdown for pie chart */]
        },
        "age_distribution": {
            "chart_type": "bar",
            "title": "Age Group Distribution",
            "data": [/* Age bands for bar chart */]
        },
        "reproduction_funnel": {
            "chart_type": "funnel",
            "title": "Reproduction Performance Funnel",
            "data": [/* Funnel stages with counts and percentages */]
        },
        "health_distribution": {
            "chart_type": "bar",
            "title": "Disease Events Distribution",
            "data": [/* Disease types and counts */]
        },
        "vaccination_status": {
            "chart_type": "pie",
            "title": "Vaccination Coverage Status",
            "data": [/* Vaccinated vs not vaccinated */]
        },
        "monthly_milk_trend": {
            "chart_type": "line",
            "title": "Monthly Milk Production Trend (12 Months)",
            "data": [/* 12 months of milk data */]
        }
    },
    "metadata": {
        "total_graphs": 7,
        "endpoint_type": "dashboard_graphs_consolidated"
    }
}
```

## Benefits of Consolidation

### ✅ Reduced API Calls
- **Before:** 4+ separate API calls for different graph types
- **After:** Only 2 API calls total (1 for numbers, 1 for graphs)

### ✅ Better Performance
- Single database queries for shared calculations
- Reduced network overhead
- Faster dashboard loading

### ✅ Simplified Frontend Integration
- One call for all KPI tiles/cards
- One call for all charts/graphs
- Easier error handling and loading states

### ✅ Consistent Data
- All metrics calculated from the same dataset
- No timing inconsistencies between endpoints
- Single point of data validation

## Implementation Details

### Controller Methods
1. `dashboardKpis()` - Consolidates all numerical data
2. `dashboardGraphs()` - Consolidates all chart data
3. Helper methods for calculations shared between both endpoints

### Database Optimization
- Efficient queries with proper joins
- Single data fetch per endpoint
- Optimized calculations for multiple metrics

### Error Handling
- Comprehensive try-catch blocks
- Meaningful error messages
- Graceful degradation for missing data

## Chart Library Compatibility

All graph data is formatted to be compatible with popular chart libraries:

- **Flutter:** `fl_chart` package
- **Web:** Chart.js, D3.js, Recharts
- **React Native:** Victory Native, react-native-chart-kit

## Example Usage

### Flutter Implementation
```dart
// Get KPI data
final kpiResponse = await http.get('/api/farm-analysis/123/dashboard-kpis');
final kpis = jsonDecode(kpiResponse.body)['data']['kpis'];

// Get graph data
final graphResponse = await http.get('/api/farm-analysis/123/dashboard-graphs');
final graphs = jsonDecode(graphResponse.body)['data'];

// Use data for dashboard widgets
buildKpiCards(kpis);
buildMilkTrendChart(graphs['milk_trend']);
buildSpeciesPieChart(graphs['species_demographics']);
```

## Migration from Old Endpoints

### Old Endpoints (DEPRECATED)
- ❌ `/api/farm-analysis/{farm_id}/kpi-grid`
- ❌ `/api/farm-analysis/{farm_id}/milk-trend`
- ❌ `/api/farm-analysis/{farm_id}/reproduction-funnel`
- ❌ `/api/farm-analysis/{farm_id}/demographics`

### New Endpoints (ACTIVE)
- ✅ `/api/farm-analysis/{farm_id}/dashboard-kpis`
- ✅ `/api/farm-analysis/{farm_id}/dashboard-graphs`

---

**Status:** ✅ **IMPLEMENTED AND READY FOR USE**

**Last Updated:** January 27, 2025

**Contact:** GitHub Copilot - Farm Analytics API Team
