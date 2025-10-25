# Events Paginated API - Complete Documentation

## Endpoint Information

**URL:** `/api/events-paginated`  
**Method:** `GET`  
**Authentication:** Required (Bearer Token)  
**Controller:** `ApiAnimalController@events_online`

## Overview

This endpoint retrieves paginated, filtered events with optimized performance. It uses raw SQL queries and supports multiple filter combinations for flexible event searching.

---

## Request Parameters

### Pagination Parameters

| Parameter | Type | Default | Min | Max | Description |
|-----------|------|---------|-----|-----|-------------|
| `page` | integer | 1 | 1 | 10000 | Current page number |
| `per_page` | integer | 25 | 5 | 50 | Number of items per page |

### Filter Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `search` | string | No | Search across e_id, v_id, type, description, detail (max 200 chars) | `"vaccination"` |
| `event_type` | string | No | Exact match on event type (min 2 chars, max 100) | `"Treatment"` |
| `category` | string | No | Filter by category: `sanitary` or `production` | `"sanitary"` |
| `animal_id` | integer | No | Filter by specific animal ID | `1234` |
| `e_id` | string | No | Partial match on event ID (max 100 chars) | `"E-123"` |
| `v_id` | string | No | Partial match on visit ID (max 100 chars) | `"V-456"` |
| `date_from` | string | No | Start date in YYYY-MM-DD format | `"2025-01-01"` |
| `date_to` | string | No | End date in YYYY-MM-DD format | `"2025-12-31"` |

### Category Values

**Sanitary Events Include:**
- Treatment
- Vaccination
- Batch Treatment
- Temperature check
- Death
- Disease test
- Disease
- Abortion
- Sample taken
- Sample result
- Test conducted
- Test result
- Mortality

**Production Events:**
All other event types not listed above.

---

## Response Format

### Success Response (Status: 200)

```json
{
  "status": 1,
  "message": "Success. Retrieved 25 events.",
  "data": [
    {
      "id": 12345,
      "animal_id": 678,
      "type": "Vaccination",
      "detail": "Annual vaccination completed",
      "description": "Administered FMD vaccine to animal",
      "short_description": "FMD vaccination",
      "created_at": "2025-10-25 14:30:00",
      "updated_at": "2025-10-25 14:30:00",
      "created_at_formatted": "Oct 25, 2025 14:30",
      "updated_at_formatted": "Oct 25, 2025 14:30",
      "time_ago": "2 hours ago",
      "administrator_id": 10,
      "farm_id": 5,
      "disease_id": null,
      "vaccine_id": 3,
      "medicine_id": null,
      "medicine_text": null,
      "medicine_quantity": null,
      "medicine_name": null,
      "weight": null,
      "milk": null,
      "temperature": null,
      "e_id": "E-2025-001",
      "v_id": "V-2025-045",
      "status": "completed",
      "vaccination": "FMD",
      "photo": "https://example.com/photo.jpg",
      "is_present": 1,
      "price": 15000,
      "animal_text": null,
      "animal_photo": null,
      "farm_text": null,
      "administrator_text": null,
      "session_text": null
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 25,
    "total": 150,
    "last_page": 6,
    "has_more": true,
    "from": 1,
    "to": 25
  },
  "filters_applied": {
    "search": "",
    "event_type": null,
    "category": "",
    "animal_id": 0,
    "e_id": "",
    "v_id": "",
    "date_from": "",
    "date_to": ""
  }
}
```

### Error Responses

#### Authentication Error (Status: 200, status: 0)
```json
{
  "status": 0,
  "message": "User not authenticated. Please login again.",
  "data": [],
  "error_code": "AUTH_REQUIRED"
}
```

#### Database Error (Status: 200, status: 0)
```json
{
  "status": 0,
  "message": "Failed to retrieve farm access permissions.",
  "data": [],
  "error_code": "DATABASE_ERROR"
}
```

#### No Farms Warning (Status: 200, status: 1)
```json
{
  "status": 1,
  "message": "No accessible farms found. Please contact administrator.",
  "data": [],
  "pagination": {
    "current_page": 1,
    "per_page": 25,
    "total": 0,
    "last_page": 1,
    "has_more": false
  },
  "warning": "NO_FARMS_ACCESSIBLE"
}
```

#### Unexpected Error (Status: 200, status: 0)
```json
{
  "status": 0,
  "message": "An unexpected error occurred. Please try again later.",
  "data": [],
  "error_code": "UNEXPECTED_ERROR"
}
```

---

## Data Fields Description

### Core Event Fields

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | integer | No | Unique event record ID |
| `animal_id` | integer | Yes | Associated animal ID |
| `type` | string | No | Event type (e.g., "Vaccination", "Treatment") |
| `detail` | text | Yes | Detailed notes about the event |
| `description` | text | Yes | Event description |
| `short_description` | string | Yes | Brief summary of event |
| `created_at` | datetime | No | Event creation timestamp |
| `updated_at` | datetime | No | Last update timestamp |
| `e_id` | string | Yes | Event identification code |
| `v_id` | string | Yes | Visit identification code |
| `status` | string | Yes | Event status |

### Medical Fields

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `disease_id` | integer | Yes | Associated disease ID |
| `vaccine_id` | integer | Yes | Vaccine used (if vaccination event) |
| `medicine_id` | integer | Yes | Medicine/drug ID |
| `medicine_name` | string | Yes | Name of medicine used |
| `medicine_text` | text | Yes | Medicine details/notes |
| `medicine_quantity` | decimal | Yes | Amount of medicine administered |
| `vaccination` | string | Yes | Vaccination type/name |

### Measurement Fields

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `weight` | decimal | Yes | Animal weight in kg |
| `milk` | decimal | Yes | Milk production in liters |
| `temperature` | decimal | Yes | Body temperature in °C |

### Administrative Fields

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `administrator_id` | integer | Yes | User who created the event |
| `farm_id` | integer | No | Farm where event occurred |
| `price` | decimal | Yes | Cost/price associated with event |
| `photo` | string | Yes | Event photo URL |
| `is_present` | boolean | Yes | Animal presence status (1=present, 0=absent) |

### Computed Fields (Not in Database)

| Field | Type | Description |
|-------|------|-------------|
| `created_at_formatted` | string | Human-readable creation date (e.g., "Oct 25, 2025 14:30") |
| `updated_at_formatted` | string | Human-readable update date |
| `time_ago` | string | Relative time (e.g., "2 hours ago", "3 days ago") |
| `animal_text` | string/null | Animal name/description (currently null, placeholder) |
| `animal_photo` | string/null | Animal photo URL (currently null, placeholder) |
| `farm_text` | string/null | Farm name (currently null, placeholder) |
| `administrator_text` | string/null | Administrator name (currently null, placeholder) |
| `session_text` | string/null | Session info (currently null, placeholder) |

---

## Usage Examples

### Example 1: Basic Pagination
```bash
GET /api/events-paginated?page=1&per_page=25
```

### Example 2: Search Events
```bash
GET /api/events-paginated?search=vaccination&page=1
```

### Example 3: Filter by Event Type
```bash
GET /api/events-paginated?event_type=Treatment&page=1
```

### Example 4: Filter by Category
```bash
GET /api/events-paginated?category=sanitary&page=1
```

### Example 5: Filter by Animal
```bash
GET /api/events-paginated?animal_id=1234&page=1
```

### Example 6: Date Range Filter
```bash
GET /api/events-paginated?date_from=2025-01-01&date_to=2025-12-31&page=1
```

### Example 7: Combined Filters
```bash
GET /api/events-paginated?category=sanitary&animal_id=1234&date_from=2025-01-01&page=1&per_page=50
```

---

## Performance Characteristics

- **Query Optimization:** Uses raw SQL with prepared statements
- **Response Time:** < 2 seconds for most queries
- **Max Records per Request:** 50
- **Farm Access:** Uses UNION query for efficient permission checking
- **Indexing:** Leverages database indexes on `farm_id`, `created_at`, `animal_id`

---

## Security Features

1. **Authentication Required:** All requests must include valid bearer token
2. **Farm Access Control:** Users only see events from farms they have access to
3. **Input Validation:** All parameters are validated and sanitized
4. **SQL Injection Prevention:** Uses prepared statements with parameter binding
5. **Rate Limiting:** Page limit of 10,000 to prevent abuse
6. **String Length Limits:** Enforces maximum lengths on search/filter strings
7. **Date Format Validation:** Strict YYYY-MM-DD format validation
8. **Category Whitelist:** Only allows specific category values

---

## Error Handling

The API includes comprehensive error handling:

1. **Database Connection Errors:** Logged and returns DATABASE_ERROR
2. **Query Execution Errors:** Logged and returns QUERY_ERROR
3. **Data Processing Errors:** Individual events skipped, others returned
4. **Unexpected Errors:** Caught and logged with UNEXPECTED_ERROR
5. **Authentication Errors:** Returns AUTH_REQUIRED

All errors are logged to Laravel's log files with detailed messages.

---

## Frontend Integration Notes

### Handling Pagination
```javascript
// Check if more pages exist
if (response.pagination.has_more) {
    // Load next page
    nextPage = response.pagination.current_page + 1;
}
```

### Handling Empty Results
```javascript
if (response.data.length === 0 && response.status === 1) {
    // Show "No events found" message
    if (response.warning === 'NO_FARMS_ACCESSIBLE') {
        // Show "Contact administrator" message
    }
}
```

### Displaying Time
Use `time_ago` for recent events and `created_at_formatted` for older events.

### Error Handling
```javascript
if (response.status === 0) {
    switch(response.error_code) {
        case 'AUTH_REQUIRED':
            // Redirect to login
            break;
        case 'DATABASE_ERROR':
        case 'QUERY_ERROR':
        case 'UNEXPECTED_ERROR':
            // Show error message and retry option
            break;
    }
}
```

---

## Change Log

### Version 1.0 (October 2025)
- Initial optimized implementation
- Raw SQL queries for performance
- Comprehensive error handling
- Input validation and security measures
- Support for 8 filter parameters
- Pagination with has_more indicator

---

## Support

For technical issues or questions:
- Check Laravel logs at `storage/logs/laravel.log`
- Review error_code in API response
- Contact backend team with specific error details

---

## Related Endpoints

- `/api/animals-v2` - Get user's animals for filter dropdown
- `/api/events-v4` - Alternative events endpoint with incremental sync
- `/api/events-online` - Legacy endpoint (may be blocked by WAF)
