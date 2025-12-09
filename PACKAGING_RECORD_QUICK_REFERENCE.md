# PackagingRecord Quick Reference Card

**Module:** Packaging Records | **Section:** Butchery | **Status:** ✅ Production Ready

---

## 🚀 Quick Start

### For Backend Developers
```bash
# Migration already run ✅
php artisan migrate

# Seed menu ✅
php artisan db:seed --class=AdminButcheryMenuSeeder

# Test
php test_packaging_record.php
```

### For API Consumers
**Base URL:** `https://u-lits.com/api`  
**Import:** `PackagingRecords.postman_collection.json`  
**Auth:** Include `administrator_id` in all requests

---

## 📡 API Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/packaging-records` | List all (with filters) |
| GET | `/api/packaging-records/{id}` | Get single record |
| POST | `/api/packaging-records/create` | Create new package |
| POST | `/api/packaging-records/update` | Update package |
| POST | `/api/packaging-records/mark-sold` | Mark as sold |
| POST | `/api/packaging-records/delete` | Delete package |
| POST | `/api/packaging-records/generate-pdf` | Regenerate PDF |
| GET | `/api/slaughter-records/{id}/packaging-records` | Get by slaughter |

---

## 📋 Prime Cuts (27)

```
beef_boneless, beef_stew, bones, brisket, chops, chuck_ribs,
family_steak, fore_rib, leg_cut, middle_rib, minced_meat, neck,
ossubucco, oxtail, ribs, rump_steak, short_ribs, shin, staff_meat,
thick_flank, fillet, rib_eye, rolled_loin, rump, silver_side,
sirloin_striploin, t_bone, topside_beef_roast, veal_steak
```

## 🥩 Offal Types (10)

```
heart, kidneys, liver, tongue, lungs, tripe, tail, head, feet, intestines
```

---

## 📊 Status Values

- **Active** 🟢 - Normal package, can edit/delete
- **Sold** 🔵 - Read-only, cannot edit/delete
- **Expired** 🔴 - Past expiry date
- **Discarded** ⚫ - Marked as waste

---

## 🎨 Status Badge Colors

```dart
Active: Colors.green
Sold: Colors.blue
Expired: Colors.red
Discarded: Colors.grey
```

---

## 📝 Create Package Example

```json
POST /api/packaging-records/create

{
  "administrator_id": 1,
  "slaughter_record_id": 1,
  "package_type": "Prime Cut",
  "packaging_date": "2025-12-09",
  "expiry_date": "2026-01-08",
  "packaged_by": 1,
  "beef_boneless": 10.5,
  "fillet": 3.2,
  "sirloin_striploin": 5.7,
  "notes": "Premium cuts"
}
```

**Response:** Package created + PDF auto-generated ✅

---

## 🔍 Search & Filter

```
?package_type=Prime Cut
?status=Active
?search=PKG-000001
?packaging_date_from=2025-12-01
?packaging_date_to=2025-12-31
?per_page=20
```

---

## ⚠️ Business Rules

1. **Cannot edit sold packages** - Status must be 'Active'
2. **Cannot delete sold packages** - Permanent record
3. **Total weight auto-calculated** - Sum of all non-zero weights
4. **PDF auto-generated** - On create/update
5. **Expiry auto-tracked** - Changes to 'Expired' when date passes
6. **At least one weight required** - Validation enforced

---

## 📱 Mobile Integration

**Read:** `PACKAGING_RECORD_MOBILE_INTEGRATION_GUIDE.md`

**Key Models:**
- `PackagingRecord` - Main model
- `CutBreakdown` - Weight breakdown

**Key Screens:**
- List packages (with filters)
- Create package (Prime Cut / Offal)
- Package details (with PDF download)
- Barcode scanner

---

## 🗂️ Files Location

```
app/
  Models/PackagingRecord.php              # Model
  Http/Controllers/PackagingRecordController.php  # API
  Services/PackagingRecordPdfService.php  # PDF
  Admin/Controllers/PackagingRecordController.php # Admin

resources/views/pdf/packaging-label.blade.php  # PDF Template

database/
  migrations/2025_12_09_120000_create_packaging_records_table.php
  seeders/AdminButcheryMenuSeeder.php

routes/
  api.php                                 # +8 routes
  app/Admin/routes.php                    # +1 route
```

---

## 🧪 Testing

```bash
# Model tests
php test_packaging_record.php

# Check table
php artisan tinker
>>> \App\Models\PackagingRecord::count()

# View records
>>> \App\Models\PackagingRecord::with('slaughterRecord')->get()
```

---

## 📞 Admin Panel

**URL:** `/admin/packaging-records`  
**Menu:** Butchery → Packaging Records  
**Icon:** fa-archive  

**Features:**
- Grid with filters
- AJAX slaughter record search
- Dynamic form (Prime Cut/Offal)
- PDF download buttons
- Status badges

---

## 🔗 Relationships

```php
PackagingRecord
  ->slaughterRecord()      // belongsTo SlaughterRecord
  ->animal()               // belongsTo Animal
  ->distributionRecord()   // belongsTo SlaughterDistributionRecord
  ->packager()             // belongsTo Administrator
```

---

## 💾 Database

**Table:** `packaging_records`  
**Columns:** 59  
**Indexes:** 7  
**No Cascades:** ✅  

**Key Fields:**
- `package_code` - Auto-generated (PKG-000001)
- `total_weight` - Auto-calculated
- `expiry_date` - Required
- `status` - ENUM('Active','Sold','Expired','Discarded')
- `pdf_file_path` - Auto-populated

---

## 📄 PDF Labels

**Format:** A4 (210mm x 297mm)  
**Storage:** `public/storage/images/packaging_labels/`  
**Naming:** `PKG-XXXXXX_YYYYMMDD.pdf`  

**Includes:**
- Package code barcode
- Animal info (V-ID, E-ID, LHC, Breed, Sex)
- Package details (Type, Dates, Packager)
- Cut/Offal breakdown table
- Total weight
- QR code for traceability
- Expiry warning (if ≤2 days)

---

## 🎯 Common Tasks

### Create Prime Cut Package
```php
PackagingRecord::create([
    'slaughter_record_id' => 1,
    'package_type' => 'Prime Cut',
    'packaging_date' => '2025-12-09',
    'expiry_date' => '2026-01-08',
    'packaged_by' => 1,
    'beef_boneless' => 10.5,
    'fillet' => 3.2,
    // ... more weights
]);
```

### Get Packages by Slaughter
```php
$packages = PackagingRecord::where('slaughter_record_id', 1)->get();
```

### Mark as Sold
```php
$package->update([
    'status' => 'Sold',
    'sold_date' => now(),
    'buyer_info' => 'Sunshine Supermarket'
]);
```

---

## 🚨 Troubleshooting

**PDF not generating?**
- Check storage permissions: `chmod -R 775 storage/`
- Verify DomPDF installed: `composer show barryvdh/laravel-dompdf`

**Expiry date error?**
- Must be provided explicitly
- Must be after packaging_date

**Total weight zero?**
- At least one weight field must be > 0
- Auto-calculates on save

**Cannot edit package?**
- Check status - must be 'Active'
- Sold packages are read-only

---

## 📚 Documentation

1. **Implementation Plan** - `PACKAGING_RECORD_IMPLEMENTATION_PLAN.md`
2. **Success Summary** - `PACKAGING_RECORD_SUCCESS_SUMMARY.md`
3. **Mobile Guide** - `PACKAGING_RECORD_MOBILE_INTEGRATION_GUIDE.md`
4. **Final Report** - `PACKAGING_RECORD_FINAL_REPORT.md`
5. **Postman Collection** - `PackagingRecords.postman_collection.json`

---

## ✅ Checklist

Backend:
- [x] Migration run
- [x] Model created
- [x] API endpoints working
- [x] PDF generation working
- [x] Admin panel integrated
- [x] Menu seeded
- [x] Tests passing

Documentation:
- [x] Implementation plan
- [x] Success summary
- [x] Mobile integration guide
- [x] Postman collection
- [x] Quick reference
- [x] Final report

Ready for:
- [ ] Production deployment
- [ ] Mobile app development
- [ ] End-user testing

---

**Questions?** Refer to comprehensive docs or contact backend team.

**Version:** 1.0 | **Date:** December 9, 2025 | **Status:** ✅ Complete
