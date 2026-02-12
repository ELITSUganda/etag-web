# QUICK SETUP GUIDE - Butchery Packaging Menus

## Step-by-Step Menu Creation via Admin Panel

### Step 1: Access Menu Management
1. Login to admin panel: `http://localhost:8888/etag-web/admin`
2. Navigate to: **Admin → Menu** (usually in sidebar under "Admin" section)
3. Or go directly to: `http://localhost:8888/etag-web/admin/auth/menu`

### Step 2: Create Parent Menu "Packaging"
1. Click the **"New"** button (top right)
2. Fill in the form:
   - **Parent ID:** Select "Root" (or select "Butchery" if it exists)
   - **Title:** `Packaging`
   - **Icon:** `fa-archive`
   - **URI:** (leave blank - this is a parent menu)
   - **Roles:** (leave default or select as needed)
   - **Order:** Enter a number (e.g., 100) - adjust based on your menu order
3. Click **"Submit"**
4. **Remember the ID** of this menu item (you'll see it in the list)

### Step 3: Create Sub-Menu 1 - "Primal Cuts Fore Quarters"
1. Click **"New"** again
2. Fill in:
   - **Parent ID:** Select **"Packaging"** (the menu you just created)
   - **Title:** `Primal Cuts Fore Quarters`
   - **Icon:** `fa-cut`
   - **URI:** `packaging-fore-quarters`
   - **Roles:** (same as parent)
   - **Order:** `1`
3. Click **"Submit"**

### Step 4: Create Sub-Menu 2 - "Primal Cuts Hind Quarters"
1. Click **"New"** again
2. Fill in:
   - **Parent ID:** Select **"Packaging"**
   - **Title:** `Primal Cuts Hind Quarters`
   - **Icon:** `fa-cut`
   - **URI:** `packaging-hind-quarters`
   - **Roles:** (same as parent)
   - **Order:** `2`
3. Click **"Submit"**

### Step 5: Create Sub-Menu 3 - "Offals"
1. Click **"New"** again
2. Fill in:
   - **Parent ID:** Select **"Packaging"**
   - **Title:** `Offals`
   - **Icon:** `fa-cubes`
   - **URI:** `packaging-offals`
   - **Roles:** (same as parent)
   - **Order:** `3`
3. Click **"Submit"**

### Step 6: Verify Menu Structure
After creating all menus, you should see this structure:
```
📦 Packaging
  ├─ 🔪 Primal Cuts Fore Quarters → packaging-fore-quarters
  ├─ 🔪 Primal Cuts Hind Quarters → packaging-hind-quarters
  └─ 🧊 Offals → packaging-offals
```

### Step 7: Test the URLs
Navigate to each URL to verify they work:
1. `http://localhost:8888/etag-web/admin/packaging-fore-quarters`
2. `http://localhost:8888/etag-web/admin/packaging-hind-quarters`
3. `http://localhost:8888/etag-web/admin/packaging-offals`

## Troubleshooting

### Menu doesn't appear?
- Clear browser cache (Ctrl+Shift+R or Cmd+Shift+R)
- Clear Laravel cache: `php artisan cache:clear`
- Logout and login again
- Check menu roles/permissions

### URL returns 404?
- Verify routes exist: `php artisan route:list | grep packaging`
- Clear route cache: `php artisan route:clear`
- Check controller file exists
- Restart web server if needed

### Icons not showing?
- Verify Font Awesome is loaded
- Use basic icons: `fa-file`, `fa-list`, `fa-plus`
- Icons reference: https://fontawesome.com/v4/icons/

## Alternative: SQL Command (if you have database access)

```bash
# Connect to MySQL
mysql -uroot -proot etag_web

# Run the SQL from butchery_packaging_menu.sql file
source database/butchery_packaging_menu.sql;

# Or copy-paste the SQL directly
```

## Expected Result

After setup, your admin sidebar should show:
```
...
📦 Packaging
  ├─ Primal Cuts Fore Quarters
  ├─ Primal Cuts Hind Quarters
  └─ Offals
...
```

Clicking each sub-menu will take you to the specific packaging grid view with:
- Type-filtered packages
- Appropriate cut columns
- Create/Edit forms with relevant fields

## Test Checklist
- [ ] Menu "Packaging" visible in sidebar
- [ ] Three sub-menus visible under Packaging
- [ ] Clicking "Primal Cuts Fore Quarters" opens grid
- [ ] Clicking "Primal Cuts Hind Quarters" opens grid
- [ ] Clicking "Offals" opens grid
- [ ] Each grid shows appropriate columns
- [ ] Forms show correct cut types
- [ ] Grid filtering works
- [ ] Status badges display correctly

---
**Setup Complete!** 🎉

For detailed documentation, see: `BUTCHERY_PACKAGING_SYSTEM.md`
