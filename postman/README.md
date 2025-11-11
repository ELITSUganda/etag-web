# E-TAG Livestock Management API - Postman Collection

Complete Postman workspace for the E-TAG Livestock Management System API. This collection provides comprehensive API testing capabilities with proper authentication, environment variables, and organized endpoints.

## 📁 Collection Structure

The API is organized into 10 logical collections:

1. **01-Authentication** - User authentication, registration, and profile management
2. **02-Animals** - Animal registration, transfers, images, and QR codes
3. **03-Farms** - Farm management, workers, reports, and inspections
4. **04-Events** - Animal health events, treatments, and medical records
5. **05-Movements** - Movement permits (LMPR), tracking, and checkpoints
6. **06-Products-Shop** - Marketplace, orders, drugs, chat, and payments
7. **07-Vaccination** - Vaccination programs, schedules, and stock management
8. **08-Slaughter** - Slaughter houses, records, and meat distribution
9. **09-Utilities** - Locations, districts, AJAX endpoints, and notifications
10. **10-Farm-Analytics** - Dashboard KPIs, graphs, and reporting

## 🚀 Quick Start Guide

### Step 1: Import Environment

1. Open Postman
2. Click **Environments** in the left sidebar
3. Click **Import** button
4. Select `environment.json` from this folder
5. You should see **E-TAG API Environment** appear in your environments list

### Step 2: Import Collections

1. Click **Collections** in the left sidebar
2. Click **Import** button
3. Select all 10 collection files (`.postman_collection.json`)
4. Click **Import** - all collections will appear in your workspace

### Step 3: Configure Environment

1. Select **E-TAG API Environment** from the environment dropdown (top right)
2. Click the eye icon 👁️ to view/edit environment variables
3. Update the following variables:

   **For Local Development:**
   - `base_url`: `http://localhost:8888/etag-web/api`
   
   **For Production:**
   - `base_url`: `https://u-lits.com/api`

4. Save the environment

### Step 4: Authenticate

1. Open **01-Authentication** collection
2. Run the **Login** request with valid credentials:
   ```json
   {
       "phone_number": "0700000000",
       "password": "your_password"
   }
   ```
3. The token and user ID will be **automatically saved** to environment variables
4. All subsequent requests will use these credentials automatically

## 🔐 Authentication

The API uses **Bearer Token** authentication. After logging in via the **Login** endpoint in the Authentication collection:

- `token` is automatically saved to environment
- `administrator_id` is automatically saved to environment
- All other collections automatically use these values via `{{token}}` and `{{administrator_id}}`

**No manual token copying needed!** The test scripts handle this automatically.

## 📝 Environment Variables

The following variables are available in the environment:

| Variable | Description | Auto-set? |
|----------|-------------|-----------|
| `base_url` | API base URL | Manual |
| `token` | Authentication token | ✅ Auto (after login) |
| `administrator_id` | Logged-in user ID | ✅ Auto (after login) |
| `farm_id` | Selected farm ID | ✅ Auto (after farm creation) |
| `animal_id` | Selected animal ID | ✅ Auto (after animal creation) |
| `movement_id` | Selected movement permit ID | ✅ Auto (after movement creation) |
| `product_id` | Selected product ID | ✅ Auto (after product creation) |
| `order_id` | Selected order ID | ✅ Auto (after order creation) |

## 🎯 Usage Tips

### Switching Environments

To switch between local and production:

1. Click environment dropdown (top right)
2. Click "Edit" on **E-TAG API Environment**
3. Change `base_url` value
4. Save

Or create separate environments for local and production.

### Testing Workflow

**Recommended testing order:**

1. **Authentication** → Login first
2. **Farms** → Create/select a farm
3. **Animals** → Register animals to the farm
4. **Events** → Record health events
5. **Movements** → Create movement permits
6. **Products/Shop** → Browse and order products
7. **Vaccination** → Schedule vaccinations
8. **Slaughter** → Record slaughter operations
9. **Analytics** → View dashboard and reports

### Using AJAX Endpoints

The **Utilities** collection includes AJAX autocomplete endpoints:

- `ajax-animals` - Search animals by E-ID/V-ID
- `ajax-farms` - Search farms by holding code
- `ajax-users` - Search users by name
- `sub-counties`, `districts` - Location searches

Use these with `?q=search_term` parameter.

### File Uploads

For endpoints that accept files (animal images, documents):

1. Select the request
2. Go to **Body** tab
3. Select **form-data**
4. Set key type to **File**
5. Select your file
6. Add other required fields

Example fields:
```
photo: [File] select_image.jpg
administrator_id: {{administrator_id}}
animal_id: {{animal_id}}
```

## 🏗️ API Architecture

### Base Endpoints

- **Local**: `http://localhost:8888/etag-web/api`
- **Production**: `https://u-lits.com/api`

### Response Format

All responses follow this structure:

**Success:**
```json
{
    "code": 1,
    "message": "Success message",
    "data": { ... }
}
```

**Error:**
```json
{
    "code": 0,
    "message": "Error message",
    "data": null
}
```

### Pagination

List endpoints support pagination:
```
?page=1&per_page=20
```

### Common Parameters

Most endpoints require:
- `administrator_id` - Logged-in user ID (auto-included via environment)
- Token in headers (auto-included)

## 📊 Collection Details

### 01 - Authentication (13 endpoints)
- Login / Register / Logout
- Profile management (view, update, photo)
- Password change / recovery
- Account deletion
- FCM token registration

### 02 - Animals (21 endpoints)
- CRUD operations for animals
- Image uploads and galleries
- QR code generation
- Animal transfers between farms
- Search and filtering
- Status updates (alive, dead, sold)

### 03 - Farms (18 endpoints)
- Farm registration and management
- Worker management
- Farm inspections and reports
- Sub-county admin features
- Holding code generation
- Farm photos and documentation

### 04 - Events (15 endpoints)
- Health event recording (disease, treatment, death)
- Pregnancy and birth records
- Milk production tracking
- Weight monitoring
- Disease outbreak management
- Batch event recording

### 05 - Movements (14 endpoints)
- Movement permit creation (LMPR)
- Permit approval workflow
- GPS tracking and checkpoints
- Route validation
- Status updates
- Permit cancellation

### 06 - Products/Shop (25 endpoints)
- Product catalog (drugs, equipment, services)
- Shopping cart management
- Order processing and tracking
- Payment integration
- Chat and messaging
- Reviews and ratings
- Financial records

### 07 - Vaccination (11 endpoints)
- Vaccination program management
- Scheduling and reminders
- Stock management
- Batch vaccinations
- Coverage reports
- Disease-vaccine mapping

### 08 - Slaughter (9 endpoints)
- Slaughter house management
- Slaughter record creation
- Carcass tracking and assignment
- Meat distribution
- Quality inspection
- Session management

### 09 - Utilities (22 endpoints)
- Location data (districts, sub-counties, parishes)
- AJAX search endpoints
- Notifications
- Generic CRUD operations
- Drug dosages
- Image processing
- System utilities

### 10 - Farm Analytics (14 endpoints)
- Dashboard KPIs
- Growth trends and graphs
- Financial analytics
- Health event summaries
- Vaccination coverage reports
- Farm performance comparison
- Disease outbreak tracking
- Custom report generation
- Data export (Excel, PDF, CSV)

## 🔧 Troubleshooting

### Authentication Issues

**Problem**: Getting "Unauthorized" errors
- **Solution**: Run the Login request again, ensure token is saved

**Problem**: Token expired
- **Solution**: Login again to get a new token

### Base URL Issues

**Problem**: "Could not send request" errors
- **Solution**: Verify `base_url` in environment matches your server
- **Check**: For local MAMP, ensure MAMP is running on correct port

### Missing Variables

**Problem**: Variables showing as `{{variable_name}}` in requests
- **Solution**: Ensure environment is selected (top right dropdown)
- **Check**: Variables exist in environment (click eye icon 👁️)

### File Upload Issues

**Problem**: File upload failing
- **Solution**: Use `form-data` body type, not `raw` or `x-www-form-urlencoded`
- **Check**: Key type is set to "File" in Postman

## 📖 Additional Resources

- **API Documentation**: See individual collection descriptions
- **Error Codes**: Check response `code` field (1 = success, 0 = error)
- **Support**: Contact system administrator

## 🆕 Updates & Maintenance

### Adding New Endpoints

When the API adds new endpoints:

1. Open the relevant collection
2. Right-click → Add Request
3. Configure the request
4. Update this README if needed

### Updating Environment

To add new environment variables:

1. Click Environments → E-TAG API Environment
2. Click Edit
3. Add new variable
4. Save

### Version Control

Consider exporting collections periodically:

1. Right-click collection → Export
2. Save as Collection v2.1
3. Commit to version control

## 📄 License

This Postman collection is part of the E-TAG Livestock Management System.

---

**Last Updated**: November 11, 2025  
**API Version**: 1.0  
**Collections**: 10  
**Total Endpoints**: 162+

For technical support or API changes, contact the development team.
