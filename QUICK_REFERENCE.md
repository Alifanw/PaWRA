QUICK REFERENCE GUIDE - NEW FEATURES
====================================

USER CREDENTIALS FOR TESTING:
==============================

Booking Reports:
  Email: booking@airpanas.local
  Password: 123123
  Role: booking
  Access: /admin/reports/booking-report

Ticketing Reports:
  Email: ticket@airpanas.local
  Password: 123123
  Role: ticketing
  Access: /admin/reports/ticket-report

Admin (All Features):
  Email: admin@airpanas.local
  Password: 123123
  Role: admin

Super Admin:
  Email: superadmin@airpanas.local
  Password: Admin123!
  Role: superadmin

Monitoring/Dashboard:
  Email: monitor@airpanas.local
  Password: 123123
  Role: monitoring
  Access: /admin/product-codes (manage units)

====================================
NEW ENDPOINTS & ROUTES:
====================================

BOOKING REPORTS:
- GET /admin/reports/booking-report
- GET /admin/reports/booking-report/export (CSV)
- Roles: booking, admin, superadmin
- Filters: date range, status, product

TICKET REPORTS:
- GET /admin/reports/ticket-report
- GET /admin/reports/ticket-report/export (CSV)
- Roles: ticketing, admin, superadmin
- Filters: date range, status, product

AVAILABILITY API (Fixed):
- GET /api/availabilities?product_id=X&checkin=DATE&checkout=DATE
- Now returns correct available_count per unit
- No longer shows 0 units for available products

BULK ACTIONS (Working):
- POST /admin/product-codes/bulk-status
- DELETE /admin/product-codes/bulk-destroy
- Manage units in batch operations

====================================
FEATURE LOCATIONS:
====================================

Booking Availability Selector:
- Location: Booking creation form
- Component: AvailabilitySelector.jsx
- Behavior: Fetches /api/availabilities and displays available rooms
- Status: ✅ Now shows non-zero units

Product Code Management:
- Location: /admin/product-codes
- Component: ProductCodes/Index.jsx
- Features: List, add, edit, delete, bulk actions
- Filters: Product, category, status, search
- Status: ✅ Bulk actions functional

Booking Reports:
- Location: /admin/reports/booking-report
- Component: Reports/Bookings.jsx
- Features: Filters, stats, table, CSV export
- Permissions: view-booking-reports
- Status: ✅ Fully implemented

Ticket Reports:
- Location: /admin/reports/ticket-report
- Component: Reports/Tickets.jsx
- Features: Filters, stats, table, CSV export
- Permissions: view-ticket-reports
- Status: ✅ Fully implemented

====================================
DATABASE CHANGES:
====================================

product_availability table:
- Added: total_units INT DEFAULT 1
- Purpose: Track inventory per unit
- Values: All 120 existing units set to total_units=1

role_permission table:
- Added: view-booking-reports permission
- Added: view-ticket-reports permission
- Assigned to respective roles via seeder

====================================
CODE CHANGES SUMMARY:
====================================

Files Modified:
1. app/Models/ProductAvailability.php - Added 'total_units' to fillable
2. app/Http/Controllers/Api/AvailabilityController.php - Fixed availability logic
3. app/Providers/AuthServiceProvider.php - Registered new policies
4. database/seeders/FixRolePermissionSeeder.php - Added new permissions
5. routes/web.php - Added 4 new report routes

Files Created:
1. database/migrations/2025_12_14_000000_add_total_units_to_product_availability.php
2. app/Http/Controllers/Admin/ReportBookingController.php
3. app/Http/Controllers/Admin/ReportTicketController.php
4. app/Policies/BookingPolicy.php
5. app/Policies/TicketSalePolicy.php
6. resources/js/Pages/Admin/Reports/Tickets.jsx

====================================
NEXT STEPS (OPTIONAL IMPROVEMENTS):
====================================

1. Add role-based product filtering in form:
   - Filter products dropdown by user's role
   - Only booking users see villa products
   - Only ticketing users see ticket products
   - Implementation: Modify ProductCodeController.index() filtering

2. Add more report features:
   - Detailed breakdowns by product/category
   - Revenue charts and graphs
   - Comparison reports (month-to-month)
   - Advanced filtering options

3. Performance optimization:
   - Add caching for availability queries
   - Index frequently searched columns
   - Optimize booking conflict queries

4. Email notifications:
   - New booking/ticket creation notifications
   - Report generation and email delivery
   - Alerts for low inventory

====================================
TROUBLESHOOTING:
====================================

Q: Booking shows 0 available units
A: Make sure product has ProductAvailability records with status='available'
   Check: ProductAvailability::where('product_id', X)->count()

Q: Reports page says "No permission"
A: Login with correct user role (booking for booking reports, ticketing for ticket reports)
   Or login as admin/superadmin

Q: Bulk actions not working
A: Ensure JavaScript is enabled
   Check browser console for errors
   Verify CSRF token is included in request

Q: Ticketing login fails
A: Verify user exists: User::where('email', 'ticket@airpanas.local')->first()
   Check password: password_verify('123123', $user->password)
   Verify role: $user->hasRole('ticketing')

====================================
CONTACT & SUPPORT:
====================================

For issues with:
- Availability logic: Check AvailabilityController.php
- Report permissions: Check BookingPolicy.php & TicketSalePolicy.php
- Role-based access: Check RestrictByRole middleware
- Frontend display: Check React components in resources/js/Pages/

Last Updated: 2025-12-14
Status: Production Ready ✅
