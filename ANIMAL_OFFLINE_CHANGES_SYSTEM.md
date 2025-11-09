# Animal Offline Change Tracking System - Implementation Summary

**Date:** November 9, 2025  
**Purpose:** Enable offline bulk changes to animals with server synchronization

## Overview
This system allows users to make bulk changes to multiple animals while offline, queue those changes locally, and sync them to the server when online. Changes are tracked individually and can be retried if they fail.

## System Architecture

### 1. Database Layer

#### Online Database (MySQL)
**Table:** `animal_offline_changes`

**Migration:** `2025_11_09_000001_create_animal_offline_changes_table.php`

**Fields:**
- `id` - Auto-increment primary key
- `local_id` - Unique identifier from mobile (indexed)
- `animal_ids` - JSON array of animal IDs affected
- `change_type` - Type of change (e.g., change_group, change_farm)
- `change_data` - JSON data for the change
- `changed_by_user_id` - User who made the change (indexed)
- `processing_by_user_id` - User processing the change (nullable)
- `status` - Current status (pending, processing, synced, failed)
- `error_message` - Error if sync failed (nullable)
- `timestamp` - When change was made on mobile
- `processed_at` - When successfully processed (nullable)
- `created_at`, `updated_at` - Laravel timestamps

#### Local Database (SQLite)
**Table:** `animal_offline_changes`

**Fields:**
- `id` - Auto-increment primary key
- `local_id` - Unique identifier (from Utils.getUniqueText())
- `animal_ids` - JSON array of animal IDs
- `change_type` - Type of change
- `change_data` - JSON data for the change
- `timestamp` - When change was made
- `changed_by_user_id` - User who made the change
- `status` - pending, synced, failed
- `error_message` - Error message if failed
- `created_at` - When record was created locally

### 2. Backend Components

#### Model: `AnimalOfflineChange.php`
**Location:** `/app/Models/AnimalOfflineChange.php`

**Key Methods:**
- `getAnimalIdsArray()` - Decode JSON to array
- `getChangeDataArray()` - Decode JSON to array
- `setAnimalIdsArray($ids)` - Encode array to JSON
- `setChangeDataArray($data)` - Encode array to JSON
- `changedBy()` - Relationship to User
- `processingBy()` - Relationship to User
- `scopePending($query)` - Query pending changes
- `scopeFailed($query)` - Query failed changes
- `scopeSynced($query)` - Query synced changes
- `scopeForUser($query, $userId)` - Query user's changes

#### Controller: `AnimalOfflineChangeController.php`
**Location:** `/app/Http/Controllers/AnimalOfflineChangeController.php`

**Endpoints:**

1. **POST `/api/animal-offline-changes`** - Create new change
   - Validates: local_id, animal_ids array, change_type, change_data, timestamp
   - Checks for duplicate local_id
   - Returns created change record

2. **GET `/api/animal-offline-changes`** - Get user's changes
   - Optional `status` filter (pending, synced, failed)
   - Optional `limit` parameter (default 100)
   - Returns array of changes

3. **POST `/api/animal-offline-changes/process/{id}`** - Process a change
   - Marks as 'processing'
   - Applies change to animals
   - Updates status to 'synced' or 'failed'
   - Returns updated change record

4. **DELETE `/api/animal-offline-changes/{id}`** - Delete change
   - Only pending or failed changes can be deleted
   - Returns success message

**Supported Change Types:**
- `change_farm` - Change farm_id for multiple animals
- `change_group` - Change group_id for multiple animals
- `change_status` - Change status for multiple animals
- `change_e_id` - Change electronic ID (individual mapping required)
- `change_v_id` - Change visual ID (individual mapping required)
- `change_breed` - Change breed for multiple animals
- `change_sex` - Change sex for multiple animals
- `change_type` - Change type for multiple animals

### 3. Mobile App Components

#### Model: `AnimalOfflineChange.dart`
**Location:** `/lib/model/AnimalOfflineChange.dart`

**Key Methods:**

**Data Management:**
- `fromJson(dynamic m)` - Create from JSON
- `toJson()` - Convert to JSON
- `getAnimalIdsArray()` - Get animal IDs as List<int>
- `setAnimalIdsArray(List<int> ids)` - Set animal IDs from list
- `getChangeDataMap()` - Get change data as Map
- `setChangeDataMap(Map data)` - Set change data from map

**Database Operations:**
- `save()` - Save to local database
- `update()` - Update in local database
- `delete()` - Delete from local database
- `initTable()` - Initialize table structure
- `getItems({where})` - Get items with optional filter
- `getPendingItems()` - Get pending changes
- `getFailedItems()` - Get failed changes
- `deleteSynced()` - Delete all synced records

**Synchronization:**
- `submitToServer()` - Submit single change to server
- `syncAll()` - Sync all pending and failed changes
- `createChange({...})` - Create a new offline change

**Display Helpers:**
- `getChangeTypeDisplay()` - Human-readable change type
- `getStatusDisplay()` - Human-readable status

## Usage Flow

### Creating Offline Changes

```dart
// Create a change to update farm for multiple animals
AnimalOfflineChange change = await AnimalOfflineChange.createChange(
  animalIds: [1, 2, 3, 4, 5],
  changeType: 'change_farm',
  changeData: {'farm_id': '10'},
  userId: currentUserId,
);
```

### Syncing Changes

```dart
// Sync all pending changes
Map<String, int> result = await AnimalOfflineChange.syncAll();
print('Success: ${result['success']}, Failed: ${result['failed']}');

// Or sync individual change
bool success = await change.submitToServer();
```

### Viewing Changes

```dart
// Get pending changes
List<AnimalOfflineChange> pending = await AnimalOfflineChange.getPendingItems();

// Get failed changes
List<AnimalOfflineChange> failed = await AnimalOfflineChange.getFailedItems();

// Get all user changes
List<AnimalOfflineChange> all = await AnimalOfflineChange.getItems();
```

## Change Type Specifications

### 1. change_farm
**Change Data Structure:**
```json
{
  "farm_id": "10"
}
```
**Effect:** Updates `farm_id` for all specified animals

### 2. change_group
**Change Data Structure:**
```json
{
  "group_id": "5"
}
```
**Effect:** Updates `group_id` for all specified animals

### 3. change_status
**Change Data Structure:**
```json
{
  "status": "Active"
}
```
**Effect:** Updates `status` for all specified animals
**Valid Values:** Active, Sold, Dead, Lost, etc.

### 4. change_e_id
**Change Data Structure:**
```json
{
  "e_id_map": {
    "1": "NEW-E-ID-001",
    "2": "NEW-E-ID-002",
    "3": "NEW-E-ID-003"
  }
}
```
**Effect:** Updates `e_id` individually for each animal
**Note:** Validates uniqueness before applying

### 5. change_v_id
**Change Data Structure:**
```json
{
  "v_id_map": {
    "1": "NEW-V-ID-001",
    "2": "NEW-V-ID-002"
  }
}
```
**Effect:** Updates `v_id` individually for each animal

### 6. change_breed
**Change Data Structure:**
```json
{
  "breed": "Holstein"
}
```
**Effect:** Updates `breed` for all specified animals

### 7. change_sex
**Change Data Structure:**
```json
{
  "sex": "Male"
}
```
**Effect:** Updates `sex` for all specified animals
**Valid Values:** Male, Female

### 8. change_type
**Change Data Structure:**
```json
{
  "type": "Cattle"
}
```
**Effect:** Updates `type` for all specified animals

## Error Handling

### Validation Errors
- Missing required fields
- Invalid data types
- Duplicate local_id

### Processing Errors
- Animals not found
- Animals don't belong to user
- Duplicate e_id when changing IDs
- Missing required data in change_data

### Sync Errors
- Network connection issues
- Server errors
- Permission issues

**Failed changes:**
- Status set to 'failed'
- Error message stored
- Can be retried later
- User notified of failure

## Next Steps

### Phase 1: UI Implementation (Planned)
1. **Animal List Multi-Select**
   - Long press to enable multi-select mode
   - Checkbox selection
   - Select all / Deselect all
   - Count of selected animals

2. **Bulk Actions Bottom Sheet**
   - Show after animals selected
   - Options:
     * Change Farm
     * Change Group
     * Change Status
     * Change Breed
     * Change Sex
     * Change Type
     * Change E-ID (individual)
     * Change V-ID (individual)

3. **Change Dialog/Form**
   - Select new value based on change type
   - Preview affected animals
   - Confirm action
   - Show success/pending status

4. **Offline Changes List Screen**
   - List all pending/failed changes
   - Show status (pending/failed)
   - Retry failed changes
   - Delete failed changes
   - View error messages
   - Auto-sync on connection

### Phase 2: Sync Strategy (Planned)
1. **Auto-Sync**
   - Check for pending changes on app start
   - Sync when network detected
   - Background sync periodically

2. **Manual Sync**
   - Sync button in changes list
   - Pull to refresh
   - Retry individual failures

3. **Conflict Resolution**
   - Timestamp-based resolution
   - Last write wins
   - User notification on conflicts

## Testing Checklist

- [ ] Migration runs successfully
- [ ] Model relationships work
- [ ] API endpoints return correct responses
- [ ] Local database table creates successfully
- [ ] Changes save locally
- [ ] Changes sync to server
- [ ] Failed changes retry successfully
- [ ] Synced changes delete locally
- [ ] Multiple change types process correctly
- [ ] E-ID uniqueness validation works
- [ ] User permissions enforced
- [ ] Error messages display correctly

## Files Modified/Created

### Backend
- ✅ `/database/migrations/2025_11_09_000001_create_animal_offline_changes_table.php`
- ✅ `/app/Models/AnimalOfflineChange.php`
- ✅ `/app/Http/Controllers/AnimalOfflineChangeController.php`
- ✅ `/routes/api.php` (added routes)

### Mobile App
- ✅ `/lib/model/AnimalOfflineChange.dart`

### Documentation
- ✅ This file

## Status
✅ **Phase 1 Complete** - Database, Models, API Endpoints, Local Model created
⏳ **Phase 2 Pending** - UI implementation for multi-select and bulk actions
⏳ **Phase 3 Pending** - Sync logic and conflict resolution
⏳ **Phase 4 Pending** - Testing and refinement
