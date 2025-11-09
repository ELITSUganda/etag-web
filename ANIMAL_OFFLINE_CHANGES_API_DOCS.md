# Animal Offline Changes API - Complete Implementation Guide

## Overview
Complete backend API implementation for handling animal bulk changes with offline-first architecture. This system allows users to make bulk changes to multiple animals, store them locally, and sync to the server when online.

## API Response Format Standard

All API endpoints follow a standardized response format:

**Success Response:**
```json
{
  "code": "1",
  "message": "Operation description",
  "data": { ... }
}
```

**Error Response:**
```json
{
  "code": "0",
  "message": "Error description",
  "data": null
}
```

**Field Descriptions:**
- **code**: Operation result - `"1"` for success, `"0"` for failure
- **message**: Human-readable message describing the operation result
- **data**: Response payload (varies by endpoint), or `null` for errors
- **data.status**: (When applicable) Record status - `pending`, `processing`, `synced`, or `failed`

**Important Note:** The `code` field indicates the API operation result, while `data.status` (when present) indicates the state of a change record. These are different concepts.

## Database Schema

### New Columns Added to `animals` Table
```sql
ALTER TABLE animals ADD COLUMN current_worth DECIMAL(15, 2) NULL;
ALTER TABLE animals ADD COLUMN conception_method VARCHAR(255) NULL;
ALTER TABLE animals ADD COLUMN sire_id BIGINT UNSIGNED NULL;
ALTER TABLE animals ADD FOREIGN KEY (sire_id) REFERENCES animals(id) ON DELETE SET NULL;
```

**Note:** `parent_id` already existed in the table.

### Column Descriptions
- **current_worth**: The current market value of the animal in local currency (UGX)
- **conception_method**: How the animal was conceived (`Natural`, `AI`, or `ET`)
- **sire_id**: Foreign key to the male parent animal (father)
- **parent_id**: General parent reference (can be used for either parent)

## API Endpoints

### 1. Create Offline Change
**Endpoint:** `POST /api/animal-offline-changes`

**Description:** Creates a new offline change record that will be processed later.

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "local_id": "unique-local-id-123",
  "animal_ids": [1, 2, 3, 4],
  "change_type": "change_dob",
  "change_data": {
    "dob": "2023-06-15"
  },
  "timestamp": 1699545600
}
```

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| local_id | string | Yes | Unique identifier for this change (UUID recommended) |
| animal_ids | array | Yes | Array of animal IDs to apply changes to |
| animal_ids.* | integer | Yes | Individual animal ID |
| change_type | string | Yes | Type of change (see Change Types below) |
| change_data | object | Yes | Data for the change (varies by type) |
| timestamp | integer | Yes | Unix timestamp when change was created |

**Success Response (201):**
```json
{
  "code": "1",
  "message": "Change submitted and processed successfully",
  "data": {
    "id": 1,
    "local_id": "unique-local-id-123",
    "animal_ids": "[1,2,3,4]",
    "change_type": "change_dob",
    "change_data": "{\"dob\":\"2023-06-15\"}",
    "changed_by_user_id": 5,
    "processing_by_user_id": 5,
    "timestamp": 1699545600,
    "status": "synced",
    "error_message": null,
    "processed_at": 1699545600,
    "created_at": "2025-11-09T10:30:00.000000Z",
    "updated_at": "2025-11-09T10:30:00.000000Z"
  }
}
```

**Response Field Descriptions:**
- **code**: Operation result (`"1"` = success, `"0"` = failure)
- **message**: Human-readable message describing the operation result
- **data.status**: Change record status (`pending`, `processing`, `synced`, or `failed`)
- **data.error_message**: Error details if status is `failed`, null otherwise
- **data.processed_at**: Unix timestamp when change was processed, null if not yet processed

**Error Responses:**

**Validation Failed (400):**
```json
{
  "code": "0",
  "message": "Validation failed: {errors}",
  "data": null
}
```

**Duplicate Submission (200):**
```json
{
  "code": "1",
  "message": "Change already submitted",
  "data": {...}
}
```

### 2. Get User's Offline Changes
**Endpoint:** `GET /api/animal-offline-changes`

**Description:** Retrieves all offline changes for the authenticated user.

**Query Parameters:**
| Parameter | Type | Optional | Description |
|-----------|------|----------|-------------|
| status | string | Yes | Filter by status: `pending`, `processing`, `synced`, `failed` |
| limit | integer | Yes | Maximum records to return (default: 100) |

**Success Response (200):**
```json
{
  "code": "1",
  "message": "Success. Count: 5",
  "data": [
    {
      "id": 1,
      "local_id": "unique-local-id-123",
      "animal_ids": "[1,2,3,4]",
      "change_type": "change_dob",
      "change_data": "{\"dob\":\"2023-06-15\"}",
      "status": "synced",
      ...
    },
    ...
  ]
}
```

### 3. Process Offline Change
**Endpoint:** `POST /api/animal-offline-changes/process/{id}`

**Description:** Processes a pending offline change and applies it to the animals. **Note:** Changes are now auto-processed on submission, so this endpoint is rarely needed.

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | ID of the offline change record |

**Success Response (200):**
```json
{
  "code": "1",
  "message": "Successfully updated 4 animal(s)",
  "data": {
    "id": 1,
    "status": "synced",
    "processed_at": 1699545700,
    "error_message": null,
    ...
  }
}
```

**Error Response (400):**
```json
{
  "code": "0",
  "message": "Failed to process change: {error_details}",
  "data": {
    "id": 1,
    "status": "failed",
    "error_message": "Date of birth cannot be in the future",
    ...
  }
}
```

### 4. Delete Offline Change
**Endpoint:** `DELETE /api/animal-offline-changes/{id}`

**Description:** Deletes a pending or failed offline change record.

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | ID of the offline change record |

**Success Response (200):**
```json
{
  "code": "1",
  "message": "Change deleted successfully",
  "data": null
}
```

**Error Response (403):**
```json
{
  "status": 0,
  "code": 0,
  "message": "Cannot delete synced change",
  "data": null
}
```

## Change Types & Validation

### 1. Change Farm
**Type:** `change_farm`

**Change Data:**
```json
{
  "farm_id": 5
}
```

**Validation:**
- `farm_id` must be provided
- All animals must belong to the user
- Animals will be moved to the specified farm

### 2. Change Group
**Type:** `change_group`

**Change Data:**
```json
{
  "group_id": 3
}
```

**Validation:**
- `group_id` must be provided
- All animals must belong to the user

### 3. Change Status
**Type:** `change_status`

**Change Data:**
```json
{
  "status": "Active"
}
```

**Valid Status Values:**
- `Active`
- `Sold`
- `Dead`
- `Lost`
- `Stolen`
- `Transferred`

**Validation:**
- `status` must be provided
- All animals must belong to the user

### 4. Change Date of Birth (NEW)
**Type:** `change_dob`

**Change Data:**
```json
{
  "dob": "2023-06-15"
}
```

**Validation:**
- `dob` must be provided
- Format must be `YYYY-MM-DD`
- Date cannot be in the future
- Updates the `dob` column in animals table

### 5. Change Breed
**Type:** `change_breed`

**Change Data:**
```json
{
  "breed": "Holstein"
}
```

**Validation:**
- `breed` must be provided
- All animals must belong to the user

### 6. Change Sex
**Type:** `change_sex`

**Change Data:**
```json
{
  "sex": "Male"
}
```

**Valid Sex Values:**
- `Male`
- `Female`

**Validation:**
- `sex` must be provided

### 7. Change Type
**Type:** `change_type`

**Change Data:**
```json
{
  "type": "Dairy"
}
```

**Valid Type Values:**
- `Beef`
- `Dairy`
- `Dual Purpose`
- `Breeding`
- `Draught`
- `Other`

### 8. Change Current Worth (NEW)
**Type:** `change_worth`

**Change Data:**
```json
{
  "current_worth": "5000000"
}
```

**Validation:**
- `current_worth` must be provided
- Must be a positive number (>= 0)
- Stored as DECIMAL(15, 2) in database
- Represents value in local currency (UGX)

### 9. Change Conception Method (NEW)
**Type:** `change_conception`

**Change Data:**
```json
{
  "conception_method": "AI"
}
```

**Valid Conception Methods:**
- `Natural` - Traditional natural mating
- `AI` - Artificial Insemination
- `ET` - Embryo Transfer

**Validation:**
- `conception_method` must be provided
- Must be one of the valid values
- Updates the `conception_method` column

### 10. Change Sire/Parent (NEW)
**Type:** `change_sire`

**Change Data:**
```json
{
  "sire_id": 42
}
```

**Validation:**
- `sire_id` must be provided
- Sire animal must exist in database
- Sire animal must be Male
- Updates both `sire_id` and `parent_id` columns
- Foreign key constraint ensures data integrity

### 11. Change E-ID (Deprecated)
**Type:** `change_e_id`

**Note:** This change type requires individual mapping for each animal to ensure uniqueness.

**Change Data:**
```json
{
  "e_id_map": {
    "1": "UGA001234567",
    "2": "UGA001234568",
    "3": "UGA001234569"
  }
}
```

### 12. Change V-ID (Deprecated)
**Type:** `change_v_id`

**Change Data:**
```json
{
  "v_id_map": {
    "1": "V-001",
    "2": "V-002",
    "3": "V-003"
  }
}
```

## Status Workflow

```
┌─────────┐
│ pending │ ← Initial state when change is created
└────┬────┘
     │
     ↓
┌────────────┐
│ processing │ ← Set when process() is called
└─────┬──────┘
      │
      ├──→ ┌────────┐
      │    │ synced │ ← Successfully processed
      │    └────────┘
      │
      └──→ ┌────────┐
           │ failed │ ← Error during processing
           └────────┘
              │
              ↓ (can retry)
           ┌────────┐
           │pending │
           └────────┘
```

## Error Handling

### Common Error Scenarios

**1. Animal Not Found or Unauthorized:**
```json
{
  "success": false,
  "message": "Some animals not found or do not belong to user"
}
```

**2. Invalid Date Format:**
```json
{
  "success": false,
  "message": "Invalid date format. Expected YYYY-MM-DD"
}
```

**3. Future Date:**
```json
{
  "success": false,
  "message": "Date of birth cannot be in the future"
}
```

**4. Invalid Worth Value:**
```json
{
  "success": false,
  "message": "Current worth must be a positive number"
}
```

**5. Invalid Conception Method:**
```json
{
  "success": false,
  "message": "Invalid conception method. Must be Natural, AI, or ET"
}
```

**6. Invalid Sire:**
```json
{
  "success": false,
  "message": "Sire animal not found"
}
```

**7. Sire Not Male:**
```json
{
  "success": false,
  "message": "Sire must be a male animal"
}
```

## Security Considerations

### 1. Authentication
- All endpoints require Bearer token authentication
- User ID is extracted from authenticated token
- Users can only modify their own animals

### 2. Authorization
- Animals are verified to belong to the requesting user
- Only `pending` or `failed` changes can be deleted
- `synced` changes are immutable

### 3. Data Validation
- All input is validated before processing
- Type checking ensures data integrity
- Foreign key constraints prevent orphaned records

### 4. Transaction Safety
- All change processing uses database transactions
- Rollback on any error ensures data consistency
- Error messages are logged for debugging

## Performance Optimization

### 1. Batch Updates
- Uses `whereIn()` for bulk updates where possible
- Reduces database queries
- Processes multiple animals in single transaction

### 2. Indexing Recommendations
```sql
-- Index on user_id for faster filtering
CREATE INDEX idx_offline_changes_user ON animal_offline_changes(changed_by_user_id);

-- Index on status for filtering
CREATE INDEX idx_offline_changes_status ON animal_offline_changes(status);

-- Index on sire_id for relationship queries
CREATE INDEX idx_animals_sire ON animals(sire_id);

-- Index on parent_id
CREATE INDEX idx_animals_parent ON animals(parent_id);
```

### 3. Query Optimization
- Limit results with `limit()` parameter
- Use status filtering to reduce result set
- Order by created_at DESC for recent changes first

## Mobile Integration

### Offline Storage Flow
1. User makes changes while offline
2. Changes saved to local SQLite database
3. When online, app calls `/api/animal-offline-changes` (POST)
4. Server responds with success/duplicate
5. App marks local change as synced
6. Background sync checks for pending changes

### Conflict Resolution
- Uses `local_id` (UUID) to prevent duplicate submissions
- Server returns existing record if `local_id` already exists
- App can safely retry failed submissions

### Sync Strategy
```dart
// Pseudo-code for mobile app
Future<void> syncOfflineChanges() async {
  final pendingChanges = await getLocalPendingChanges();
  
  for (final change in pendingChanges) {
    try {
      final response = await api.post('/api/animal-offline-changes', change);
      if (response['status'] == 1) {
        await markLocalChangeAsSynced(change.localId);
      }
    } catch (e) {
      // Retry later
      await markLocalChangeAsFailed(change.localId, e.toString());
    }
  }
}
```

## Testing Guide

### 1. Unit Tests
Test each change type individually:

```bash
# Test DOB change
curl -X POST http://localhost:8000/api/animal-offline-changes \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "local_id": "test-dob-123",
    "animal_ids": [1, 2],
    "change_type": "change_dob",
    "change_data": {"dob": "2023-06-15"},
    "timestamp": 1699545600
  }'
```

### 2. Integration Tests
Test complete workflow:

```bash
# 1. Create change
CHANGE_ID=$(curl -X POST ... | jq -r '.data.id')

# 2. Process change
curl -X POST http://localhost:8000/api/animal-offline-changes/process/$CHANGE_ID \
  -H "Authorization: Bearer {token}"

# 3. Verify animals updated
curl -X GET http://localhost:8000/api/animals/1 \
  -H "Authorization: Bearer {token}"
```

### 3. Edge Case Tests
- Empty animal_ids array
- Non-existent animal IDs
- Mixed ownership (some owned, some not)
- Invalid data formats
- Boundary values (future dates, negative worth, etc.)
- Duplicate local_id
- Concurrent updates

## Monitoring & Logging

### Key Metrics to Track
- Number of pending changes per user
- Success rate of change processing
- Average processing time per change type
- Most common failure reasons
- Peak sync times

### Logging Recommendations
```php
// In controller
Log::info('Animal offline change created', [
    'user_id' => $user_id,
    'change_type' => $request->change_type,
    'animal_count' => count($request->animal_ids),
    'local_id' => $request->local_id
]);

Log::error('Change processing failed', [
    'change_id' => $change->id,
    'change_type' => $change->change_type,
    'error' => $e->getMessage()
]);
```

## Migration Checklist

- [x] Create migration file
- [x] Add new columns to animals table
- [x] Update Animal model fillable array
- [x] Implement controller changes
- [x] Add validation for new change types
- [x] Test all change types
- [ ] Update API documentation
- [ ] Deploy to staging
- [ ] Run integration tests
- [ ] Deploy to production
- [ ] Monitor for errors

## Troubleshooting

### Common Issues

**Issue:** "Sire animal not found"
- **Solution:** Verify sire_id exists in animals table and is a valid male animal

**Issue:** "Date of birth cannot be in the future"
- **Solution:** Check client device time settings, validate date before sending

**Issue:** "Current worth must be a positive number"
- **Solution:** Ensure worth is formatted as numeric string or number, not currency formatted

**Issue:** Foreign key constraint violation
- **Solution:** Ensure sire_id references valid animal ID before updating

## Future Enhancements

### Potential Improvements
1. Batch processing API to process multiple changes at once
2. Webhook notifications when changes are processed
3. Change history tracking (audit log)
4. Rollback capability for synced changes
5. Scheduled processing for off-peak hours
6. Rate limiting per user
7. Change preview before processing
8. Bulk import from CSV/Excel

## Support & Maintenance

### API Version
- **Current Version:** v1.0
- **Release Date:** November 9, 2025
- **Compatibility:** Requires mobile app v5.0.150+

### Contact
- **Documentation:** Internal wiki
- **Bug Reports:** Project issue tracker
- **Questions:** Development team channel

---

**Last Updated:** November 9, 2025  
**Author:** Backend Development Team  
**Status:** ✅ Production Ready
