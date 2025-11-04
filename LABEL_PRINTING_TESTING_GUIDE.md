# Label Printing Testing Guide

## Quick Testing Steps

### Test 1: Create New Label Task
1. Open app → Navigate to Butcher Records
2. Tap "Label Printing" menu
3. Tap "Create New Task"
4. Select butcher records
5. Choose template (Standard)
6. Tap "Generate Labels"
7. **Expected:** Task created, PDF generated
8. **Verify:** PDF URL is accessible in browser

### Test 2: View PDF
1. From task detail screen
2. Tap "View PDF" button
3. **Expected:** PDF opens in viewer
4. **Verify:** Labels display correctly

### Test 3: Copy PDF Link
1. Tap menu (⋮) → "Copy PDF Link"
2. Paste in browser or Notes app
3. **Expected:** Link copied successfully
4. **Verify:** URL format is `https://domain.com/storage/images/labels_*.pdf`

### Test 4: Download PDF
1. Tap menu (⋮) → "Download PDF"
2. **Expected:** Browser/Download manager opens
3. **Verify:** PDF downloads to device

### Test 5: Regenerate Labels ⭐ **NEW**
1. Open completed task
2. Tap menu (⋮) → "Regenerate Labels"
3. Confirm in dialog
4. **Expected:** 
   - Toast: "Regenerating labels..."
   - New task created
   - Auto-navigate to new task detail
   - New PDF generated
5. **Verify:**
   - New task has different task number (incremented)
   - PDF URL is accessible
   - All settings match original task
   - Old task still exists in list

### Test 6: Regenerate Multiple Times
1. Regenerate same task 3 times
2. **Expected:** 3 new tasks created
3. **Verify:**
   - Each has unique task number
   - Each has own PDF file
   - All PDFs are accessible

---

## URL Format Verification

### Correct URL Format ✅
```
https://yourdomain.com/storage/images/labels_LPT-20241104-001_1699123456.pdf
```

### Incorrect URL Format ❌
```
https://yourdomain.com/storage/storage/images/labels_LPT-20241104-001_1699123456.pdf
                              ^^^^^^^^ (double storage/)
```

**How to Check:**
1. Create a task
2. Copy PDF link
3. Verify it has only ONE "storage/" in the path

---

## Error Testing

### Test Error 1: Invalid Task ID
1. Manually call API with invalid task_id
2. **Expected:** "Original task not found" error

### Test Error 2: Missing Task ID
1. Manually call API without task_id
2. **Expected:** "Task ID is required" error

---

## File System Verification

### Check PDF Files on Server
```bash
# SSH into server
cd /path/to/project/public/storage/images/

# List PDF files
ls -lh labels_*.pdf

# Check permissions
ls -ld .
# Should show: drwxr-xr-x (755)

# Check file permissions
ls -l labels_*.pdf
# Should show: -rw-r--r-- (644)
```

### Check Database
```sql
-- Check task records
SELECT id, task_number, status, pdf_path, pdf_size 
FROM label_printing_tasks 
ORDER BY id DESC 
LIMIT 10;

-- Verify pdf_path format
-- Should be: storage/images/labels_LPT-20241104-001_1699123456.pdf
-- NOT: /storage/images/... or storage/storage/images/...
```

---

## Success Criteria

✅ **PDF Creation**
- [x] PDFs saved to `public/storage/images/`
- [x] Files physically exist on disk
- [x] Correct permissions (755 dir, 644 files)

✅ **PDF Access**
- [x] URLs accessible in browser
- [x] No 404 errors
- [x] Downloads work correctly
- [x] No authentication required

✅ **Regeneration**
- [x] Creates new task with new task number
- [x] Generates fresh PDF
- [x] All settings cloned correctly
- [x] Navigation to new task works
- [x] Old task remains accessible

✅ **Error Handling**
- [x] Invalid task ID handled
- [x] Missing task ID handled
- [x] User feedback on errors
- [x] No crashes or silent failures

---

## Quick Fixes

### If PDFs Return 404:
1. Check file exists: `ls public/storage/images/labels_*.pdf`
2. Check permissions: `chmod 755 public/storage/images`
3. Check web server config (document root = public/)
4. Clear web server cache

### If Regeneration Fails:
1. Check logs: `tail -f storage/logs/laravel.log`
2. Verify disk space: `df -h`
3. Check database connection
4. Verify API route exists

### If Double "storage/" in URL:
1. Already fixed in this update
2. Verify code matches documentation
3. Clear application cache: `php artisan cache:clear`

---

**Last Updated:** November 4, 2024  
**Status:** Ready for Testing ✅
