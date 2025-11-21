# API Documentation - AirPanas Tourism Admin System

## Base URL
```
Production: https://your-domain.com/api
Development: http://localhost/api
```

## Authentication

All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {your-token-here}
```

### Login
**POST** `/api/auth/login`

**Request Body:**
```json
{
  "username": "superadmin",
  "password": "password"
}
```

**Response (200 OK):**
```json
{
  "token": "1|abc123...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "username": "superadmin",
    "full_name": "Super Administrator",
    "email": "admin@airpanas.local",
    "role_id": 1,
    "role_name": "superadmin"
  }
}
```

**Error Responses:**
- **401 Unauthorized**: Invalid credentials
- **423 Locked**: Account temporarily locked
- **429 Too Many Requests**: Rate limit exceeded

### Logout
**POST** `/api/auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "message": "Successfully logged out"
}
```

### Get Current User
**GET** `/api/auth/me`

**Headers:** `Authorization: Bearer {token}`

---

## Users Management

### List Users
**GET** `/api/users`

**Permission Required:** `users.manage`

**Query Parameters:**
- `page` (int): Page number
- `per_page` (int): Items per page
- `search` (string): Search by username or full_name
- `role_id` (int): Filter by role
- `is_block` (bool): Filter by block status

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "username": "superadmin",
      "full_name": "Super Administrator",
      "email": "admin@airpanas.local",
      "role_id": 1,
      "role_name": "superadmin",
      "is_block": false,
      "last_login_at": "2025-11-20T05:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

### Create User
**POST** `/api/users`

**Permission Required:** `users.manage`

**Request Body:**
```json
{
  "username": "newuser",
  "password": "SecurePassword123",
  "full_name": "New User",
  "email": "newuser@airpanas.local",
  "role_id": 2
}
```

**Validation Rules:**
- `username`: required, unique, max:50, alphanumeric + underscore
- `password`: required, min:8
- `full_name`: required, max:100
- `email`: required, email, unique
- `role_id`: required, exists in roles table

---

## Products Management

### List Products
**GET** `/api/products`

**Permission Required:** `products.view` OR `products.manage`

**Query Parameters:**
- `page`, `per_page`, `search`
- `category_id` (int): Filter by category
- `status` (string): 'active' or 'inactive'
- `product_type` (string): Filter by type

### Create Product
**POST** `/api/products`

**Permission Required:** `products.manage`

**Request Body:**
```json
{
  "category_id": 1,
  "product_code": "PROD001",
  "name": "Room Standard",
  "description": "Standard room with breakfast",
  "product_type": "room",
  "unit": "night",
  "default_price": 500000,
  "status": "active"
}
```

---

## Bookings Management

### List Bookings
**GET** `/api/bookings`

**Permission Required:** `bookings.view` OR `bookings.manage`

**Query Parameters:**
- `status`: draft, pending, confirmed, checked_in, checked_out, cancelled
- `from`: Check-in date from (YYYY-MM-DD)
- `to`: Check-in date to (YYYY-MM-DD)
- `customer`: Search by customer name or phone

### Create Booking
**POST** `/api/bookings`

**Permission Required:** `bookings.create` OR `bookings.manage`

**Request Body:**
```json
{
  "customer_name": "John Doe",
  "customer_phone": "081234567890",
  "checkin": "2025-11-25 14:00:00",
  "checkout": "2025-11-27 12:00:00",
  "night_count": 2,
  "room_count": 1,
  "total_amount": 1000000,
  "discount_amount": 0,
  "dp_amount": 300000,
  "notes": "Late check-in requested",
  "booking_units": [
    {
      "product_id": 1,
      "unit_code": "R101",
      "qty": 1,
      "rate": 500000,
      "discount": 0,
      "subtotal": 1000000
    }
  ]
}
```

**Response (201 Created):**
```json
{
  "id": 123,
  "booking_code": "BKG20251120001",
  "customer_name": "John Doe",
  "status": "pending",
  "total_amount": 1000000
}
```

**Validation:**
- `checkout` must be after `checkin`
- `booking_code` is auto-generated and unique
- `created_by` is automatically set to authenticated user

### Update Booking Status
**PUT** `/api/bookings/{id}/status`

**Permission Required:** `bookings.manage`

**Request Body:**
```json
{
  "status": "confirmed"
}
```

**Valid Statuses:**
- `draft` → `pending`
- `pending` → `confirmed` | `cancelled`
- `confirmed` → `checked_in` | `cancelled`
- `checked_in` → `checked_out`

### Add Payment to Booking
**POST** `/api/bookings/{id}/payments`

**Permission Required:** `payments.create`

**Request Body:**
```json
{
  "amount": 700000,
  "payment_method": "cash",
  "payment_reference": "CASH-001",
  "paid_at": "2025-11-20 10:30:00"
}
```

---

## Ticket Sales

### Create Ticket Sale
**POST** `/api/ticket-sales`

**Permission Required:** `sales.create`

**Request Body:**
```json
{
  "invoice_no": "INV20251120001",
  "gross_amount": 150000,
  "discount_amount": 10000,
  "net_amount": 140000,
  "status": "paid",
  "items": [
    {
      "product_id": 5,
      "qty": 2,
      "unit_price": 50000,
      "discount_amount": 5000,
      "line_total": 95000
    },
    {
      "product_id": 6,
      "qty": 1,
      "unit_price": 50000,
      "discount_amount": 5000,
      "line_total": 45000
    }
  ]
}
```

**Response (201 Created):**
```json
{
  "id": 555,
  "invoice_no": "INV20251120001",
  "sale_date": "2025-11-20T10:45:00.000000Z",
  "cashier_id": 3,
  "total_qty": 3,
  "net_amount": 140000,
  "status": "paid"
}
```

**Auto-Generated Fields:**
- `invoice_no`: Auto if not provided (format: INV{timestamp}{random})
- `sale_date`: Current datetime
- `cashier_id`: Authenticated user
- `total_qty`: Sum of all items qty

---

## Reports

### Daily Sales Report
**GET** `/api/reports/daily-sales`

**Permission Required:** `reports.view`

**Query Parameters:**
- `from` (date): Start date (YYYY-MM-DD)
- `to` (date): End date (YYYY-MM-DD)
- `cashier_id` (int): Filter by cashier

**Response:**
```json
{
  "data": [
    {
      "sale_date": "2025-11-20",
      "cashier_id": 3,
      "cashier_name": "Cashier User",
      "total_transactions": 15,
      "total_qty": 45,
      "gross_amount": 2250000,
      "discount_amount": 150000,
      "net_amount": 2100000
    }
  ]
}
```

### Booking Report
**GET** `/api/reports/bookings`

**Permission Required:** `reports.view`

**Query Parameters:**
- `from`, `to`: Date range
- `status`: Filter by status
- `group_by`: daily, weekly, monthly

---

## Error Responses

### 400 Bad Request
```json
{
  "error": "Validation Error",
  "message": "The given data was invalid.",
  "errors": {
    "username": ["The username field is required."]
  }
}
```

### 401 Unauthorized
```json
{
  "error": "Unauthorized",
  "message": "Authentication required"
}
```

### 403 Forbidden
```json
{
  "error": "Forbidden",
  "message": "You do not have permission to perform this action"
}
```

### 429 Too Many Requests
```json
{
  "error": "Too Many Attempts",
  "message": "Too many login attempts. Please try again in 60 seconds.",
  "retry_after": 60
}
```

---

## Security Features

### Rate Limiting
- Login endpoint: 5 attempts per minute per IP
- Login by username: 3 attempts per 5 minutes
- Account lockout: 15 minutes after failed attempts
- General API: 60 requests per minute

### Authentication
- Token-based (Laravel Sanctum)
- Token expiration: 60 minutes (configurable)
- Password hashing: bcrypt (upgradeable to argon2id)

### Authorization (RBAC)
Permissions are checked via middleware:
- `permission:users.manage` - Can manage users
- `permission:products.manage` - Can manage products
- `permission:bookings.create,bookings.manage` - Can create OR manage bookings
- Wildcard `*` for superadmin

### Audit Logging
All critical actions are logged:
- User login/logout
- CRUD operations on bookings, sales, users
- Permission denied attempts
- Stored: user_id, action, resource, ip_addr, user_agent, timestamp

---

## Test Credentials

```
Superadmin:
  username: superadmin
  password: password
  
Admin:
  username: admin
  password: password
  
Cashier:
  username: cashier
  password: password
```

**⚠️ CHANGE ALL PASSWORDS IN PRODUCTION!**

---

## Example cURL Requests

### Login
```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"superadmin","password":"password"}'
```

### List Bookings with Token
```bash
TOKEN="your-token-here"
curl -X GET "http://localhost/api/bookings?status=confirmed" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### Create Booking
```bash
TOKEN="your-token-here"
curl -X POST http://localhost/api/bookings \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "customer_phone": "081234567890",
    "checkin": "2025-12-01 14:00:00",
    "checkout": "2025-12-03 12:00:00",
    "night_count": 2,
    "room_count": 1,
    "total_amount": 1000000,
    "booking_units": [
      {
        "product_id": 1,
        "unit_code": "R101",
        "qty": 1,
        "rate": 500000,
        "discount": 0,
        "subtotal": 1000000
      }
    ]
  }'
```
