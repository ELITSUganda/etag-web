# Butchery Workflow API Documentation
**Date:** 26 December 2025  
**Version:** 5.0.150+

## Architecture Overview

### Data Hierarchy
```
Carcass (SlaughterRecord)
├── Quarters (SlaughterDistributionRecord)
│   └── source_id = carcass.id
│   └── source_address contains "Fore-1/4" or "Hind-1/4"
└── Cuts (SlaughterDistributionRecord)
    ├── source_id = carcass.id (DIRECTLY to carcass, NOT quarter)
    ├── cut_type = "Prime" or "Offal"
    └── source_address = "Prime - {cut_name}" or "Offal - {cut_name}"
```

### Key Design Principles
1. **Cuts belong directly to carcass** - `source_id` always points to the carcass ID
2. **Quarters belong to carcass** - `source_id` points to carcass ID
3. **Cut types** - Identified by `cut_type` field ("Prime" or "Offal")
4. **Filtering** - Use `source_address` and `cut_type` to distinguish quarters from cuts

---

## Backend Updates

### 1. SlaughterDistributionRecord Model

**File:** `/app/Models/SlaughterDistributionRecord.php`

#### New Fillable Fields
```php
protected $fillable = [
    'animal_id',
    'slaughterhouse_id',
    'created_by_id',
    'source_type',
    'source_id',           // ID of carcass (SlaughterRecord)
    'source_name',         // Cut name (e.g., "T-Bone", "Ribeye")
    'source_address',      // "Prime - {name}" or "Offal - {name}"
    'source_phone',
    'receiver_type',
    'receiver_id',
    'receiver_name',
    'receiver_address',
    'receiver_phone',
    'lhc',
    'v_id',
    'e_id',
    'animal_owner_id',
    'bar_code',
    'qr_code',
    'post_fat',
    'post_grade',
    'post_animal',
    'post_age',
    'original_weight',
    'current_weight',
    'price',
    'slaughter_date',
    'cut_type',            // "Prime" or "Offal"
];
```

#### New Helper Methods

**Check if record is a quarter:**
```php
public function isQuarter()
{
    $address = strtolower($this->source_address ?? '');
    return str_contains($address, 'fore-1/4') || str_contains($address, 'hind-1/4');
}
```

**Check if record is a cut:**
```php
public function isCut()
{
    return in_array($this->cut_type, ['Prime', 'Offal']);
}
```

**Get all quarters for a carcass:**
```php
SlaughterDistributionRecord::getQuartersForCarcass($carcassId)
```

**Get all cuts for a carcass:**
```php
SlaughterDistributionRecord::getCutsForCarcass($carcassId, $cutType = null)
// $cutType: 'Prime', 'Offal', or null for all cuts
```

---

### 2. API Controller Updates

**File:** `/app/Http/Controllers/ApiAnimalController.php`

#### Modified: `create_slaughter_distribution_record`

**Key Changes:**
1. Detects if record is a cut by checking `cut_type` parameter
2. For cuts, `source_id` always points to carcass (not quarter)
3. Weight deduction from carcass (not quarter) for cuts
4. Accepts `source_name` parameter for cut names

**Request Parameters:**
```json
{
    "source_id": 34,              // Carcass ID (SlaughterRecord.id)
    "source_name": "T-Bone",      // Cut name
    "source_address": "Prime - T-Bone",
    "cut_type": "Prime",          // "Prime" or "Offal"
    "original_weight": 15,
    "receiver_id": 1              // Optional
}
```

**Example: Creating a Quarter**
```json
{
    "source_id": 34,
    "source_name": "Fore-1/4 - Left",
    "source_address": "Fore-1/4 - Left",
    "original_weight": 50
}
```

**Example: Creating a Prime Cut**
```json
{
    "source_id": 34,
    "source_name": "Ribeye",
    "source_address": "Prime - Ribeye",
    "cut_type": "Prime",
    "original_weight": 12
}
```

**Example: Creating an Offal Cut**
```json
{
    "source_id": 34,
    "source_name": "Liver",
    "source_address": "Offal - Liver",
    "cut_type": "Offal",
    "original_weight": 5
}
```

---

## API Endpoints

### 1. Get All Distribution Records
**Endpoint:** `GET /api/slaughter-distributions`  
**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "status": 1,
    "message": "Success.",
    "data": [
        {
            "id": 84,
            "source_id": "34",
            "source_name": "T-Bone",
            "source_address": "Prime - T-Bone",
            "cut_type": "Prime",
            "original_weight": "15",
            "current_weight": "15",
            "v_id": "000004005",
            "e_id": "000000004005",
            "created_at": "2025-12-26T01:28:58.000000Z"
        }
    ]
}
```

### 2. Create Distribution Record
**Endpoint:** `POST /api/create-slaughter-distribution-record`  
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
    "source_id": 34,
    "source_name": "Ribeye",
    "source_address": "Prime - Ribeye",
    "cut_type": "Prime",
    "original_weight": 12
}
```

**Success Response:**
```json
{
    "status": 1,
    "message": "Record saved successfully.",
    "data": {
        "sr": { /* SlaughterRecord object */ },
        "sdr": { /* SlaughterDistributionRecord object */ },
        "source": null
    }
}
```

---

## Mobile App Integration

### SlaughterDistributionRecordModel (Flutter/Dart)

**File:** `/lib/model/SlaughterDistributionRecordModel.dart`

#### Property Mapping

| Mobile Property | Backend Column | Type | Description |
|----------------|----------------|------|-------------|
| `source_id` | `source_id` | String | Carcass ID |
| `source_name` | `source_name` | String | Cut/Quarter name |
| `source_address` | `source_address` | String | Full address with type |
| `cut_type` | `cut_type` | String | "Prime" or "Offal" |
| `original_weight` | `original_weight` | String | Initial weight |
| `current_weight` | `current_weight` | String | Current weight |
| `v_id` | `v_id` | String | Visual ID |
| `e_id` | `e_id` | String | Electronic ID |

#### Creating Quarters (Mobile)
```dart
final model = SlaughterDistributionRecordModel();
model.source_id = carcass.id.toString();
model.source_name = "Fore-1/4 - Left";
model.source_address = "Fore-1/4 - Left";
model.original_weight = "50";
model.current_weight = "50";
model.v_id = carcass.v_id;
model.e_id = carcass.e_id;
await model.save();
```

#### Creating Prime Cuts (Mobile)
```dart
final model = SlaughterDistributionRecordModel();
model.source_id = carcass.id.toString();  // CARCASS ID, not quarter
model.source_name = "T-Bone";
model.source_address = "Prime - T-Bone";
model.cut_type = "Prime";
model.original_weight = "15";
model.current_weight = "15";
model.v_id = carcass.v_id;
model.e_id = carcass.e_id;
await model.save();
```

#### Creating Offal Cuts (Mobile)
```dart
final model = SlaughterDistributionRecordModel();
model.source_id = carcass.id.toString();  // CARCASS ID, not quarter
model.source_name = "Liver";
model.source_address = "Offal - Liver";
model.cut_type = "Offal";
model.original_weight = "5";
model.current_weight = "5";
model.v_id = carcass.v_id;
model.e_id = carcass.e_id;
await model.save();
```

#### Loading and Filtering (Mobile)
```dart
// Load all distributions for a carcass
final allRecords = await SlaughterDistributionRecordModel.getLocalData(
    where: "source_id = '${carcass.id}'"
);

// Filter quarters
final quarters = allRecords.where((rec) =>
    rec.source_address.toLowerCase().contains('fore-1/4') ||
    rec.source_address.toLowerCase().contains('hind-1/4')
).toList();

// Filter prime cuts
final primeCuts = allRecords.where((rec) =>
    rec.cut_type.toLowerCase() == 'prime' ||
    rec.source_address.toLowerCase().contains('prime')
).toList();

// Filter offal cuts
final offalCuts = allRecords.where((rec) =>
    rec.cut_type.toLowerCase() == 'offal' ||
    rec.source_address.toLowerCase().contains('offal')
).toList();
```

---

## Cut Types Reference

### Prime Cuts (14 types)
1. Beef boneless
2. Beef Stew
3. Bones
4. Chops
5. Chuck ribs
6. Family Steak
7. Ground Beef
8. Ribeye
9. Sirloin
10. T-Bone
11. Tenderloin
12. Brisket
13. Short Ribs
14. Flank Steak

### Offal Cuts (8 types)
1. Heart
2. Kidneys
3. Liver
4. Tongue
5. Brain
6. Tripe
7. Tail
8. Head

---

## Testing

### Backend Test
**File:** `test-butchery-workflow.php`

**Run:** 
```bash
cd /Applications/MAMP/htdocs/etag-web
php test-butchery-workflow.php
```

**Test Coverage:**
- ✓ Creates 4 quarters
- ✓ Creates 4 prime cuts
- ✓ Creates 4 offal cuts
- ✓ Verifies all cuts reference carcass ID
- ✓ Tests data retrieval methods
- ✓ Validates architecture consistency

**Expected Output:**
```
✓ All cuts correctly reference carcass ID: 34
✓ Architecture validation PASSED
```

### Mobile Test
**Screen:** `ButcheryWorkflowScreen.dart`

**Test Flow:**
1. Select carcass → Progress: 1/4
2. Create 4 quarters → Progress: 2/4
3. Select and create prime cuts → Progress: 3/4
4. Select and create offal cuts → Progress: 4/4

---

## Migration History

1. **2023_12_03_174734** - Create slaughter_distribution_records table
   - Added base fields: source_id, source_name, source_address, etc.

2. **2025_12_16_110711** - Add cut_type column
   - Added `cut_type` field for distinguishing Prime/Offal cuts

---

## Consistency Checklist

- ✅ Backend model updated with fillable fields
- ✅ Backend API supports cut_type parameter
- ✅ Backend API uses carcass ID for cuts
- ✅ Mobile model properties match backend columns
- ✅ Mobile app creates correct data structure
- ✅ Filtering logic works on both sides
- ✅ Test script validates architecture
- ✅ Documentation complete

---

## Common Pitfalls to Avoid

❌ **DON'T** use `name`, `address`, `type` - these don't exist!  
✅ **DO** use `source_name`, `source_address`, `cut_type`

❌ **DON'T** set cut's source_id to quarter ID  
✅ **DO** set cut's source_id to carcass ID

❌ **DON'T** forget to set cut_type for cuts  
✅ **DO** set cut_type to "Prime" or "Offal"

❌ **DON'T** filter cuts by quarter relationship  
✅ **DO** filter cuts by cut_type and source_address

---

## Status: ✅ COMPLETE

**Backend:** Fully implemented and tested  
**Mobile:** Fully implemented with ButcheryWorkflowScreen  
**Integration:** Properties aligned, data flows correctly  
**Testing:** Backend test passing, mobile ready for testing  
**Documentation:** Complete with examples
