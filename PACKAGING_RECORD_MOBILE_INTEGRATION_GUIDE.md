# PackagingRecord Mobile App Integration Guide

**Module:** Packaging Records  
**Section:** Butchery  
**Date:** December 9, 2025  
**Backend Status:** ✅ Complete  
**API Version:** 1.0

---

## 📱 Overview

This guide provides complete integration instructions for implementing the **PackagingRecord** module in the E-TAG mobile app under the Butchery section. The module enables users to create, view, and manage meat packaging labels for both Prime Cuts (27 types) and Offal (10 types).

---

## 🎯 User Flows

### 1. Create New Package Flow
```
Butchery Menu 
  → Packaging Records 
    → Create New Package
      → Select Slaughter Record (search/dropdown)
      → Choose Package Type (Prime Cut / Offal)
      → Set Packaging Date
      → Set Expiry Date
      → Enter Weights (dynamic based on type)
      → Add Notes (optional)
      → Submit
        → View Success + PDF Download
```

### 2. View Packages Flow
```
Butchery Menu 
  → Packaging Records 
    → List View (with filters)
      → Tap Package
        → View Details
          → Cut Breakdown Table
          → Download PDF Label
          → Edit (if not sold)
          → Mark as Sold
```

### 3. Scan Barcode Flow
```
Butchery Menu 
  → Packaging Records 
    → Scan Barcode
      → Camera Scanner
        → Find Package by Barcode
          → View Package Details
```

---

## 🔌 API Endpoints

### Base URLs
- **Local Testing:** `http://localhost:8888/etag-web/api`
- **Production:** `https://u-lits.com/api`

### Authentication
All requests require `administrator_id` parameter (logged-in user ID)

---

## 1️⃣ List All Packaging Records

**Endpoint:** `GET /api/packaging-records`

**Query Parameters:**
```dart
{
  "administrator_id": 1,          // Required
  "per_page": 20,                 // Optional, default 50
  "page": 1,                      // Optional, default 1
  "package_type": "Prime Cut",    // Optional: "Prime Cut" or "Offal"
  "status": "Active",             // Optional: Active, Sold, Expired, Discarded
  "search": "PKG-000001",         // Optional: Search V-ID, E-ID, barcode
  "packaging_date_from": "2025-12-01",  // Optional
  "packaging_date_to": "2025-12-31",    // Optional
  "expiry_date_from": "2025-12-01",     // Optional
  "expiry_date_to": "2025-12-31"        // Optional
}
```

**Response (200):**
```json
{
  "code": 1,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "package_code": "PKG-000001",
      "slaughter_record_id": 1,
      "v_id": "30772",
      "e_id": "E001",
      "package_type": "Prime Cut",
      "total_weight": "39.80",
      "packaging_date": "2025-12-09",
      "expiry_date": "2026-01-08",
      "status": "Active",
      "is_expired": false,
      "days_until_expiry": 29,
      "pdf_url": "http://localhost:8888/etag-web/storage/images/packaging_labels/PKG-000001_20251209.pdf",
      "packager_name": "John Doe",
      "created_at": "2025-12-09 12:30:00"
    }
  ],
  "total": 15,
  "per_page": 20,
  "current_page": 1,
  "last_page": 1
}
```

**Flutter Example:**
```dart
Future<PackagingRecordsResponse> fetchPackagingRecords({
  required int adminId,
  int page = 1,
  int perPage = 20,
  String? packageType,
  String? status,
  String? search,
}) async {
  final queryParams = {
    'administrator_id': adminId.toString(),
    'page': page.toString(),
    'per_page': perPage.toString(),
    if (packageType != null) 'package_type': packageType,
    if (status != null) 'status': status,
    if (search != null) 'search': search,
  };
  
  final uri = Uri.parse('$baseUrl/api/packaging-records')
      .replace(queryParameters: queryParams);
  
  final response = await http.get(uri);
  
  if (response.statusCode == 200) {
    return PackagingRecordsResponse.fromJson(json.decode(response.body));
  } else {
    throw Exception('Failed to load packaging records');
  }
}
```

---

## 2️⃣ Get Single Package Details

**Endpoint:** `GET /api/packaging-records/{id}`

**Query Parameters:**
```dart
{
  "administrator_id": 1  // Required
}
```

**Response (200):**
```json
{
  "code": 1,
  "message": "Success",
  "data": {
    "id": 1,
    "package_code": "PKG-000001",
    "slaughter_record_id": 1,
    "animal_id": 5,
    "v_id": "30772",
    "e_id": "E001",
    "lhc": "UG12345",
    "breed": "Ankole",
    "sex": "Male",
    "barcode": "SL001-2025-001",
    "package_type": "Prime Cut",
    "total_weight": "39.80",
    "packaging_date": "2025-12-09",
    "expiry_date": "2026-01-08",
    "packaged_by": 1,
    "packager_name": "John Doe",
    "status": "Active",
    "is_expired": false,
    "days_until_expiry": 29,
    "pdf_generated": true,
    "pdf_url": "http://localhost:8888/etag-web/storage/images/packaging_labels/PKG-000001_20251209.pdf",
    "notes": "Premium quality prime cuts",
    "cut_breakdown": [
      {
        "label": "Beef Boneless",
        "field": "beef_boneless",
        "weight": "10.50"
      },
      {
        "label": "Fillet",
        "field": "fillet",
        "weight": "3.20"
      },
      {
        "label": "Sirloin/Striploin",
        "field": "sirloin_striploin",
        "weight": "5.70"
      }
    ],
    "slaughter_record": {
      "id": 1,
      "v_id": "30772",
      "slaughter_date": "2025-12-08"
    },
    "created_at": "2025-12-09 12:30:00",
    "updated_at": "2025-12-09 12:30:00"
  }
}
```

**Flutter Model:**
```dart
class PackagingRecord {
  final int id;
  final String packageCode;
  final int slaughterRecordId;
  final String vId;
  final String eId;
  final String packageType;
  final double totalWeight;
  final DateTime packagingDate;
  final DateTime expiryDate;
  final String status;
  final bool isExpired;
  final int daysUntilExpiry;
  final String pdfUrl;
  final List<CutBreakdown> cutBreakdown;
  
  PackagingRecord.fromJson(Map<String, dynamic> json)
      : id = json['id'],
        packageCode = json['package_code'],
        slaughterRecordId = json['slaughter_record_id'],
        vId = json['v_id'],
        eId = json['e_id'],
        packageType = json['package_type'],
        totalWeight = double.parse(json['total_weight']),
        packagingDate = DateTime.parse(json['packaging_date']),
        expiryDate = DateTime.parse(json['expiry_date']),
        status = json['status'],
        isExpired = json['is_expired'],
        daysUntilExpiry = json['days_until_expiry'],
        pdfUrl = json['pdf_url'],
        cutBreakdown = (json['cut_breakdown'] as List)
            .map((e) => CutBreakdown.fromJson(e))
            .toList();
}

class CutBreakdown {
  final String label;
  final String field;
  final double weight;
  
  CutBreakdown.fromJson(Map<String, dynamic> json)
      : label = json['label'],
        field = json['field'],
        weight = double.parse(json['weight'].toString());
}
```

---

## 3️⃣ Create New Package (Prime Cut)

**Endpoint:** `POST /api/packaging-records/create`

**Request Body:**
```json
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
  "t_bone": 4.3,
  "rump_steak": 6.8,
  "ribeye": 2.9,
  "brisket": 5.4,
  "notes": "Premium quality prime cuts"
}
```

**Flutter Example:**
```dart
Future<PackagingRecord> createPrimeCutPackage({
  required int adminId,
  required int slaughterRecordId,
  required DateTime packagingDate,
  required DateTime expiryDate,
  required Map<String, double> weights,  // e.g., {'beef_boneless': 10.5, 'fillet': 3.2}
  String? notes,
}) async {
  final body = {
    'administrator_id': adminId,
    'slaughter_record_id': slaughterRecordId,
    'package_type': 'Prime Cut',
    'packaging_date': packagingDate.toIso8601String().split('T')[0],
    'expiry_date': expiryDate.toIso8601String().split('T')[0],
    'packaged_by': adminId,
    if (notes != null) 'notes': notes,
    ...weights,  // Spread the weights map
  };
  
  final response = await http.post(
    Uri.parse('$baseUrl/api/packaging-records/create'),
    headers: {'Content-Type': 'application/json'},
    body: json.encode(body),
  );
  
  if (response.statusCode == 201) {
    final data = json.decode(response.body);
    return PackagingRecord.fromJson(data['data']);
  } else {
    throw Exception('Failed to create package');
  }
}
```

**Response (201):**
```json
{
  "code": 1,
  "message": "Packaging record created successfully",
  "data": {
    "id": 5,
    "package_code": "PKG-000005",
    "total_weight": "39.80",
    "pdf_generated": true,
    "pdf_url": "http://localhost:8888/etag-web/storage/images/packaging_labels/PKG-000005_20251209.pdf",
    // ... full record details
  }
}
```

---

## 4️⃣ Create New Package (Offal)

**Request Body:**
```json
{
  "administrator_id": 1,
  "slaughter_record_id": 1,
  "package_type": "Offal",
  "packaging_date": "2025-12-09",
  "expiry_date": "2025-12-16",
  "packaged_by": 1,
  "heart": 1.2,
  "kidneys": 0.8,
  "liver": 2.5,
  "tongue": 1.1,
  "tail": 0.9,
  "tripe": 3.2,
  "intestines": 4.5,
  "lungs": 1.8,
  "notes": "Fresh offal package"
}
```

**Note:** Offal packages typically have shorter shelf life (7 days) compared to Prime Cuts (30 days).

---

## 5️⃣ Update Package

**Endpoint:** `POST /api/packaging-records/update`

**Request Body:**
```json
{
  "administrator_id": 1,
  "id": 1,
  "beef_boneless": 12.0,
  "fillet": 4.0,
  "notes": "Updated weights after re-weighing"
}
```

**Protection:** Cannot update if status is 'Sold'

**Response (200):**
```json
{
  "code": 1,
  "message": "Packaging record updated successfully",
  "data": {
    // Updated record with recalculated total_weight
    // PDF automatically regenerated
  }
}
```

---

## 6️⃣ Mark Package as Sold

**Endpoint:** `POST /api/packaging-records/mark-sold`

**Request Body:**
```json
{
  "administrator_id": 1,
  "id": 1,
  "sold_date": "2025-12-09",
  "buyer_info": "Sunshine Supermarket - Kampala Branch"
}
```

**Response (200):**
```json
{
  "code": 1,
  "message": "Packaging record marked as sold",
  "data": {
    "id": 1,
    "status": "Sold",
    // ... full record
  }
}
```

**Note:** Once marked as sold, the record becomes read-only (cannot edit or delete).

---

## 7️⃣ Delete Package

**Endpoint:** `POST /api/packaging-records/delete`

**Request Body:**
```json
{
  "administrator_id": 1,
  "id": 5
}
```

**Protection:** Cannot delete if status is 'Sold'

**Response (200):**
```json
{
  "code": 1,
  "message": "Packaging record deleted successfully"
}
```

---

## 8️⃣ Generate/Regenerate PDF

**Endpoint:** `POST /api/packaging-records/generate-pdf`

**Request Body:**
```json
{
  "administrator_id": 1,
  "id": 1
}
```

**Response (200):**
```json
{
  "code": 1,
  "message": "PDF generated successfully",
  "data": {
    "id": 1,
    "package_code": "PKG-000001",
    "pdf_url": "http://localhost:8888/etag-web/storage/images/packaging_labels/PKG-000001_20251209.pdf",
    "pdf_file_path": "packaging_labels/PKG-000001_20251209.pdf"
  }
}
```

---

## 9️⃣ Get Packages by Slaughter Record

**Endpoint:** `GET /api/slaughter-records/{slaughter_record_id}/packaging-records`

**Query Parameters:**
```dart
{
  "administrator_id": 1  // Required
}
```

**Response (200):**
```json
{
  "code": 1,
  "message": "Success",
  "data": [
    // Array of packaging records for this slaughter record
  ]
}
```

**Use Case:** Show all packages created from a specific slaughtered animal.

---

## 📊 Prime Cuts (27 Types)

### UI Recommendation: Group by Category

**Category 1: Standard Cuts**
- Beef Boneless
- Beef for Stew
- Bones
- Brisket
- Chops
- Chuck Ribs
- Family Steak
- Fore Rib
- Leg Cut
- Middle Rib
- Minced Meat
- Neck

**Category 2: Premium Cuts**
- Fillet
- Rib Eye
- Rolled Loin
- Rump
- Rump Steak
- Short Ribs
- Silver Side
- Sirloin/Striploin
- T-Bone
- Topside/Beef Roast
- Veal Steak

**Category 3: Specialty**
- Ossubucco
- Oxtail
- Ribs
- Shin
- Staff Meat
- Thick Flank

### Field Names Mapping
```dart
final Map<String, String> primeCutFields = {
  'beef_boneless': 'Beef Boneless',
  'beef_stew': 'Beef for Stew',
  'bones': 'Bones',
  'brisket': 'Brisket',
  'chops': 'Chops',
  'chuck_ribs': 'Chuck Ribs',
  'family_steak': 'Family Steak',
  'fore_rib': 'Fore Rib',
  'leg_cut': 'Leg Cut',
  'middle_rib': 'Middle Rib',
  'minced_meat': 'Minced Meat',
  'neck': 'Neck',
  'ossubucco': 'Ossubucco',
  'oxtail': 'Oxtail',
  'ribs': 'Ribs',
  'rump_steak': 'Rump Steak',
  'short_ribs': 'Short Ribs',
  'shin': 'Shin',
  'staff_meat': 'Staff Meat',
  'thick_flank': 'Thick Flank',
  'fillet': 'Fillet',
  'rib_eye': 'Rib Eye',
  'rolled_loin': 'Rolled Loin',
  'rump': 'Rump',
  'silver_side': 'Silver Side',
  'sirloin_striploin': 'Sirloin/Striploin',
  't_bone': 'T-Bone',
  'topside_beef_roast': 'Topside/Beef Roast',
  'veal_steak': 'Veal Steak',
};
```

---

## 🥩 Offal Types (10 Types)

### Field Names Mapping
```dart
final Map<String, String> offalFields = {
  'heart': 'Heart',
  'kidneys': 'Kidneys',
  'liver': 'Liver',
  'tongue': 'Tongue',
  'lungs': 'Lungs',
  'tripe': 'Tripe',
  'tail': 'Tail',
  'head': 'Head',
  'feet': 'Feet',
  'intestines': 'Testicles/Intestines',  // Note: DB field is 'testicles'
};
```

---

## 🎨 UI/UX Recommendations

### 1. Package Type Selection
```dart
// Radio buttons or segmented control
PackageTypeSelector(
  options: ['Prime Cut', 'Offal'],
  onChanged: (type) {
    setState(() {
      selectedType = type;
      // Clear weights when switching types
      weights.clear();
    });
  },
)
```

### 2. Weight Input Screen
```dart
// Dynamic form based on package type
ListView.builder(
  itemCount: currentFields.length,
  itemBuilder: (context, index) {
    final field = currentFields.keys.elementAt(index);
    final label = currentFields[field];
    
    return WeightInputTile(
      label: label,
      field: field,
      value: weights[field],
      onChanged: (value) {
        weights[field] = value;
        updateTotalWeight();
      },
    );
  },
)
```

### 3. Status Badge Colors
```dart
Color getStatusColor(String status) {
  switch (status) {
    case 'Active':
      return Colors.green;
    case 'Sold':
      return Colors.blue;
    case 'Expired':
      return Colors.red;
    case 'Discarded':
      return Colors.grey;
    default:
      return Colors.black;
  }
}
```

### 4. Expiry Warning
```dart
Widget buildExpiryWarning(int daysUntilExpiry) {
  if (daysUntilExpiry <= 2) {
    return Container(
      color: Colors.red.shade100,
      padding: EdgeInsets.all(8),
      child: Row(
        children: [
          Icon(Icons.warning, color: Colors.red),
          SizedBox(width: 8),
          Text(
            'Expires in $daysUntilExpiry days!',
            style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }
  return SizedBox.shrink();
}
```

### 5. PDF Download Button
```dart
ElevatedButton.icon(
  icon: Icon(Icons.picture_as_pdf),
  label: Text('Download Label'),
  onPressed: () async {
    await downloadPdf(record.pdfUrl);
    // Or open in browser/PDF viewer
  },
)
```

---

## 🔍 Search & Filter Features

### Recommended Filters
1. **Package Type** - Dropdown (All, Prime Cut, Offal)
2. **Status** - Dropdown (All, Active, Sold, Expired, Discarded)
3. **Date Range** - Date pickers (Packaging Date, Expiry Date)
4. **Search Bar** - Free text (V-ID, E-ID, Package Code, Barcode)

### Filter UI Example
```dart
FilterBottomSheet(
  onApply: (filters) {
    fetchPackagingRecords(
      packageType: filters['type'],
      status: filters['status'],
      search: filters['search'],
      packagingDateFrom: filters['date_from'],
      packagingDateTo: filters['date_to'],
    );
  },
)
```

---

## 📸 Barcode Scanner Integration

```dart
// Using mobile_scanner package
import 'package:mobile_scanner/mobile_scanner.dart';

class BarcodeScannerScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Scan Package Barcode')),
      body: MobileScanner(
        onDetect: (barcode, args) async {
          if (barcode.rawValue != null) {
            // Search for package by barcode
            final packages = await searchPackagesByBarcode(
              barcode.rawValue!,
            );
            
            if (packages.isNotEmpty) {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => PackageDetailScreen(
                    packageId: packages.first.id,
                  ),
                ),
              );
            }
          }
        },
      ),
    );
  }
}
```

---

## ⚠️ Validation Rules

### Client-Side Validation
```dart
class PackageValidator {
  static String? validateSlaughterRecord(int? value) {
    if (value == null) return 'Please select a slaughter record';
    return null;
  }
  
  static String? validatePackageType(String? value) {
    if (value == null || value.isEmpty) {
      return 'Please select package type';
    }
    if (!['Prime Cut', 'Offal'].contains(value)) {
      return 'Invalid package type';
    }
    return null;
  }
  
  static String? validateDate(DateTime? value) {
    if (value == null) return 'Please select a date';
    return null;
  }
  
  static String? validateExpiryDate(
    DateTime? expiryDate,
    DateTime? packagingDate,
  ) {
    if (expiryDate == null) return 'Please select expiry date';
    if (packagingDate != null && expiryDate.isBefore(packagingDate)) {
      return 'Expiry date must be after packaging date';
    }
    return null;
  }
  
  static String? validateWeight(double? value) {
    if (value != null && value < 0) {
      return 'Weight cannot be negative';
    }
    return null;
  }
  
  static String? validateWeights(Map<String, double> weights) {
    if (weights.values.every((w) => w == 0 || w == null)) {
      return 'Please enter at least one weight';
    }
    return null;
  }
}
```

---

## 🚀 Performance Tips

1. **Pagination:** Load 20 items at a time, implement infinite scroll
2. **Caching:** Cache package list locally, refresh on pull-to-refresh
3. **Image Loading:** Use `cached_network_image` for PDF thumbnails (if generated)
4. **Debounce Search:** Wait 500ms after user stops typing before searching
5. **Offline Support:** Queue create/update operations when offline

---

## 🧪 Testing Checklist

### Functional Tests
- [ ] Create Prime Cut package
- [ ] Create Offal package
- [ ] View package list with filters
- [ ] View single package details
- [ ] Update package weights
- [ ] Mark package as sold
- [ ] Delete active package
- [ ] Cannot edit sold package
- [ ] Cannot delete sold package
- [ ] PDF download works
- [ ] Barcode search works

### UI Tests
- [ ] Package type switching clears weights
- [ ] Total weight updates in real-time
- [ ] Expiry warnings show for ≤2 days
- [ ] Status badges show correct colors
- [ ] Date pickers work correctly
- [ ] Validation messages show properly
- [ ] Loading states display
- [ ] Error messages clear

### Edge Cases
- [ ] No slaughter records available
- [ ] No packages found
- [ ] Network timeout handling
- [ ] Invalid barcode scan
- [ ] Expired packages highlighted
- [ ] Large weight numbers (> 1000 kg)
- [ ] Very long notes text

---

## 📞 Support & Documentation

**Backend API:** ✅ Complete and tested  
**Postman Collection:** `PackagingRecords.postman_collection.json`  
**Backend Docs:** `PACKAGING_RECORD_SUCCESS_SUMMARY.md`  

**Questions?** Contact backend team or refer to the comprehensive documentation in the etag-web repository.

---

**Happy Coding! 🚀**
