# Farm Analytics Dashboard - Graph Implementation Plan

## Overview
This document outlines the implementation strategy for farmer-facing analytics graphs based on our database analysis and the `FarmGraphsDashboardScreen` template.

## Implementation Strategy

### Phase 1: Core Analytics (Immediate Implementation)
**Target: 4 Essential Graphs - All data available in current schema**

### Phase 2: Enhanced Analytics (Short-term Enhancement) 
**Target: 3 Partially implementable graphs - Require minor data restructuring**

### Phase 3: Advanced Intelligence (Future Development)
**Target: 3 Complex graphs - Require new modules/AI capabilities**

---

## PHASE 1: CORE ANALYTICS (✅ READY TO IMPLEMENT)

### 1. KPI Dashboard Grid (8 Key Metrics)
**Endpoint:** `GET /api/farm-analysis/{farm_id}/kpi-grid`

**Metrics to Calculate:**
- **Total Animals:** Count from `animals` table where `deleted_at IS NULL`
- **Lactating Cows:** Unique `animal_id` from `events` where `type = 'Milk Production'` in last 30 days
- **Daily Milk Production:** Sum of `events.milk` for today + average per lactating cow
- **Conception Rate:** (Pregnant animals / Total services) × 100 from `pregnant_animals` + `events`
- **Disease Incidence:** Cases per 100 animals per month from `sick_animals` + `events`
- **Vaccination Coverage:** % of eligible animals vaccinated from `farm_vaccination_records`
- **Mortality Rate:** Deaths in last 12 months from `archived_animals` where `last_event = 'Mortality'`
- **Data Quality Score:** % of animals with complete tags (`e_id`, `v_id`) and recent events

**Response Format:**
```json
{
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
  "vaccination_due": 18
}
```

### 2. Milk Production Trend Chart
**Endpoint:** `GET /api/farm-analysis/{farm_id}/milk-trend?days=30`

**Data Source:** `events` table where `type = 'Milk Production'`
**Calculations:**
- Daily total milk production
- 7-day moving average
- Individual cow performance trends
- Peak/low production identification

**Response Format:**
```json
{
  "period": "30_days",
  "total_production": 26750.5,
  "average_daily": 891.7,
  "trend_direction": "increasing",
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
```

### 3. Reproduction Performance Funnel
**Endpoint:** `GET /api/farm-analysis/{farm_id}/reproduction-funnel`

**Data Sources:** 
- `events` where `type IN ('AI', 'Natural mating')`
- `pregnant_animals` table
- `events` where `pregnancy_check_results IS NOT NULL`

**Funnel Stages:**
1. **Services Given:** Total AI + Natural mating events
2. **Pregnancy Checks Done:** Animals with PD results
3. **Currently Pregnant:** Active pregnancies
4. **Due for Calving:** Expected calvings in next 60 days

**Response Format:**
```json
{
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
```

### 4. Herd Demographics Analysis
**Endpoint:** `GET /api/farm-analysis/{farm_id}/demographics`

**Data Source:** `animals` table with age calculations
**Breakdowns:**
- Species distribution (Cattle, Goats, Sheep, Pigs)
- Age band analysis (0-6m, 6-12m, 12-24m, >24m)
- Gender distribution
- Breed composition

**Response Format:**
```json
{
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
    {"breed": "Ankole", "count": 38},
    {"breed": "Crossbred", "count": 44}
  ]
}
```

---

## PHASE 2: ENHANCED ANALYTICS (⚠️ PARTIAL DATA AVAILABLE)

### 5. Animal Growth & Weight Distribution
**Endpoint:** `GET /api/farm-analysis/{farm_id}/growth-analysis`

**Current Limitation:** `events.weight` data is inconsistent
**Implementation Strategy:** Show weight distribution and identify animals with multiple weight records

### 6. Disease Occurrence Patterns  
**Endpoint:** `GET /api/farm-analysis/{farm_id}/disease-heatmap`

**Current Limitation:** Disease data scattered across tables
**Implementation Strategy:** Aggregate disease events by month and type

### 7. Vaccination Coverage Details
**Endpoint:** `GET /api/farm-analysis/{farm_id}/vaccination-coverage`

**Current Limitation:** Individual animal vaccination history incomplete
**Implementation Strategy:** Farm-level coverage with aggregate statistics

---

## PHASE 3: ADVANCED INTELLIGENCE (❌ FUTURE DEVELOPMENT)

### 8. Smart Inventory Management
**Requirement:** Real-time drug stock tracking with predictive analytics

### 9. AI-Powered Alerts System
**Requirement:** Machine learning models for anomaly detection

### 10. Data Sync & Quality Dashboard
**Requirement:** Offline-first architecture with sync monitoring

---

## API DESIGN PRINCIPLES

### 1. Consistent Response Structure
```json
{
  "success": true,
  "data": { /* graph data */ },
  "metadata": {
    "farm_id": 123,
    "generated_at": "2025-09-01T10:30:00Z",
    "data_freshness": "live",
    "cache_ttl": 300
  },
  "filters_applied": {
    "date_range": "last_30_days",
    "animal_types": ["cattle", "goats"]
  }
}
```

### 2. Smart Caching Strategy
- KPI data: Cache for 5 minutes
- Trend data: Cache for 15 minutes  
- Demographics: Cache for 1 hour
- Historical data: Cache for 24 hours

### 3. Performance Optimization
- Use indexed queries on `farm_id`, `created_at`, `animal_id`
- Implement database query result caching
- Add pagination for large datasets
- Use database aggregation functions

### 4. Error Handling
- Graceful degradation when data is missing
- Clear error messages for farmers
- Fallback to cached data when possible

---

## DATABASE OPTIMIZATION RECOMMENDATIONS

### Indexes to Add:
```sql
-- Critical for performance
CREATE INDEX idx_events_farm_type_date ON events(farm_id, type, created_at);
CREATE INDEX idx_animals_farm_deleted ON animals(farm_id, deleted_at);
CREATE INDEX idx_pregnant_animals_status ON pregnant_animals(current_status, farm_id);
CREATE INDEX idx_archived_animals_farm_event ON archived_animals(farm_id, last_event);
```

### Query Optimization:
- Use `WHERE deleted_at IS NULL` for active animals
- Leverage `created_at` for date range filtering
- Use `DISTINCT animal_id` for unique animal counts
- Implement proper JOIN strategies

---

## IMPLEMENTATION TIMELINE

### Week 1: Foundation
- [ ] Extend FarmAnalysisController with graph endpoints
- [ ] Implement KPI Grid calculations
- [ ] Add caching mechanism

### Week 2: Core Graphs  
- [ ] Milk Production Trend API
- [ ] Reproduction Funnel API
- [ ] Demographics Analysis API

### Week 3: Integration & Testing
- [ ] Flutter integration with new endpoints
- [ ] Performance testing and optimization
- [ ] Error handling and edge cases

### Week 4: Enhancement
- [ ] Phase 2 graphs (partial implementation)
- [ ] Advanced filtering options
- [ ] Documentation and farmer training

---

## SUCCESS METRICS

### Technical Metrics:
- API response time < 500ms
- 99.9% uptime
- Cache hit ratio > 80%
- Database query optimization

### Business Metrics:
- Farmer engagement with analytics
- Data-driven decision making
- Improved farm performance tracking
- User satisfaction scores

---

## SECURITY & PRIVACY

### Data Protection:
- Farm-specific data isolation
- User authentication for API access
- Audit logging for sensitive operations
- GDPR compliance for farmer data

### Performance Security:
- Rate limiting on API endpoints
- Input validation and sanitization
- SQL injection prevention
- Secure caching mechanisms

---

This implementation plan provides a clear roadmap from basic analytics to advanced intelligence, ensuring we build upon our existing strong data foundation while planning for future enhancements.
