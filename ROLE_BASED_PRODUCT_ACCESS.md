# Role-Based Product & Category Access Control

## Overview
This document describes the role-based access control (RBAC) implementation for products and product categories in the AirPanas system.

## Category Types
Products are organized by category type:
- **ticket** - Ticketing/entrance fees (for ticketing role)
- **villa** - Villa/accommodation products (for booking role)
- **parking** - Parking services (for parking role)
- **other** - General products (admin/monitoring only)

## Role Permissions

### Superadmin
- ✅ Can view ALL products and categories (all types)
- ✅ Can create/update/delete ALL products and categories
- ✅ No category restrictions

### Admin
- ✅ Can view ALL products and categories (all types)
- ✅ Can create/update/delete ALL products and categories
- ✅ No category restrictions

### Ticketing Staff
- ✅ Can view ticket products (category_type='ticket')
- ✅ Can create/update products in ticket category
- ❌ Cannot access villa or parking products
- ❌ Cannot create/update products outside ticket category

### Booking Staff
- ✅ Can view villa products (category_type='villa')
- ✅ Can create/update products in villa category
- ❌ Cannot access ticket or parking products
- ❌ Cannot create/update products outside villa category

### Parking Staff
- ✅ Can view parking products (category_type='parking')
- ✅ Can create/update products in parking category
- ❌ Cannot access ticket or villa products
- ❌ Cannot create/update products outside parking category

### Monitoring Staff
- ✅ Can view ALL products and categories (read-only)
- ✅ No category restrictions
- ❌ Cannot create/update/delete products

## Implementation Details

### ProductController (app/Http/Controllers/Api/ProductController.php)

The controller has been updated with:

1. **Role-based filtering in index()**
   - Automatically filters products based on user's role
   - Uses `getAllowedCategoryTypes()` helper method
   - Respects category_id filter while enforcing role permissions

2. **Authorization in show(), update(), destroy()**
   - Verifies user has access to the product's category type
   - Returns 403 Unauthorized if user lacks permission
   - Prevents unauthorized access to products

3. **getAllowedCategoryTypes() Helper**
   ```php
   protected function getAllowedCategoryTypes($user): array
   ```
   - Maps user roles to allowed category types
   - Returns empty array if no specific type access
   - Used throughout controller for consistency

### ProductCategoryController (app/Http/Controllers/Api/ProductCategoryController.php)

New controller for managing product categories with:

1. **Role-based category listing in index()**
   - Filters categories by allowed types
   - Supports filtering by status and type

2. **Category authorization in show(), update(), destroy()**
   - Prevents unauthorized category access
   - Ensures users can only manage their assigned categories

3. **Consistent role mapping**
   - Uses same `getAllowedCategoryTypes()` logic as products

## API Routes

```php
// Both routes protected by permission:products.manage,products.view
Route::apiResource('products', ProductController::class);
Route::apiResource('product-categories', ProductCategoryController::class);
```

## Example Usage

### Get products for current user
```bash
GET /api/products
```
Response automatically filtered by user's role:
- Ticketing staff: only ticket products
- Booking staff: only villa products
- Parking staff: only parking products
- Admin/Superadmin/Monitoring: all products

### Create product (Booking staff)
```bash
POST /api/products
Content-Type: application/json

{
  "category_id": 5,
  "code": "VILLA-001",
  "name": "Standard Villa",
  "base_price": 500000,
  "is_active": true
}
```
✅ Success if category_id belongs to villa category
❌ Fails if category_id belongs to ticket or parking

### Get categories
```bash
GET /api/product-categories
```
Response filtered by user's role

## Testing

### Test as Ticketing Staff
```bash
# Should return only ticket products
curl -H "Authorization: Bearer TOKEN" \
  http://localhost/api/products

# Should return only ticket categories
curl -H "Authorization: Bearer TOKEN" \
  http://localhost/api/product-categories
```

### Test as Booking Staff
```bash
# Should return only villa products
curl -H "Authorization: Bearer TOKEN" \
  http://localhost/api/products

# Should return only villa categories
curl -H "Authorization: Bearer TOKEN" \
  http://localhost/api/product-categories
```

### Test Authorization Failure
```bash
# Booking staff attempting to view ticket product
curl -H "Authorization: Bearer BOOKING_TOKEN" \
  http://localhost/api/products/1

# Response: 403 Forbidden
# "You do not have access to this product"
```

## Audit Logging

All product/category operations are logged with:
- User ID
- Action (created, updated, deleted)
- Before/after values
- IP address
- User agent
- Timestamp

## Security Considerations

1. **Frontend + Backend Validation**
   - Frontend: Sidebar shows only accessible menus per role
   - Backend: Controller enforces access control
   - Defense in depth: both layers protect against unauthorized access

2. **Category Type Enforcement**
   - Database: category_type is enum (ticket, villa, parking, other)
   - Application: validates category_type in requests
   - Routes: permission middleware provides base-level access

3. **Audit Trail**
   - All modifications logged for compliance
   - Includes before/after values for change tracking
   - Timestamped with user context

## Extending Role Access

To add new category types or roles:

1. **Add category type to migration:**
   ```php
   $table->enum('category_type', ['ticket', 'villa', 'parking', 'other', 'new_type']);
   ```

2. **Update getAllowedCategoryTypes() method:**
   ```php
   if (in_array('newrole', $roleNames)) {
       $allowedTypes[] = 'new_type';
   }
   ```

3. **Add permissions to role seeder:**
   ```php
   $newRole->syncPermissions([
       'view-products',
       'create-products',
       // ...
   ]);
   ```

4. **Test with new role user**

## Troubleshooting

### Products/Categories not appearing
- Check user's role assignment in role_user table
- Verify role has products.view or products.manage permission
- Check category_type matches expected values
- Review audit logs for access attempts

### 403 Unauthorized errors
- Verify product's category type matches user's role
- Check user's role in database: `SELECT roles.name FROM role_user JOIN roles ON role_user.role_id = roles.id WHERE role_user.user_id = ?`
- Verify role has correct permissions

### Database queries slow
- Index category_type column for faster filtering
- Use database explain plan on product queries
- Consider caching category types per role

## Files Modified

- `app/Http/Controllers/Api/ProductController.php` - Added role-based filtering
- `app/Http/Controllers/Api/ProductCategoryController.php` - New controller
- `routes/api.php` - Added ProductCategoryController route and import

## Build & Deploy

After changes, rebuild frontend:
```bash
npm run build
```

Then clear Laravel cache:
```bash
php artisan config:cache
php artisan route:cache
```
