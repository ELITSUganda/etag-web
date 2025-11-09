# User Management API - Complete Documentation

## Overview
Comprehensive user management system with advanced features for profile management, password operations, role assignments, filtering, and searching.

**Controller**: `ApiUserManagementController.php`  
**Base Path**: `/api/`  
**Date**: November 9, 2025

---

## Table of Contents
1. [Profile Management](#profile-management)
2. [Password Operations](#password-operations)
3. [User Listing & Search](#user-listing--search)
4. [User Administration](#user-administration)
5. [Role Management](#role-management)
6. [Statistics](#statistics)

---

## Profile Management

### 1. Get User Profile
Get authenticated user's complete profile with all relationships.

**Endpoint**: `GET /api/user/profile`

**Headers**:
```
Authorization: Bearer {token}
```

**Response**:
```json
{
    "status": 1,
    "message": "Profile retrieved successfully",
    "data": {
        "id": 123,
        "username": "+256700123456",
        "name": "John Doe",
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone_number": "+256700123456",
        "phone_number_2": "+256700654321",
        "avatar": "public/storage/images/avatar.jpg",
        "address": "Kampala, Uganda",
        "nin": "CM12345678901234",
        "gender": "Male",
        "status": "Active",
        "user_type": "Admin",
        "sub_county_id": 15,
        "sub_county_name": "Kampala Central",
        "district_id": 1,
        "district_name": "Kampala",
        "created_at": "2025-01-01 10:00:00",
        "roles": [...],
        "permissions": [...],
        "role_names": ["Administrator", "Farmer"],
        "role_slugs": ["administrator", "farmer"],
        "permission_names": ["view_users", "create_animals"]
    }
}
```

---

### 2. Update Profile
Update user's profile information.

**Endpoint**: `POST /api/user/update-profile`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Parameters**:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| first_name | string | No | First name (max 255) |
| last_name | string | No | Last name (max 255) |
| name | string | No | Full name (max 255) |
| email | string | No | Email address |
| phone_number | string | No | Primary phone |
| phone_number_2 | string | No | Secondary phone |
| address | string | No | Physical address |
| nin | string | No | National ID |
| gender | string | No | Male or Female |
| sub_county_id | integer | No | Sub-county ID |
| district_id | integer | No | District ID |
| avatar | file | No | Profile image |

**Example Request**:
```bash
curl -X POST "https://your-domain.com/api/user/update-profile" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "first_name=John" \
  -F "last_name=Doe" \
  -F "phone_number=+256700123456" \
  -F "email=john@example.com" \
  -F "gender=Male" \
  -F "avatar=@/path/to/image.jpg"
```

**Response**:
```json
{
    "status": 1,
    "message": "Profile updated successfully",
    "data": {
        "id": 123,
        "name": "John Doe",
        "first_name": "John",
        "last_name": "Doe",
        ...
    }
}
```

**Features**:
- ✅ Auto-generates full name from first + last name
- ✅ Validates phone number format
- ✅ Checks for duplicate phone numbers
- ✅ Auto-sets district from sub-county
- ✅ Handles avatar upload
- ✅ Partial updates (only provided fields)

---

## Password Operations

### 3. Change Password
User changes their own password.

**Endpoint**: `POST /api/user/change-password`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Parameters**:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| current_password | string | Yes | Current password |
| new_password | string | Yes | New password (min 4 chars) |
| new_password_confirmation | string | Yes | Password confirmation |

**Example Request**:
```json
{
    "current_password": "oldpass123",
    "new_password": "newpass456",
    "new_password_confirmation": "newpass456"
}
```

**Response**:
```json
{
    "status": 1,
    "message": "Password changed successfully",
    "data": null
}
```

**Validation**:
- Verifies current password
- Requires password confirmation
- Minimum 4 characters

---

### 4. Reset Password (Admin)
Administrator resets user's password.

**Endpoint**: `POST /api/users/{id}/reset-password`

**Headers**:
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Parameters**:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| user_id | integer | Yes | Target user ID |
| new_password | string | Yes | New password (min 4) |

**Example Request**:
```json
{
    "user_id": 456,
    "new_password": "resetpass123"
}
```

**Response**:
```json
{
    "status": 1,
    "message": "Password reset successfully",
    "data": null
}
```

**Authorization**: Requires administrator role

---

## User Listing & Search

### 5. List Users with Filters
Get paginated list of users with advanced filtering and search.

**Endpoint**: `GET /api/users/list`

**Headers**:
```
Authorization: Bearer {token}
```

**Query Parameters**:

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| search | string | Search by name, phone, email, username | `John` |
| status | string | Filter by status | `Active`, `Inactive` |
| user_type | string | Filter by user type | `Admin`, `Worker` |
| gender | string | Filter by gender | `Male`, `Female` |
| district_id | integer | Filter by district | `1` |
| sub_county_id | integer | Filter by sub-county | `15` |
| role_id | integer | Filter by role ID | `3` |
| role_slug | string | Filter by role slug | `farmer` |
| created_from | date | From date | `2025-01-01` |
| created_to | date | To date | `2025-12-31` |
| sort_by | string | Sort field | `name`, `created_at` |
| sort_dir | string | Sort direction | `asc`, `desc` |
| per_page | integer | Results per page | `20` (default) |
| page | integer | Page number | `1` |

**Example Requests**:

```bash
# Search for users named "John"
GET /api/users/list?search=John

# Get active farmers
GET /api/users/list?status=Active&role_slug=farmer

# Get users from Kampala district
GET /api/users/list?district_id=1

# Get female users created this year
GET /api/users/list?gender=Female&created_from=2025-01-01

# Get users sorted by name
GET /api/users/list?sort_by=name&sort_dir=asc&per_page=50
```

**Response**:
```json
{
    "status": 1,
    "message": "Users retrieved successfully",
    "data": {
        "users": [
            {
                "id": 123,
                "name": "John Doe",
                "phone_number": "+256700123456",
                "email": "john@example.com",
                "status": "Active",
                "roles_list": ["Administrator", "Farmer"],
                ...
            }
        ],
        "pagination": {
            "total": 150,
            "per_page": 20,
            "current_page": 1,
            "last_page": 8,
            "from": 1,
            "to": 20
        }
    }
}
```

**Search Fields**:
- Name (first, last, full)
- Phone number
- Email
- Username

---

### 6. Get User Details
Get detailed information about a specific user.

**Endpoint**: `GET /api/users/{id}`

**Headers**:
```
Authorization: Bearer {token}
```

**Example Request**:
```bash
GET /api/users/123
```

**Response**:
```json
{
    "status": 1,
    "message": "User details retrieved successfully",
    "data": {
        "id": 123,
        "name": "John Doe",
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone_number": "+256700123456",
        "roles": [...],
        "permissions": [...],
        "role_names": ["Administrator", "Farmer"],
        "role_slugs": ["administrator", "farmer"],
        "sub_county_name": "Kampala Central",
        "district_name": "Kampala",
        ...
    }
}
```

---

### 7. Get Users by Role
Get all users with a specific role.

**Endpoint**: `GET /api/users/by-role/{roleSlug}`

**Headers**:
```
Authorization: Bearer {token}
```

**Query Parameters**:
| Parameter | Type | Description |
|-----------|------|-------------|
| search | string | Search query |
| status | string | Filter by status |
| per_page | integer | Results per page |

**Example Requests**:
```bash
# Get all farmers
GET /api/users/by-role/farmer

# Get active butchery staff
GET /api/users/by-role/butchery?status=Active

# Search butchers
GET /api/users/by-role/butchery?search=John
```

**Response**:
```json
{
    "status": 1,
    "message": "Users retrieved successfully",
    "data": {
        "role": {
            "id": 21,
            "name": "Butchery Shop",
            "slug": "butchery"
        },
        "users": [...],
        "pagination": {...}
    }
}
```

---

## User Administration

### 8. Update User Status
Activate or deactivate a user account.

**Endpoint**: `POST /api/users/{id}/status`

**Headers**:
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Parameters**:
| Field | Type | Required | Values |
|-------|------|----------|--------|
| status | string | Yes | `Active`, `Inactive` |

**Example Request**:
```json
{
    "status": "Inactive"
}
```

**Response**:
```json
{
    "status": 1,
    "message": "User status updated successfully",
    "data": {
        "id": 123,
        "name": "John Doe",
        "status": "Inactive",
        ...
    }
}
```

**Authorization**: Requires administrator role

---

## Role Management

### 9. List All Roles
Get all available roles in the system.

**Endpoint**: `GET /api/roles/list`

**Headers**:
```
Authorization: Bearer {token}
```

**Response**:
```json
{
    "status": 1,
    "message": "Roles retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Super Administrator",
            "slug": "administrator",
            "created_at": "2024-01-01 00:00:00"
        },
        {
            "id": 3,
            "name": "Farmer",
            "slug": "farmer",
            "created_at": "2024-01-01 00:00:00"
        },
        {
            "id": 21,
            "name": "Butchery Shop",
            "slug": "butchery",
            "created_at": "2024-06-15 00:00:00"
        }
    ]
}
```

---

### 10. Update User Roles (Replace All)
Replace all user's roles with new set.

**Endpoint**: `POST /api/users/{id}/roles`

**Headers**:
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Parameters**:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| role_ids | array | Yes | Array of role IDs |

**Example Request**:
```json
{
    "role_ids": [3, 21]
}
```

**Response**:
```json
{
    "status": 1,
    "message": "User roles updated successfully",
    "data": {
        "id": 123,
        "name": "John Doe",
        "roles": [...],
        "role_names": ["Farmer", "Butchery Shop"]
    }
}
```

**Note**: This removes all existing roles and assigns only the provided ones.

---

### 11. Add Role to User
Add a single role to user without removing existing roles.

**Endpoint**: `POST /api/users/{id}/roles/add`

**Headers**:
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Parameters**:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| role_id | integer | Yes | Role ID to add |

**Example Request**:
```json
{
    "role_id": 21
}
```

**Response**:
```json
{
    "status": 1,
    "message": "Role added successfully",
    "data": {
        "id": 123,
        "name": "John Doe",
        "roles": [...],
        "role_names": ["Farmer", "Butchery Shop"]
    }
}
```

**Validation**: Returns error if user already has this role.

---

### 12. Remove Role from User
Remove a specific role from user.

**Endpoint**: `POST /api/users/{id}/roles/remove`

**Headers**:
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Parameters**:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| role_id | integer | Yes | Role ID to remove |

**Example Request**:
```json
{
    "role_id": 21
}
```

**Response**:
```json
{
    "status": 1,
    "message": "Role removed successfully",
    "data": {
        "id": 123,
        "name": "John Doe",
        "roles": [...],
        "role_names": ["Farmer"]
    }
}
```

---

## Statistics

### 13. Get User Statistics
Get comprehensive user statistics and analytics.

**Endpoint**: `GET /api/users/statistics`

**Headers**:
```
Authorization: Bearer {token}
```

**Response**:
```json
{
    "status": 1,
    "message": "Statistics retrieved successfully",
    "data": {
        "total_users": 1523,
        "active_users": 1420,
        "inactive_users": 103,
        "users_by_gender": {
            "male": 892,
            "female": 631
        },
        "recent_registrations": {
            "today": 12,
            "this_week": 87,
            "this_month": 342
        },
        "users_by_role": [
            {
                "role": "Farmer",
                "slug": "farmer",
                "count": 1234
            },
            {
                "role": "Butchery Shop",
                "slug": "butchery",
                "count": 45
            },
            {
                "role": "Administrator",
                "slug": "administrator",
                "count": 12
            }
        ]
    }
}
```

---

## Error Responses

All endpoints return consistent error responses:

### Validation Error (422)
```json
{
    "status": 0,
    "message": "Validation failed",
    "data": {
        "errors": {
            "email": ["The email has already been taken."],
            "phone_number": ["The phone number field is required."]
        }
    }
}
```

### Unauthorized (403)
```json
{
    "status": 0,
    "message": "Unauthorized",
    "data": null
}
```

### Not Found (404)
```json
{
    "status": 0,
    "message": "User not found",
    "data": null
}
```

### Server Error (500)
```json
{
    "status": 0,
    "message": "Failed to retrieve users: [error details]",
    "data": null
}
```

---

## Authentication

All endpoints require authentication via Bearer token:

```bash
curl -X GET "https://your-domain.com/api/user/profile" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Getting a Token**: Use the existing login endpoint:
```bash
POST /api/login
{
    "username": "+256700123456",
    "password": "your_password"
}
```

---

## Use Cases

### Example 1: Complete Profile Update
```bash
curl -X POST "https://your-domain.com/api/user/update-profile" \
  -H "Authorization: Bearer TOKEN" \
  -F "first_name=John" \
  -F "last_name=Doe" \
  -F "email=john@example.com" \
  -F "phone_number=+256700123456" \
  -F "gender=Male" \
  -F "sub_county_id=15" \
  -F "address=Kampala, Uganda" \
  -F "avatar=@profile.jpg"
```

### Example 2: Search Active Farmers in Kampala
```bash
GET /api/users/list?role_slug=farmer&status=Active&district_id=1&per_page=50
```

### Example 3: Assign Butchery Role to User
```bash
curl -X POST "https://your-domain.com/api/users/123/roles/add" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"role_id": 21}'
```

### Example 4: Get All Butchery Staff
```bash
GET /api/users/by-role/butchery?status=Active
```

### Example 5: Change User Status
```bash
curl -X POST "https://your-domain.com/api/users/456/status" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "Inactive"}'
```

---

## Features Summary

### ✅ Profile Management
- Complete profile retrieval with relationships
- Partial profile updates
- Avatar upload
- Location information (district/sub-county)
- Phone number validation
- Duplicate detection

### ✅ Password Operations
- User self-service password change
- Administrator password reset
- Password confirmation
- Secure hashing

### ✅ Advanced Search & Filtering
- Multi-field search (name, phone, email, username)
- Filter by: status, gender, location, role, date
- Flexible sorting
- Pagination

### ✅ Role Management
- List all available roles
- Replace all user roles
- Add single role
- Remove single role
- Get users by specific role

### ✅ User Administration
- Activate/deactivate accounts
- View detailed user info
- Comprehensive statistics
- Role-based authorization

### ✅ Developer-Friendly
- Consistent response format
- Clear error messages
- Comprehensive validation
- RESTful design
- Well-documented

---

## Testing

### 1. Test Profile Retrieval
```bash
curl -X GET "http://localhost/api/user/profile" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 2. Test User Search
```bash
curl -X GET "http://localhost/api/users/list?search=John&status=Active"
```

### 3. Test Role Assignment
```bash
curl -X POST "http://localhost/api/users/123/roles/add" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"role_id": 21}'
```

---

## Database Tables Used

- `admin_users` - User information
- `admin_roles` - Available roles
- `admin_role_users` - User-role relationships
- `admin_permissions` - Permissions
- `admin_user_permissions` - User-permission relationships
- `location` - Districts and sub-counties

---

## Security Considerations

1. **Authentication**: All endpoints require valid Bearer token
2. **Authorization**: Admin functions check for administrator role
3. **Password Security**: Uses PHP password_hash() with bcrypt
4. **Input Validation**: Comprehensive validation on all inputs
5. **SQL Injection Protection**: Uses Eloquent ORM and parameterized queries
6. **Duplicate Prevention**: Checks for existing phone/email before updates

---

## Performance Optimization

1. **Eager Loading**: Loads relationships efficiently
2. **Pagination**: All list endpoints support pagination
3. **Selective Fields**: Only loads required fields
4. **Indexed Queries**: Uses indexed columns (id, phone_number, email)
5. **Caching Ready**: Can be enhanced with Redis/Memcached

---

## Future Enhancements

Potential additions:
1. Two-factor authentication
2. Password reset via email/SMS
3. Activity logging
4. Bulk user operations
5. User export (CSV/Excel)
6. Advanced analytics dashboard
7. User groups/teams
8. Custom permissions per user

---

**Documentation Version**: 1.0  
**Last Updated**: November 9, 2025  
**Controller**: `ApiUserManagementController.php`  
**Status**: ✅ Complete and Tested
