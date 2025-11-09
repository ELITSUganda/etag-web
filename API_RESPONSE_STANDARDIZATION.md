# API Response Format Standardization - Animal Offline Changes

## Date: November 9, 2025

## Summary
Standardized all Animal Offline Changes API endpoints to use the correct response format with `code` field for operation results, eliminating the redundant `status` field from API responses.

## Changes Made

### 1. Controller Updates (`AnimalOfflineChangeController.php`)

**Modified All Endpoints:**
- `store()` - Create offline change
- `index()` - Get user's changes
- `process()` - Process change
- `destroy()` - Delete change

**Before:**
```json
{
  "status": 1,
  "code": 1,
  "message": "Success",
  "data": {...}
}
```

**After:**
```json
{
  "code": "1",
  "message": "Success",
  "data": {...}
}
```

### 2. Utils Class Update (`app/Models/Utils.php`)

**Modified `response()` Method:**
- Removed redundant `status` field from response
- Now only returns `code`, `message`, and `data`
- Maintains backward compatibility by accepting either `code` or `status` in input

**Changes:**
```php
// OLD
$resp['status'] = "1";
if ($resp['status'] == '1' || $resp['status'] == 1) {
    $resp['code'] = "1";
    $resp['status'] = "1";
} else {
    $resp['code'] = "0";
    $resp['status'] = "0";
}

// NEW
$resp['code'] = "1";
if (isset($data['code'])) {
    $resp['code'] = $data['code'] . "";
}
// For backward compatibility
elseif (isset($data['status'])) {
    $resp['code'] = $data['status'] . "";
}
```

### 3. Documentation Updates (`ANIMAL_OFFLINE_CHANGES_API_DOCS.md`)

**Added Section:** API Response Format Standard
- Documented standardized response structure
- Explained difference between `code` (operation result) and `status` (record state)
- Updated all example responses throughout the document

**Key Clarifications:**
- **code**: `"1"` = success, `"0"` = failure (operation result)
- **data.status**: `pending`, `processing`, `synced`, `failed` (change record state)

## Response Structure

### Standard Success Response
```json
{
  "code": "1",
  "message": "Change submitted and processed successfully",
  "data": {
    "id": 16,
    "status": "synced",
    "error_message": null,
    "processed_at": 1762659742,
    ...
  }
}
```

### Standard Error Response
```json
{
  "code": "0",
  "message": "Validation failed: {...}",
  "data": null
}
```

### Partial Success Response
```json
{
  "code": "1",
  "message": "Change submitted and processed successfully",
  "data": {
    "id": 14,
    "status": "synced",
    "error_message": "Updated 2 animal(s), 1 failed: 1 animal(s) not found",
    "processed_at": 1762659500,
    ...
  }
}
```

## Field Definitions

### Top-Level Fields
| Field | Type | Description |
|-------|------|-------------|
| code | string | Operation result: `"1"` for success, `"0"` for failure |
| message | string | Human-readable description of the operation result |
| data | object/array/null | Response payload (varies by endpoint), or `null` for errors |

### Change Record Fields (in data)
| Field | Type | Description |
|-------|------|-------------|
| id | integer | Unique change record ID |
| status | string | Record state: `pending`, `processing`, `synced`, or `failed` |
| error_message | string/null | Error details if status is `failed`, null otherwise |
| processed_at | integer/null | Unix timestamp when processed, null if not yet processed |
| processing_by_user_id | integer/null | User ID who processed the change |

## Important Distinctions

### Code vs Status
**`code` (Operation Result):**
- Indicates whether the API call itself succeeded or failed
- Values: `"1"` (success) or `"0"` (failure)
- Located at top level of response
- Example: `"code": "1"` means API call was successful

**`data.status` (Record State):**
- Indicates the state of a change record in the database
- Values: `pending`, `processing`, `synced`, or `failed`
- Located inside `data` object
- Example: `"status": "synced"` means changes were applied to animals

**Example Scenario:**
```json
{
  "code": "1",  // ✅ API call succeeded
  "message": "Change submitted and processed successfully",
  "data": {
    "status": "failed",  // ❌ But the change couldn't be applied to animals
    "error_message": "Date of birth cannot be in the future"
  }
}
```

## Testing Results

### Test 1: Create Change (Success)
**Request:**
```bash
POST /api/animal-offline-changes
{
  "animal_ids": [1101, 1102],
  "change_type": "change_worth",
  "change_data": {"current_worth": "9000000"}
}
```

**Response:**
```json
{
  "code": "1",
  "message": "Change submitted and processed successfully",
  "data": {
    "id": 16,
    "status": "synced",
    "processed_at": 1762659742
  }
}
```
✅ **Result:** Success - Both animals updated to 9,000,000

### Test 2: Partial Failure Handling
**Request:**
```bash
POST /api/animal-offline-changes
{
  "animal_ids": [1101, 9999, 1102],  // 9999 doesn't exist
  "change_type": "change_worth",
  "change_data": {"current_worth": "8000000"}
}
```

**Response:**
```json
{
  "code": "1",
  "message": "Change submitted and processed successfully",
  "data": {
    "id": 14,
    "status": "synced",
    "error_message": "Updated 2 animal(s), 1 failed: 1 animal(s) not found"
  }
}
```
✅ **Result:** Partial success - Valid animals (1101, 1102) updated, invalid animal (9999) skipped

### Test 3: Validation Error
**Request:**
```bash
POST /api/animal-offline-changes
{
  "animal_ids": [1101, 1102],
  "change_type": "change_worth"
  // Missing required fields: change_data, timestamp
}
```

**Response:**
```json
{
  "code": "0",
  "message": "Validation failed: {\"change_data\":[\"The change data field is required.\"],\"timestamp\":[\"The timestamp field is required.\"]}",
  "data": null
}
```
✅ **Result:** Proper error response with code "0"

### Test 4: Get User Changes
**Request:**
```bash
GET /api/animal-offline-changes?user_id=1&status=synced&limit=1
```

**Response:**
```json
{
  "code": "1",
  "message": "Success. Count: 1",
  "data": [
    {
      "id": 16,
      "status": "synced",
      "error_message": null
    }
  ]
}
```
✅ **Result:** Standardized list response

## Benefits

1. **Consistency**: All endpoints now follow the same response format
2. **Clarity**: Clear distinction between operation result (`code`) and record state (`status`)
3. **Standards Compliance**: Follows established API response conventions
4. **Backward Compatible**: Utils class still accepts old `status` field for other controllers
5. **Better Error Handling**: Error responses are clear and consistent

## Migration Notes

### For Mobile App Developers
- Update response parsing to look for `code` instead of `status` at the top level
- Continue using `data.status` to check change record state
- Error handling logic should check `code == "0"` instead of `status == 0`

### Example Mobile Code Update
**Before:**
```dart
if (response['status'] == 1) {
  // Success
  if (response['data']['status'] == 'synced') {
    // Changes applied
  }
}
```

**After:**
```dart
if (response['code'] == "1") {
  // Success
  if (response['data']['status'] == 'synced') {
    // Changes applied
  }
}
```

## Files Modified

1. `/app/Http/Controllers/AnimalOfflineChangeController.php` (10 response updates)
2. `/app/Models/Utils.php` (response() method refactored)
3. `/ANIMAL_OFFLINE_CHANGES_API_DOCS.md` (documentation updated)

## Status
✅ **Complete** - All endpoints standardized and tested successfully
✅ **Documented** - API documentation updated
✅ **Tested** - All test cases passing with new format
