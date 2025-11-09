# User Management API - Quick Reference

## Overview
Comprehensive user management system with 13 endpoints covering all aspects of user administration.

**Base URL**: `/api/`  
**Authentication**: Bearer token required for all endpoints

---

## Quick Links

### Profile Management
- `GET /api/user/profile` - Get my profile
- `POST /api/user/update-profile` - Update my profile
- `POST /api/user/change-password` - Change my password

### User Listing
- `GET /api/users/list` - List all users (with filters)
- `GET /api/users/{id}` - Get user details
- `GET /api/users/by-role/{roleSlug}` - Get users by role
- `GET /api/users/statistics` - Get user statistics

### Administration (Requires Admin Role)
- `POST /api/users/{id}/status` - Activate/deactivate user
- `POST /api/users/{id}/reset-password` - Reset user password

### Role Management (Requires Admin Role)
- `GET /api/roles/list` - List all roles
- `POST /api/users/{id}/roles` - Replace all user roles
- `POST /api/users/{id}/roles/add` - Add role to user
- `POST /api/users/{id}/roles/remove` - Remove role from user

---

## Common Use Cases

### 1. Search for Users
```bash
# Search by name
GET /api/users/list?search=John

# Filter by status and role
GET /api/users/list?status=Active&role_slug=farmer

# Get users from specific district
GET /api/users/list?district_id=1
```

### 2. Update My Profile
```bash
POST /api/user/update-profile
{
    "first_name": "John",
    "last_name": "Doe",
    "phone_number": "+256700123456",
    "email": "john@example.com"
}
```

### 3. Assign Butchery Role
```bash
POST /api/users/123/roles/add
{
    "role_id": 21
}
```

### 4. Get All Butchery Staff
```bash
GET /api/users/by-role/butchery?status=Active
```

### 5. Change Password
```bash
POST /api/user/change-password
{
    "current_password": "old123",
    "new_password": "new456",
    "new_password_confirmation": "new456"
}
```

---

## Available Filters (users/list)

| Filter | Description | Example |
|--------|-------------|---------|
| search | Name, phone, email, username | `?search=John` |
| status | Active/Inactive | `?status=Active` |
| gender | Male/Female | `?gender=Male` |
| role_slug | Filter by role | `?role_slug=farmer` |
| district_id | Filter by district | `?district_id=1` |
| sub_county_id | Filter by sub-county | `?sub_county_id=15` |
| created_from | From date | `?created_from=2025-01-01` |
| created_to | To date | `?created_to=2025-12-31` |
| sort_by | Sort field | `?sort_by=name` |
| sort_dir | asc/desc | `?sort_dir=desc` |
| per_page | Results per page | `?per_page=50` |

---

## Response Format

### Success
```json
{
    "status": 1,
    "message": "Success message",
    "data": { ... }
}
```

### Error
```json
{
    "status": 0,
    "message": "Error message",
    "data": null
}
```

---

## Available Roles (Common)

| ID | Name | Slug |
|----|------|------|
| 1 | Super Administrator | administrator |
| 3 | Farmer | farmer |
| 5 | Abattoir | slaughter |
| 21 | Butchery Shop | butchery |
| 7 | District Veterinary Officer | dvo |
| 2 | Sub-County Veterinary officer | scvo |

---

## Testing Commands

```bash
# Get your profile
curl -X GET "http://localhost/api/user/profile" \
  -H "Authorization: Bearer YOUR_TOKEN"

# List users
curl -X GET "http://localhost/api/users/list?search=John"

# Update profile
curl -X POST "http://localhost/api/user/update-profile" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "first_name=John" \
  -F "last_name=Doe"

# Get statistics
curl -X GET "http://localhost/api/users/statistics" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Key Features

✅ **Complete Profile Management** - Update all user fields  
✅ **Advanced Search** - Multi-field search with filters  
✅ **Role Management** - Add/remove/replace roles  
✅ **Password Operations** - Change & reset passwords  
✅ **User Statistics** - Comprehensive analytics  
✅ **Flexible Filtering** - By role, location, status, etc.  
✅ **Pagination** - Handle large datasets  
✅ **Security** - Role-based authorization  

---

## Files Created

1. **Controller**: `app/Http/Controllers/ApiUserManagementController.php`
2. **Routes**: Added in `routes/api.php`
3. **Documentation**: `USER_MANAGEMENT_API_DOCUMENTATION.md`
4. **Quick Reference**: This file

---

**Status**: ✅ Ready to Use  
**Date**: November 9, 2025
