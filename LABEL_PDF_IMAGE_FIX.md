# Label PDF Image Rendering Fix

## Problem
Barcodes and QR codes were not displaying correctly in the generated PDF labels because DomPDF requires special handling for images.

## Root Cause
- DomPDF cannot directly access image files via relative paths like `/storage/images/...`
- Images need to be either:
  1. Converted to base64 data URIs (recommended)
  2. Provided as absolute file system paths with proper permissions

## Solution Implemented

### 1. Created Image Data URI Helper Method
Added `getImageDataUri()` method in `LabelPdfGenerator.php`:

```php
protected function getImageDataUri($imagePath)
{
    if (empty($imagePath)) {
        return null;
    }

    // Clean the path - remove leading slashes and /storage prefix
    $cleanPath = ltrim($imagePath, '/');
    $cleanPath = preg_replace('#^storage/#', '', $cleanPath);
    
    // Try multiple possible locations for the image file
    $possiblePaths = [
        public_path($cleanPath),
        public_path('storage/' . $cleanPath),
        storage_path('app/public/' . $cleanPath),
        base_path($imagePath),
    ];

    foreach ($possiblePaths as $fullPath) {
        if (file_exists($fullPath)) {
            try {
                $imageData = file_get_contents($fullPath);
                $mimeType = mime_content_type($fullPath);
                $base64 = base64_encode($imageData);
                return 'data:' . $mimeType . ';base64,' . $base64;
            } catch (\Exception $e) {
                Log::warning('Failed to encode image: ' . $fullPath);
                continue;
            }
        }
    }

    Log::warning('Image file not found for PDF: ' . $imagePath);
    return null;
}
```

**How it works:**
1. Cleans the image path (removes leading slashes, /storage prefix)
2. Tries multiple possible file locations
3. Reads the image file as binary data
4. Encodes it as base64
5. Returns a data URI in format: `data:image/png;base64,iVBORw0KG...`
6. Returns null if file not found (gracefully handles missing images)

### 2. Updated All Template Methods
Updated all 4 label templates to use the new helper:

**Before:**
```php
if ($this->task->include_barcode === 'Yes' && !empty($record->bar_code)) {
    $html .= '<img src="' . $record->bar_code . '" class="barcode-img" alt="Barcode">';
}
```

**After:**
```php
if ($this->task->include_barcode === 'Yes' && !empty($record->bar_code)) {
    $barcodeDataUri = $this->getImageDataUri($record->bar_code);
    if ($barcodeDataUri) {
        $html .= '<img src="' . $barcodeDataUri . '" class="barcode-img" alt="Barcode">';
    }
}
```

### 3. Updated DomPDF Options
Optimized DomPDF settings for base64 embedded images:

```php
$pdf->setOptions([
    'isHtml5ParserEnabled' => true,
    'isRemoteEnabled' => false, // Not needed - using base64 embedded images
    'defaultFont' => 'DejaVu Sans',
    'enable_php' => false,
    'chroot' => public_path(), // Restrict file access to public directory
]);
```

## Templates Updated

### ✅ Compact Label
- Barcode with ID display
- QR code

### ✅ Standard Label  
- Barcode with "RECORD ID:" label
- QR code with "SCAN FOR FULL DETAILS" label

### ✅ Detailed Label
- Barcode with "RECORD:" label  
- QR code with "SCAN FOR FULL TRACEABILITY" label

### ✅ Premium Label
- QR code with "SCAN FOR COMPLETE TRACEABILITY" label
- Barcode with "RECORD:" label

## Benefits

1. **Reliable Rendering**: Base64 embedded images always work in DomPDF
2. **No Path Issues**: Doesn't depend on file system paths or web server configuration
3. **Portable PDFs**: All data embedded in PDF, no external dependencies
4. **Graceful Degradation**: Missing images don't break PDF generation
5. **Security**: No remote URL access needed
6. **Better Performance**: No HTTP requests for images

## Image Storage Locations Checked

The helper method checks these locations in order:
1. `public/{cleanPath}` - e.g., `public/images/qrcodes/...`
2. `public/storage/{cleanPath}` - symlinked storage
3. `storage/app/public/{cleanPath}` - actual storage location
4. `{base_path}/{imagePath}` - relative from project root

This ensures images are found regardless of how they're stored.

## Testing

To verify the fix:

1. **Create a label printing task** from the mobile app
2. **Check the generated PDF** - should show both barcode and QR code
3. **Verify image quality** - should be crisp and scannable
4. **Check logs** if images missing - will show which paths were checked

## Technical Notes

### Why Base64 Over File Paths?

**File Paths Issues:**
- Require absolute paths
- Need proper file permissions
- May fail with symlinks
- Platform-dependent (Windows vs Linux)

**Base64 Advantages:**
- Self-contained
- Platform-independent  
- No permission issues
- Works in all environments
- Standard DomPDF practice

### Image MIME Types Supported

The helper automatically detects:
- `image/png` (barcodes and QR codes)
- `image/jpeg`
- `image/gif`
- Other standard image formats

## Related Files

- **Modified**: `/app/Services/LabelPdfGenerator.php`
- **Image Generation**: `/app/Models/Utils.php` (generate_barcode, generate_qrcode)
- **Storage**: `public/storage/images/` (barcodes and QR codes)

## Validation

✅ PHP syntax validated - no errors
✅ All 4 templates updated consistently
✅ Graceful error handling implemented
✅ Logging added for debugging
✅ DomPDF options optimized

---

**Date**: November 4, 2025
**Status**: ✅ Complete
**Testing Required**: Generate PDFs with all 4 templates and verify barcodes/QR codes display correctly
