# 📋 BATCH UPDATE OFFLINE CHANGES - COMPREHENSIVE IMPLEMENTATION GUIDE

> **Document Status**: PENDING IMPLEMENTATION  
> **Last Updated**: November 9, 2025  
> **Database**: etag (MySQL)  
> **Backend**: Laravel 8.x - AnimalOfflineChangeController  
> **Mobile**: Flutter - AnimalOfflineChange Model  

---

## 📊 CURRENT IMPLEMENTATION STATUS

### ✅ ALREADY IMPLEMENTED (8 Change Types)

These change types are **already working** in production:

| Change Type | Field(s) Updated | Validation | Notes |
|------------|------------------|------------|-------|
| `change_farm` | `farm_id` | Farm must exist | Bulk update supported |
| `change_group` | `group_id` | Group must exist | Bulk update supported |
| `change_status` | `status` | Text validation | Values: Active, Sold, Dead, etc. |
| `change_dob` | `dob` | Date format YYYY-MM-DD, not future | Bulk update supported |
| `change_worth` | `current_worth` | Positive number | Bulk update supported |
| `change_conception` | `conception_method` | Must be: Natural, AI, or ET | Bulk update supported |
| `change_mother` | `parent_id`, `has_parent` | Female animal only, prevents self-reference | **Recently implemented** |
| `change_sire` | `sire_id`, `parent_id` | Male animal only | Bulk update supported |

---

## 🎯 RECOMMENDED NEW IMPLEMENTATIONS

Based on **actual database schema analysis** and **existing event types**, here are the recommended batch update features organized by priority.

---

## 🔥 PHASE 1: ESSENTIAL ANIMAL ATTRIBUTES (High Priority)

### 1. `change_color` - Update Animal Color
**Database Field**: `animals.color` (TEXT, NULL)  
**Current Usage**: Most animals have NULL color  
**Implementation**: Bulk update  
**Validation**: Text input, 2-50 characters  
**Change Data Structure**:
```json
{
  "color": "Brown with white patches"
}
```
**Use Case**: Farmers need to describe animal appearance for identification

---

### 2. `change_breed` - Update Animal Breed  
**Database Field**: `animals.breed` (TEXT, NULL)  
**Current Usage**: Many animals have NULL breed  
**Implementation**: Bulk update  
**Validation**: Text input, common breeds list for suggestions  
**Change Data Structure**:
```json
{
  "breed": "Friesian"
}
```
**Common Breeds**: Friesian, Ayrshire, Jersey, Ankole, Boran, Cross-breed  
**Note**: Already exists in AnimalOfflineChangeController.php but **NOT in mobile model**

---

### 3. `change_type` - Change Animal Species
**Database Field**: `animals.type` (VARCHAR(255), NULL)  
**Current Values**: Cattle, Goat, Sheep, Pig  
**Implementation**: Bulk update  
**Validation**: Must be one of accepted types  
**Change Data Structure**:
```json
{
  "type": "Cattle"
}
```
**Note**: Already exists in controller but **NOT in mobile model**

---

### 4. `change_sex` - Correct Animal Sex
**Database Field**: `animals.sex` (TEXT, NULL)  
**Current Values**: Male, Female  
**Implementation**: Bulk update  
**Validation**: Must be "Male" or "Female"  
**Change Data Structure**:
```json
{
  "sex": "Female"
}
```
**Use Case**: Correct initial registration errors  
**Note**: Already exists in controller but **NOT in mobile model**

---

### 5. `change_lhc` - Update Location Holding Code
**Database Field**: `animals.lhc` (TEXT, NULL)  
**Implementation**: Bulk update  
**Validation**: Must match farm's holding code format  
**Change Data Structure**:
```json
{
  "lhc": "UG-001-002-003"
}
```
**Use Case**: When farm LHC changes or correction needed

---

### 6. `change_e_id` - Update Electronic ID (Individual)
**Database Field**: `animals.e_id` (TEXT, NULL)  
**Implementation**: Individual update (uniqueness check)  
**Validation**: Must be unique across system  
**Change Data Structure**:
```json
{
  "e_id_map": {
    "123": "UG1234567890",
    "456": "UG0987654321"
  }
}
```
**Note**: Already in controller but **NOT in mobile**

---

### 7. `change_v_id` - Update Visual ID (Individual)
**Database Field**: `animals.v_id` (TEXT, NULL)  
**Implementation**: Individual update (uniqueness check)  
**Validation**: Must be unique across system  
**Change Data Structure**:
```json
{
  "v_id_map": {
    "123": "V-001",
    "456": "V-002"
  }
}
```
**Note**: Already in controller but **NOT in mobile**

---

### 8. `change_weight` - Update Current Weight
**Database Field**: `animals.weight` (FLOAT, NULL, default 0)  
**Implementation**: Bulk update  
**Validation**: Positive number, reasonable range (10-1500 kg)  
**Change Data Structure**:
```json
{
  "weight": 450.5
}
```
**Use Case**: Quick weight updates without creating full Weight Check event  
**Note**: Should ideally create Weight Check event for history

---

## 🏥 PHASE 2: HEALTH & MEDICAL STATUS (High Priority)

### 9. `change_pregnancy_status` - Update Pregnancy Status
**Database Fields**: 
- `animals.is_pregnant` (VARCHAR(255), NULL, default "No")
- `animals.pregnancy_delivery_expected_date` (DATE, NULL)
- `animals.service_date` (DATE, NULL)

**Implementation**: Bulk update (females only)  
**Validation**: 
- Animal must be female
- Date must be valid and in future
**Change Data Structure**:
```json
{
  "is_pregnant": "Yes",
  "pregnancy_delivery_expected_date": "2025-07-15",
  "service_date": "2024-10-20"
}
```
**Use Case**: Quick pregnancy status updates after vet checks

---

### 10. `mark_sick` - Mark Animal as Sick
**Related Table**: `sick_animals`  
**Implementation**: Creates sick_animals record  
**Validation**: Disease ID or description required  
**Change Data Structure**:
```json
{
  "disease_id": 5,
  "disease_text": "Foot and Mouth Disease",
  "test_results": "Positive",
  "details": "Shows symptoms of FMD",
  "current_results": "Under treatment"
}
```
**Use Case**: Quick health status marking during field visits

---

### 11. `mark_healthy` - Mark Animal as Healthy/Recovered
**Related Table**: `sick_animals`  
**Implementation**: Updates or removes sick_animals record  
**Change Data Structure**:
```json
{
  "current_results": "Recovered",
  "recovery_notes": "Fully recovered after treatment"
}
```

---

### 12. `update_fmd_vaccination` - Update FMD Vaccination Status
**Database Fields**:
- `animals.fmd` (TEXT, NULL) - Last FMD vaccination date
- `animals.has_fmd` (VARCHAR(255), NULL, default "No")

**Implementation**: Bulk update  
**Validation**: Date format YYYY-MM-DD  
**Change Data Structure**:
```json
{
  "fmd": "2025-01-15",
  "has_fmd": "Yes"
}
```
**Use Case**: Mass FMD vaccination campaigns

---

## 🐄 PHASE 3: BREEDING & REPRODUCTION (Medium Priority)

### 13. `change_service_type` - Update Service Type
**Database Fields**:
- `animals.service_type` (VARCHAR(255), NULL)
- `animals.conception_method` (VARCHAR(255), NULL)

**Implementation**: Bulk update  
**Validation**: Must be one of: Natural, AI, ET  
**Change Data Structure**:
```json
{
  "service_type": "AI",
  "conception_method": "AI"
}
```
**Note**: `conception_method` already implemented

---

### 14. `record_service_date` - Record Service/Mating Date
**Database Field**: `animals.service_date` (DATE, NULL)  
**Implementation**: Bulk update (females only)  
**Validation**: Date format, not in future  
**Change Data Structure**:
```json
{
  "service_date": "2025-01-10"
}
```
**Use Case**: Quick service recording

---

### 15. `update_sire_info` - Update Sire Information
**Database Fields**:
- `animals.sire_id` (VARCHAR(255), NULL)
- `animals.sire_e_id` (VARCHAR(255), NULL)
- `animals.semen_number` (TEXT, NULL)

**Implementation**: Bulk update  
**Validation**: Sire must exist if sire_id provided  
**Change Data Structure**:
```json
{
  "sire_id": 123,
  "sire_e_id": "UG1234567890",
  "semen_number": "SEM-2025-001"
}
```
**Use Case**: AI service records

---

### 16. `change_calving_stage` - Update Calving Stage
**Database Field**: `animals.stage` (VARCHAR(55), NULL, default "Other")  
**Current Auto-Calculated**: Yes (from DOB and sex)  
**Implementation**: Manual override option  
**Validation**: Must be one of valid stages  
**Possible Values**:
- Calf, Weaner, Heifer, Bullock, Bull, Cow, Steer, Other

**Change Data Structure**:
```json
{
  "stage": "Heifer"
}
```
**Note**: Usually auto-calculated, manual override for special cases

---

### 17. `update_weaning_status` - Update Weaning Status
**Database Fields**:
- `animals.is_weaned_off` (VARCHAR(255), NULL, default "No")
- `animals.wean_off_weight` (DOUBLE(8,2), NULL)
- `animals.wean_off_age` (DOUBLE(8,2), NULL)

**Implementation**: Bulk update (calves only)  
**Validation**: Reasonable age and weight values  
**Change Data Structure**:
```json
{
  "is_weaned_off": "Yes",
  "wean_off_weight": 80.5,
  "wean_off_age": 6.5
}
```
**Use Case**: Bulk weaning operations

---

### 18. `update_calf_status` - Update Calf Status
**Database Field**: `animals.is_a_calf` (VARCHAR(255), NULL, default "No")  
**Implementation**: Bulk update  
**Change Data Structure**:
```json
{
  "is_a_calf": "No"
}
```
**Use Case**: Transition calf to adult status

---

## 💰 PHASE 4: ECONOMIC & SALES (Medium Priority)

### 19. `mark_for_sale` - Mark Animal for Sale
**Database Fields**:
- `animals.for_sale` (TINYINT(1), NULL, default 0)
- `animals.price` (INT(11), NULL, default 0)

**Implementation**: Bulk update  
**Validation**: Price must be positive  
**Change Data Structure**:
```json
{
  "for_sale": 1,
  "price": 1500000
}
```
**Use Case**: Prepare animals for market

---

### 20. `update_current_price` - Update Market Value
**Database Fields**:
- `animals.current_price` (INT(11), NULL)
- `animals.current_worth` (DECIMAL(15,2), NULL)

**Implementation**: Bulk update  
**Validation**: Positive numbers  
**Change Data Structure**:
```json
{
  "current_price": 1800000,
  "current_worth": 1800000.00
}
```
**Note**: `current_worth` already implemented

---

### 21. `record_purchase_info` - Record Purchase Information
**Database Fields**:
- `animals.was_purchases` (VARCHAR(250), NULL) - "Yes" or "No"
- `animals.purchase_date` (VARCHAR(250), NULL)
- `animals.purchase_from` (VARCHAR(250), NULL)
- `animals.purchase_price` (INT(11), NULL)

**Implementation**: Bulk update  
**Validation**: Date format, seller info  
**Change Data Structure**:
```json
{
  "was_purchases": "Yes",
  "purchase_date": "2024-05-10",
  "purchase_from": "Kampala Livestock Market",
  "purchase_price": 1200000
}
```
**Use Case**: Import/purchase documentation

---

## 📍 PHASE 5: LOCATION & ORGANIZATION (Low-Medium Priority)

### 22. `change_district` - Update District
**Database Field**: `animals.district_id` (BIGINT(20) UNSIGNED, NULL, default 1)  
**Implementation**: Bulk update  
**Validation**: District must exist in locations table  
**Change Data Structure**:
```json
{
  "district_id": 102
}
```
**Note**: Usually auto-updated with farm change

---

### 23. `change_sub_county` - Update Sub-County
**Database Field**: `animals.sub_county_id` (BIGINT(20) UNSIGNED, NULL, default 1)  
**Implementation**: Bulk update  
**Validation**: Sub-county must exist in locations table  
**Change Data Structure**:
```json
{
  "sub_county_id": 1002007
}
```
**Note**: Usually auto-updated with farm change

---

### 24. `change_parish` - Update Parish
**Database Field**: `animals.parish_id` (BIGINT(20) UNSIGNED, NULL, default 1)  
**Implementation**: Bulk update  
**Validation**: Parish must exist in locations table  
**Change Data Structure**:
```json
{
  "parish_id": 3045
}
```
**Note**: Usually auto-updated with farm change

---

## 📝 PHASE 6: ADDITIONAL INFORMATION (Low Priority)

### 25. `update_comments` - Update Comments/Notes
**Database Field**: `animals.comments` (TEXT, NULL)  
**Implementation**: Bulk update  
**Validation**: Text length limit (1000 chars)  
**Change Data Structure**:
```json
{
  "comments": "This animal shows good growth rate. Recommended for breeding program."
}
```

---

### 26. `update_genetic_info` - Update Genetic Information
**Database Field**: `animals.genetic_donor` (VARCHAR(250), NULL)  
**Implementation**: Bulk update  
**Change Data Structure**:
```json
{
  "genetic_donor": "Elite Bull #123 - High Milk Production Line"
}
```

---

### 27. `update_birth_info` - Update Birth Information
**Database Fields**:
- `animals.weight_at_birth` (INT(11), NULL)
- `animals.birth_position` (INT(11), NULL) - Birth order

**Implementation**: Bulk update  
**Validation**: Reasonable weight range (15-70 kg for cattle)  
**Change Data Structure**:
```json
{
  "weight_at_birth": 35,
  "birth_position": 1
}
```

---

### 28. `update_performance_metrics` - Update Performance Tracking
**Database Fields**:
- `animals.has_produced_before` (VARCHAR(255), NULL, default "No")
- `animals.age_at_first_calving` (INT(11), NULL)
- `animals.weight_at_first_calving` (DOUBLE(8,2), NULL)
- `animals.has_been_inseminated` (VARCHAR(255), NULL, default "No")
- `animals.age_at_first_insemination` (INT(11), NULL)
- `animals.weight_at_first_insemination` (DOUBLE(8,2), NULL)
- `animals.inter_calving_interval` (INT(11), NULL) - Days between calvings
- `animals.calf_mortality_rate` (DOUBLE(8,2), NULL)
- `animals.weight_gain_per_day` (DOUBLE(8,2), NULL)
- `animals.number_of_isms_per_conception` (DOUBLE(8,2), NULL)

**Implementation**: Bulk update (complex data structure)  
**Use Case**: Research, breeding program tracking  
**Change Data Structure**:
```json
{
  "has_produced_before": "Yes",
  "age_at_first_calving": 28,
  "weight_at_first_calving": 420.5,
  "inter_calving_interval": 395,
  "weight_gain_per_day": 0.75
}
```

---

### 29. `update_milk_production` - Update Milk Production Info
**Database Field**: `animals.average_milk` (FLOAT, NULL)  
**Implementation**: Bulk update  
**Validation**: Positive number, reasonable range (0-60 liters/day)  
**Change Data Structure**:
```json
{
  "average_milk": 18.5
}
```
**Note**: Usually calculated from milking events

---

### 30. `update_profile_status` - Update Profile Completeness
**Database Fields**:
- `animals.has_more_info` (VARCHAR(10), NULL)
- `animals.profile_updated` (VARCHAR(255), NULL, default "No")
- `animals.last_profile_update_date` (DATETIME, NULL)

**Implementation**: Auto-updated by system  
**Change Data Structure**:
```json
{
  "has_more_info": "Yes",
  "profile_updated": "Yes",
  "last_profile_update_date": "2025-11-09 14:30:00"
}
```

---

### 31. `update_movement_info` - Update Movement/Transfer Info
**Database Fields**:
- `animals.trader` (BIGINT(20), NULL, default 0)
- `animals.destination` (TEXT, NULL)
- `animals.destination_slaughter_house` (INT(11), NULL)
- `animals.destination_farm` (INT(11), NULL)
- `animals.movement_id` (INT(11), NULL)
- `animals.slaughter_house_id` (INT(11), NULL)

**Implementation**: Individual update with validation  
**Use Case**: Animal movement tracking  
**Change Data Structure**:
```json
{
  "destination": "Kampala Slaughter House",
  "destination_slaughter_house": 5,
  "movement_id": 1234
}
```

---

### 32. `update_location_coordinates` - Update GPS Coordinates
**Database Fields**:
- `animals.origin_latitude` (VARCHAR(225), NULL, default "0.00")
- `animals.origin_longitude` (VARCHAR(225), NULL, default "0.00")
- `animals.address` (VARCHAR(225), NULL)
- `animals.phone_number` (VARCHAR(25), NULL)

**Implementation**: Bulk update  
**Validation**: Valid lat/long format  
**Change Data Structure**:
```json
{
  "origin_latitude": "0.3476",
  "origin_longitude": "32.5825",
  "address": "Kawempe Division, Kampala"
}
```

---

### 33. `update_registration_info` - Update Registration Details
**Database Fields**:
- `animals.registered_by_id` (BIGINT(20) UNSIGNED, NULL, default 1)
- `animals.registered_id` (BIGINT(20), NULL, default 1)

**Implementation**: Bulk update  
**Use Case**: Correct registration records  
**Change Data Structure**:
```json
{
  "registered_by_id": 45,
  "registered_id": 45
}
```

---

## ❌ NOT RECOMMENDED FOR BATCH UPDATE

These operations should remain as **individual events** to maintain proper audit trail:

### Events That Should Stay as Events (Not Batch Updates)
- ✗ `Milking` - Each milking session needs timestamp and quantity
- ✗ `Treatment` - Medical treatments need detailed records
- ✗ `Vaccination` - Vaccination records need batch numbers, expiry dates
- ✗ `Death` - Critical event requiring detailed documentation
- ✗ `Calving` - Important reproductive event with offspring details
- ✗ `Abortion` - Medical event requiring documentation
- ✗ `Slaughter` - Legal requirement for detailed records
- ✗ `Disease test` - Lab results need proper documentation
- ✗ `Sample taken/result` - Medical records integrity

---

## 🔧 IMPLEMENTATION CHECKLIST

### Backend Implementation (Laravel)
- [ ] Add new change type case to `AnimalOfflineChangeController@applyChange()`
- [ ] Add validation logic for new change type
- [ ] Add to bulk update array if applicable
- [ ] Test with Postman/Tinker

### Mobile Implementation (Flutter)
- [ ] Add change type constant to `AnimalOfflineChange` model
- [ ] Add display text to `getChangeTypeDisplay()` method
- [ ] Create UI for selecting this change type
- [ ] Add to batch selection bottom sheet
- [ ] Test offline storage and sync

### Database Considerations
- [ ] Verify field exists in `animals` table
- [ ] Check field constraints (NOT NULL, DEFAULT values)
- [ ] Test with sample data
- [ ] Consider database migration if needed

---

## 📊 PRIORITY MATRIX

| Priority | Change Types | Count | Rationale |
|----------|-------------|-------|-----------|
| **CRITICAL** | color, breed, type, sex, weight | 5 | Basic identification & correctness |
| **HIGH** | pregnancy_status, mark_sick/healthy, fmd_vaccination | 3 | Health management essentials |
| **MEDIUM** | service_date, weaning, calving_stage, for_sale, price | 5 | Breeding & economic tracking |
| **LOW** | comments, genetic_info, performance_metrics | 3+ | Nice-to-have, advanced features |

---

## 🎯 RECOMMENDED IMPLEMENTATION ORDER

1. **Phase 1** (Week 1): Add remaining basic attributes (color, weight, lhc)
2. **Phase 2** (Week 2): Health status changes (pregnancy, sick/healthy, FMD)
3. **Phase 3** (Week 3): Breeding details (service date, weaning, calf status)
4. **Phase 4** (Week 4): Economic features (for_sale, prices)
5. **Phase 5+** (Future): Advanced features based on user feedback

---

## 🔐 VALIDATION RULES SUMMARY

| Field Category | Validation Rules |
|---------------|------------------|
| **Text Fields** | Max length, trim whitespace, non-empty |
| **Dates** | Format YYYY-MM-DD, not in future (except expected dates) |
| **Numbers** | Positive, reasonable ranges, numeric validation |
| **IDs** | Must exist in respective tables, foreign key checks |
| **Sex-Dependent** | Pregnancy (Female only), Sire (Male only) |
| **Enums** | Must match predefined values list |

---

## 📱 MOBILE UI SUGGESTIONS

### Bottom Sheet Organization
```
┌─────────────────────────────────────┐
│     BATCH UPDATE OPTIONS            │
├─────────────────────────────────────┤
│ 🆔 IDENTIFICATION                   │
│   • Change Color                    │
│   • Change Breed                    │
│   • Update Weight                   │
├─────────────────────────────────────┤
│ 🏥 HEALTH                           │
│   • Update Pregnancy Status         │
│   • Mark as Sick                    │
│   • Mark as Healthy                 │
│   • Update FMD Vaccination          │
├─────────────────────────────────────┤
│ 🐄 BREEDING                         │
│   • Record Service Date             │
│   • Update Weaning Status           │
│   • Change Calving Stage            │
├─────────────────────────────────────┤
│ 💰 SALES & VALUE                   │
│   • Mark for Sale                   │
│   • Update Price                    │
│   • Update Current Worth            │
├─────────────────────────────────────┤
│ 📍 LOCATION                         │
│   • Change Farm        [✓ DONE]     │
│   • Change Group       [✓ DONE]     │
│   • Change Status      [✓ DONE]     │
└─────────────────────────────────────┘
```

---

## 🧪 TESTING RECOMMENDATIONS

### Test Cases for Each Change Type
1. **Single Animal** - Verify field updates correctly
2. **Multiple Animals** - Test bulk operation
3. **Validation Errors** - Test with invalid data
4. **Edge Cases** - Null values, extreme ranges
5. **Sex-Dependent** - Female-only/Male-only operations
6. **Uniqueness** - E-ID/V-ID duplication checks
7. **Offline Sync** - Save offline → sync online
8. **Partial Success** - Some animals succeed, some fail

---

## 📝 NOTES

- All timestamps use Unix epoch (seconds)
- Change data stored as JSON in database
- Status values: "pending", "processing", "synced", "failed"
- Error messages stored for failed operations
- Each change has unique local_id for offline tracking

---

## 🔗 RELATED DOCUMENTATION

- `ANIMAL_OFFLINE_CHANGES_SYSTEM.md` - Offline change system overview
- `ANIMAL_360_IMPLEMENTATION_COMPLETE.md` - Animal detail endpoint
- `API_ANIMALS_V2_OPTIMIZATION.md` - API optimization details
- Database Schema: `animals` table (83 fields total)

---

**END OF DOCUMENT**
