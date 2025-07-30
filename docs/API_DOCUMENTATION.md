# API Documentation - KishansKraft E-commerce Platform

## Overview

The KishansKraft API is a RESTful web service that provides complete e-commerce functionality including user authentication, product management, shopping cart operations, order processing, and customer communication features.

## Base Information

- **Base URL**: `https://yourdomain.com/backend/api/`
- **API Version**: v1.0
- **Authentication**: JWT Bearer tokens
- **Content Type**: `application/json`
- **Character Encoding**: UTF-8

## Authentication

The API uses JWT (JSON Web Tokens) for authentication after OTP verification. Most endpoints require authentication except for public product listings and contact forms.

### Authentication Flow

1. **Send OTP** → User provides mobile number
2. **Verify OTP** → User provides OTP code received via SMS
3. **Get JWT Token** → System returns JWT token for authenticated requests
4. **Use Token** → Include JWT token in Authorization header for protected endpoints

### Authorization Header Format

```http
Authorization: Bearer <jwt_token_here>
```

## Common Response Format

All API responses follow a consistent JSON format:

**Success Response:**
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response data object
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Error description",
    "error_code": "ERROR_CODE",
    "details": {
        // Additional error details (optional)
    }
}
```

## HTTP Status Codes

| Status Code | Meaning |
|-------------|---------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request parameters |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Access denied |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Server error |

## Error Codes

| Error Code | Description |
|------------|-------------|
| INVALID_INPUT | Invalid or missing input parameters |
| AUTHENTICATION_FAILED | Authentication credentials invalid |
| INSUFFICIENT_PERMISSIONS | User lacks required permissions |
| RESOURCE_NOT_FOUND | Requested resource does not exist |
| VALIDATION_FAILED | Input validation failed |
| RATE_LIMIT_EXCEEDED | Too many requests |
| INTERNAL_ERROR | Internal server error |

---

# Authentication API

Base endpoint: `/backend/api/auth.php`

## Send OTP

Generates and sends an OTP code to the specified mobile number for authentication.

**Endpoint:** `POST /backend/api/auth.php`

**Authentication:** Not required

**Request Body:**
```json
{
    "action": "send_otp",
    "mobile": "9876543210"
}
```

**Parameters:**
- `action` (string, required): Must be "send_otp"
- `mobile` (string, required): 10-digit Indian mobile number

**Success Response (200):**
```json
{
    "success": true,
    "message": "OTP sent successfully",
    "data": {
        "mobile": "9876543210",
        "otp_expiry": 300,
        "message": "OTP has been sent to your mobile number"
    }
}
```

**Error Responses:**

*Invalid Mobile Number (400):*
```json
{
    "success": false,
    "message": "Invalid mobile number format",
    "error_code": "INVALID_INPUT"
}
```

*Rate Limit Exceeded (429):*
```json
{
    "success": false,
    "message": "Too many OTP requests. Please try again later.",
    "error_code": "RATE_LIMIT_EXCEEDED"
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/auth.php" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "send_otp",
    "mobile": "9876543210"
  }'
```

---

## Verify OTP

Verifies the OTP code and returns authentication status. For new users, indicates registration is required.

**Endpoint:** `POST /backend/api/auth.php`

**Authentication:** Not required

**Request Body:**
```json
{
    "action": "verify_otp",
    "mobile": "9876543210",
    "otp": "123456"
}
```

**Parameters:**
- `action` (string, required): Must be "verify_otp"
- `mobile` (string, required): Mobile number that received the OTP
- `otp` (string, required): 6-digit OTP code

**Success Response - Existing User (200):**
```json
{
    "success": true,
    "message": "OTP verified successfully",
    "data": {
        "is_new_user": false,
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "mobile": "9876543210",
            "address": "123 Main St, City"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "expires_at": "2025-07-31T12:00:00Z"
    }
}
```

**Success Response - New User (200):**
```json
{
    "success": true,
    "message": "OTP verified. Please complete registration.",
    "data": {
        "is_new_user": true,
        "mobile": "9876543210",
        "temp_token": "temp_jwt_token_for_registration"
    }
}
```

**Error Responses:**

*Invalid OTP (400):*
```json
{
    "success": false,
    "message": "Invalid or expired OTP",
    "error_code": "AUTHENTICATION_FAILED"
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/auth.php" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "verify_otp",
    "mobile": "9876543210",
    "otp": "123456"
  }'
```

---

## Register User

Completes user registration for new users after OTP verification.

**Endpoint:** `POST /backend/api/auth.php`

**Authentication:** Temporary token required

**Request Body:**
```json
{
    "action": "register",
    "mobile": "9876543210",
    "name": "John Doe",
    "email": "john@example.com",
    "address": "123 Main St, City, State, 123456"
}
```

**Parameters:**
- `action` (string, required): Must be "register"
- `mobile` (string, required): Verified mobile number
- `name` (string, required): Full name (3-50 characters)
- `email` (string, required): Valid email address
- `address` (string, required): Complete address

**Success Response (201):**
```json
{
    "success": true,
    "message": "Registration completed successfully",
    "data": {
        "user": {
            "id": 2,
            "name": "John Doe",
            "email": "john@example.com",
            "mobile": "9876543210",
            "address": "123 Main St, City, State, 123456",
            "created_at": "2025-07-30T10:30:00Z"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "expires_at": "2025-07-31T10:30:00Z"
    }
}
```

**Error Responses:**

*Validation Failed (422):*
```json
{
    "success": false,
    "message": "Validation failed",
    "error_code": "VALIDATION_FAILED",
    "details": {
        "email": "Email already exists",
        "name": "Name must be between 3-50 characters"
    }
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/auth.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer temp_jwt_token" \
  -d '{
    "action": "register",
    "mobile": "9876543210",
    "name": "John Doe",
    "email": "john@example.com",
    "address": "123 Main St, City"
  }'
```

---

## Get User Profile

Retrieves the current user's profile information.

**Endpoint:** `GET /backend/api/auth.php?action=profile`

**Authentication:** Required

**Success Response (200):**
```json
{
    "success": true,
    "message": "Profile retrieved successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "mobile": "9876543210",
        "address": "123 Main St, City",
        "created_at": "2025-07-01T00:00:00Z",
        "total_orders": 5,
        "total_spent": 2500.00
    }
}
```

**cURL Example:**
```bash
curl -X GET "https://yourdomain.com/backend/api/auth.php?action=profile" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

---

## Update Profile

Updates the current user's profile information.

**Endpoint:** `POST /backend/api/auth.php`

**Authentication:** Required

**Request Body:**
```json
{
    "action": "update_profile",
    "name": "John Smith",
    "email": "johnsmith@example.com",
    "address": "456 New St, City"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "id": 1,
        "name": "John Smith",
        "email": "johnsmith@example.com",
        "mobile": "9876543210",
        "address": "456 New St, City",
        "updated_at": "2025-07-30T10:30:00Z"
    }
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/auth.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -d '{
    "action": "update_profile",
    "name": "John Smith",
    "email": "johnsmith@example.com",
    "address": "456 New St, City"
  }'
```

---

## Logout

Invalidates the current JWT token and ends the user session.

**Endpoint:** `POST /backend/api/auth.php`

**Authentication:** Required

**Request Body:**
```json
{
    "action": "logout"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/auth.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -d '{"action": "logout"}'
```

---

# Products API

Base endpoint: `/backend/api/products.php`

## List Products

Retrieves a list of all available products with optional filtering and pagination.

**Endpoint:** `GET /backend/api/products.php?action=list`

**Authentication:** Not required

**Query Parameters:**
- `action` (string, required): Must be "list"
- `page` (integer, optional): Page number for pagination (default: 1)
- `limit` (integer, optional): Items per page (default: 20, max: 100)
- `category` (integer, optional): Filter by category ID
- `status` (string, optional): Filter by status (active, inactive)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Products retrieved successfully",
    "data": {
        "products": [
            {
                "id": 1,
                "name": "Premium Cold-Pressed Mustard Oil",
                "description": "Pure, traditional cold-pressed mustard oil",
                "price": 450.00,
                "weight": "1L",
                "category_id": 1,
                "category_name": "Mustard Oil",
                "stock_quantity": 100,
                "image_url": "/uploads/products/mustard-oil-1l.jpg",
                "status": "active",
                "created_at": "2025-07-01T00:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 1,
            "total_items": 3,
            "items_per_page": 20
        }
    }
}
```

**cURL Example:**
```bash
curl -X GET "https://yourdomain.com/backend/api/products.php?action=list&page=1&limit=10" \
  -H "Content-Type: application/json"
```

---

## Get Product Details

Retrieves detailed information about a specific product.

**Endpoint:** `GET /backend/api/products.php?action=detail&id={product_id}`

**Authentication:** Not required

**Path Parameters:**
- `id` (integer, required): Product ID

**Success Response (200):**
```json
{
    "success": true,
    "message": "Product details retrieved successfully",
    "data": {
        "id": 1,
        "name": "Premium Cold-Pressed Mustard Oil",
        "description": "Pure, traditional cold-pressed mustard oil made using age-old methods in Madhubani, Bihar. Rich in omega-3 fatty acids and natural antioxidants.",
        "price": 450.00,
        "weight": "1L",
        "category_id": 1,
        "category_name": "Mustard Oil",
        "stock_quantity": 100,
        "image_url": "/uploads/products/mustard-oil-1l.jpg",
        "features": [
            "100% Pure and Natural",
            "Cold-Pressed Method",
            "Rich in Omega-3",
            "No Additives or Preservatives"
        ],
        "nutritional_info": {
            "energy": "900 kcal per 100ml",
            "fat": "100g per 100ml",
            "omega_3": "15g per 100ml"
        },
        "status": "active",
        "created_at": "2025-07-01T00:00:00Z",
        "related_products": [
            {
                "id": 2,
                "name": "Premium Mustard Oil 500ml",
                "price": 250.00,
                "image_url": "/uploads/products/mustard-oil-500ml.jpg"
            }
        ]
    }
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Product not found",
    "error_code": "RESOURCE_NOT_FOUND"
}
```

**cURL Example:**
```bash
curl -X GET "https://yourdomain.com/backend/api/products.php?action=detail&id=1" \
  -H "Content-Type: application/json"
```

---

## Search Products

Searches products by name, description, or other criteria.

**Endpoint:** `GET /backend/api/products.php?action=search`

**Authentication:** Not required

**Query Parameters:**
- `action` (string, required): Must be "search"
- `q` (string, required): Search query (minimum 2 characters)
- `category` (integer, optional): Filter by category ID
- `min_price` (float, optional): Minimum price filter
- `max_price` (float, optional): Maximum price filter
- `page` (integer, optional): Page number (default: 1)
- `limit` (integer, optional): Items per page (default: 20)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Search completed successfully",
    "data": {
        "query": "mustard oil",
        "products": [
            {
                "id": 1,
                "name": "Premium Cold-Pressed Mustard Oil",
                "description": "Pure, traditional cold-pressed mustard oil",
                "price": 450.00,
                "weight": "1L",
                "category_name": "Mustard Oil",
                "image_url": "/uploads/products/mustard-oil-1l.jpg",
                "relevance_score": 0.95
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 1,
            "total_items": 2,
            "items_per_page": 20
        }
    }
}
```

**cURL Example:**
```bash
curl -X GET "https://yourdomain.com/backend/api/products.php?action=search&q=mustard&category=1&min_price=100&max_price=500" \
  -H "Content-Type: application/json"
```

---

## Get Categories

Retrieves all product categories.

**Endpoint:** `GET /backend/api/products.php?action=categories`

**Authentication:** Not required

**Success Response (200):**
```json
{
    "success": true,
    "message": "Categories retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Mustard Oil",
            "description": "Cold-pressed mustard oil products",
            "product_count": 3,
            "image_url": "/uploads/categories/mustard-oil.jpg"
        }
    ]
}
```

**cURL Example:**
```bash
curl -X GET "https://yourdomain.com/backend/api/products.php?action=categories" \
  -H "Content-Type: application/json"
```

---

## Get Featured Products

Retrieves featured or recommended products.

**Endpoint:** `GET /backend/api/products.php?action=featured`

**Authentication:** Not required

**Query Parameters:**
- `limit` (integer, optional): Number of featured products to return (default: 6)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Featured products retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Premium Cold-Pressed Mustard Oil",
            "description": "Pure, traditional cold-pressed mustard oil",
            "price": 450.00,
            "weight": "1L",
            "image_url": "/uploads/products/mustard-oil-1l.jpg",
            "featured_reason": "Best Seller"
        }
    ]
}
```

**cURL Example:**
```bash
curl -X GET "https://yourdomain.com/backend/api/products.php?action=featured&limit=4" \
  -H "Content-Type: application/json"
```

---

# Shopping Cart API

Base endpoint: `/backend/api/cart.php`

## Add Item to Cart

Adds a product to the user's shopping cart.

**Endpoint:** `POST /backend/api/cart.php`

**Authentication:** Required

**Request Body:**
```json
{
    "action": "add",
    "product_id": 1,
    "quantity": 2
}
```

**Parameters:**
- `action` (string, required): Must be "add"
- `product_id` (integer, required): ID of the product to add
- `quantity` (integer, required): Quantity to add (minimum: 1)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Product added to cart successfully",
    "data": {
        "cart_item": {
            "product_id": 1,
            "product_name": "Premium Cold-Pressed Mustard Oil",
            "price": 450.00,
            "quantity": 2,
            "total_amount": 900.00,
            "image_url": "/uploads/products/mustard-oil-1l.jpg"
        },
        "cart_summary": {
            "total_items": 2,
            "subtotal": 900.00,
            "shipping": 0.00,
            "total": 900.00
        }
    }
}
```

**Error Responses:**

*Product Not Found (404):*
```json
{
    "success": false,
    "message": "Product not found",
    "error_code": "RESOURCE_NOT_FOUND"
}
```

*Insufficient Stock (400):*
```json
{
    "success": false,
    "message": "Insufficient stock available",
    "error_code": "INSUFFICIENT_STOCK",
    "details": {
        "available_quantity": 5,
        "requested_quantity": 10
    }
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/cart.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -d '{
    "action": "add",
    "product_id": 1,
    "quantity": 2
  }'
```

---

## Update Cart Item

Updates the quantity of an existing cart item.

**Endpoint:** `POST /backend/api/cart.php`

**Authentication:** Required

**Request Body:**
```json
{
    "action": "update",
    "product_id": 1,
    "quantity": 3
}
```

**Parameters:**
- `action` (string, required): Must be "update"
- `product_id` (integer, required): ID of the product to update
- `quantity` (integer, required): New quantity (minimum: 1)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Cart item updated successfully",
    "data": {
        "cart_item": {
            "product_id": 1,
            "product_name": "Premium Cold-Pressed Mustard Oil",
            "price": 450.00,
            "quantity": 3,
            "total_amount": 1350.00
        },
        "cart_summary": {
            "total_items": 3,
            "subtotal": 1350.00,
            "shipping": 0.00,
            "total": 1350.00
        }
    }
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/cart.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -d '{
    "action": "update",
    "product_id": 1,
    "quantity": 3
  }'
```

---

## Remove Item from Cart

Removes a product from the user's shopping cart.

**Endpoint:** `POST /backend/api/cart.php`

**Authentication:** Required

**Request Body:**
```json
{
    "action": "remove",
    "product_id": 1
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Item removed from cart successfully",
    "data": {
        "removed_product_id": 1,
        "cart_summary": {
            "total_items": 0,
            "subtotal": 0.00,
            "shipping": 0.00,
            "total": 0.00
        }
    }
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/cart.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -d '{
    "action": "remove",
    "product_id": 1
  }'
```

---

## Get Cart Contents

Retrieves all items in the user's shopping cart.

**Endpoint:** `GET /backend/api/cart.php?action=list`

**Authentication:** Required

**Success Response (200):**
```json
{
    "success": true,
    "message": "Cart contents retrieved successfully",
    "data": {
        "items": [
            {
                "product_id": 1,
                "product_name": "Premium Cold-Pressed Mustard Oil",
                "product_weight": "1L",
                "price": 450.00,
                "quantity": 2,
                "total_amount": 900.00,
                "image_url": "/uploads/products/mustard-oil-1l.jpg",
                "stock_available": 100
            }
        ],
        "summary": {
            "total_items": 2,
            "unique_products": 1,
            "subtotal": 900.00,
            "shipping_charge": 0.00,
            "tax_amount": 0.00,
            "total_amount": 900.00,
            "free_shipping_eligible": true
        }
    }
}
```

**Empty Cart Response (200):**
```json
{
    "success": true,
    "message": "Cart is empty",
    "data": {
        "items": [],
        "summary": {
            "total_items": 0,
            "unique_products": 0,
            "subtotal": 0.00,
            "shipping_charge": 0.00,
            "tax_amount": 0.00,
            "total_amount": 0.00
        }
    }
}
```

**cURL Example:**
```bash
curl -X GET "https://yourdomain.com/backend/api/cart.php?action=list" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

---

## Clear Cart

Removes all items from the user's shopping cart.

**Endpoint:** `POST /backend/api/cart.php`

**Authentication:** Required

**Request Body:**
```json
{
    "action": "clear"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Cart cleared successfully",
    "data": {
        "items_removed": 3,
        "cart_summary": {
            "total_items": 0,
            "subtotal": 0.00,
            "total": 0.00
        }
    }
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/cart.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -d '{"action": "clear"}'
```

---

# Orders API

Base endpoint: `/backend/api/orders.php`

## Create Order

Creates a new order from the user's cart contents.

**Endpoint:** `POST /backend/api/orders.php`

**Authentication:** Required

**Request Body:**
```json
{
    "action": "create",
    "shipping_address": "123 Main St, City, State, 123456",
    "payment_method": "cod",
    "notes": "Please deliver in the evening"
}
```

**Parameters:**
- `action` (string, required): Must be "create"
- `shipping_address` (string, required): Complete delivery address
- `payment_method` (string, required): Payment method ("cod" or "online")
- `notes` (string, optional): Special delivery instructions

**Success Response (201):**
```json
{
    "success": true,
    "message": "Order created successfully",
    "data": {
        "order": {
            "id": 12,
            "order_number": "KK2025073012",
            "status": "pending",
            "total_amount": 900.00,
            "shipping_amount": 0.00,
            "tax_amount": 0.00,
            "final_amount": 900.00,
            "payment_method": "cod",
            "shipping_address": "123 Main St, City, State, 123456",
            "notes": "Please deliver in the evening",
            "created_at": "2025-07-30T10:30:00Z",
            "estimated_delivery": "2025-08-02T00:00:00Z"
        },
        "items": [
            {
                "product_id": 1,
                "product_name": "Premium Cold-Pressed Mustard Oil",
                "quantity": 2,
                "price": 450.00,
                "total_amount": 900.00
            }
        ]
    }
}
```

**Error Responses:**

*Empty Cart (400):*
```json
{
    "success": false,
    "message": "Cannot create order with empty cart",
    "error_code": "INVALID_REQUEST"
}
```

*Insufficient Stock (400):*
```json
{
    "success": false,
    "message": "Some items are out of stock",
    "error_code": "INSUFFICIENT_STOCK",
    "details": {
        "out_of_stock_items": [
            {
                "product_id": 1,
                "requested": 5,
                "available": 2
            }
        ]
    }
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/orders.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -d '{
    "action": "create",
    "shipping_address": "123 Main St, City, State, 123456",
    "payment_method": "cod",
    "notes": "Please deliver in the evening"
  }'
```

---

## List User Orders

Retrieves all orders for the authenticated user.

**Endpoint:** `GET /backend/api/orders.php?action=list`

**Authentication:** Required

**Query Parameters:**
- `action` (string, required): Must be "list"
- `page` (integer, optional): Page number (default: 1)
- `limit` (integer, optional): Items per page (default: 10)
- `status` (string, optional): Filter by order status

**Success Response (200):**
```json
{
    "success": true,
    "message": "Orders retrieved successfully",
    "data": {
        "orders": [
            {
                "id": 12,
                "order_number": "KK2025073012",
                "status": "pending",
                "total_amount": 900.00,
                "final_amount": 900.00,
                "payment_method": "cod",
                "created_at": "2025-07-30T10:30:00Z",
                "item_count": 1,
                "estimated_delivery": "2025-08-02T00:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 1,
            "total_items": 1,
            "items_per_page": 10
        }
    }
}
```

**cURL Example:**
```bash
curl -X GET "https://yourdomain.com/backend/api/orders.php?action=list&page=1&status=pending" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

---

## Get Order Details

Retrieves detailed information about a specific order.

**Endpoint:** `GET /backend/api/orders.php?action=detail&id={order_id}`

**Authentication:** Required

**Path Parameters:**
- `id` (integer, required): Order ID

**Success Response (200):**
```json
{
    "success": true,
    "message": "Order details retrieved successfully",
    "data": {
        "order": {
            "id": 12,
            "order_number": "KK2025073012",
            "status": "confirmed",
            "total_amount": 900.00,
            "shipping_amount": 0.00,
            "tax_amount": 0.00,
            "final_amount": 900.00,
            "payment_method": "cod",
            "shipping_address": "123 Main St, City, State, 123456",
            "notes": "Please deliver in the evening",
            "created_at": "2025-07-30T10:30:00Z",
            "updated_at": "2025-07-30T11:00:00Z",
            "estimated_delivery": "2025-08-02T00:00:00Z"
        },
        "items": [
            {
                "id": 1,
                "product_id": 1,
                "product_name": "Premium Cold-Pressed Mustard Oil",
                "product_weight": "1L",
                "quantity": 2,
                "price": 450.00,
                "total_amount": 900.00
            }
        ],
        "status_history": [
            {
                "status": "pending",
                "timestamp": "2025-07-30T10:30:00Z",
                "notes": "Order placed"
            },
            {
                "status": "confirmed",
                "timestamp": "2025-07-30T11:00:00Z",
                "notes": "Order confirmed and being prepared"
            }
        ]
    }
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Order not found",
    "error_code": "RESOURCE_NOT_FOUND"
}
```

**cURL Example:**
```bash
curl -X GET "https://yourdomain.com/backend/api/orders.php?action=detail&id=12" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

---

## Cancel Order

Cancels an existing order if it's in a cancellable state.

**Endpoint:** `POST /backend/api/orders.php`

**Authentication:** Required

**Request Body:**
```json
{
    "action": "cancel",
    "order_id": 12,
    "reason": "Changed my mind"
}
```

**Parameters:**
- `action` (string, required): Must be "cancel"
- `order_id` (integer, required): ID of the order to cancel
- `reason` (string, optional): Reason for cancellation

**Success Response (200):**
```json
{
    "success": true,
    "message": "Order cancelled successfully",
    "data": {
        "order_id": 12,
        "order_number": "KK2025073012",
        "status": "cancelled",
        "cancelled_at": "2025-07-30T12:00:00Z",
        "refund_amount": 900.00,
        "refund_status": "pending"
    }
}
```

**Error Responses:**

*Order Cannot Be Cancelled (400):*
```json
{
    "success": false,
    "message": "Order cannot be cancelled at this stage",
    "error_code": "INVALID_REQUEST",
    "details": {
        "current_status": "shipped",
        "cancellable_statuses": ["pending", "confirmed"]
    }
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/orders.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -d '{
    "action": "cancel",
    "order_id": 12,
    "reason": "Changed my mind"
  }'
```

---

## Reorder

Creates a new order with the same items as a previous order.

**Endpoint:** `POST /backend/api/orders.php`

**Authentication:** Required

**Request Body:**
```json
{
    "action": "reorder",
    "order_id": 12,
    "shipping_address": "123 Main St, City, State, 123456",
    "payment_method": "cod"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Reorder created successfully",
    "data": {
        "new_order": {
            "id": 15,
            "order_number": "KK2025073015",
            "status": "pending",
            "final_amount": 900.00,
            "created_at": "2025-07-30T14:00:00Z"
        },
        "original_order_id": 12,
        "items_added_to_cart": 1
    }
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/orders.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -d '{
    "action": "reorder",
    "order_id": 12,
    "shipping_address": "123 Main St, City",
    "payment_method": "cod"
  }'
```

---

# Contact API

Base endpoint: `/backend/api/contact.php`

## Submit Contact Form

Submits a customer inquiry or contact form.

**Endpoint:** `POST /backend/api/contact.php`

**Authentication:** Not required

**Request Body:**
```json
{
    "action": "submit",
    "name": "John Doe",
    "email": "john@example.com",
    "mobile": "9876543210",
    "subject": "Product Inquiry",
    "message": "I would like to know about bulk orders and pricing."
}
```

**Parameters:**
- `action` (string, required): Must be "submit"
- `name` (string, required): Customer's full name (3-100 characters)
- `email` (string, required): Valid email address
- `mobile` (string, optional): Mobile number (10 digits)
- `subject` (string, required): Inquiry subject (5-200 characters)
- `message` (string, required): Detailed message (10-1000 characters)

**Success Response (201):**
```json
{
    "success": true,
    "message": "Contact form submitted successfully",
    "data": {
        "contact_id": 45,
        "reference_number": "REF2025073045",
        "submitted_at": "2025-07-30T10:30:00Z",
        "response_time": "We will respond within 24 hours"
    }
}
```

**Error Responses:**

*Validation Failed (422):*
```json
{
    "success": false,
    "message": "Validation failed",
    "error_code": "VALIDATION_FAILED",
    "details": {
        "email": "Please provide a valid email address",
        "message": "Message must be at least 10 characters long"
    }
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/contact.php" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "submit",
    "name": "John Doe",
    "email": "john@example.com",
    "mobile": "9876543210",
    "subject": "Product Inquiry",
    "message": "I would like to know about bulk orders and pricing."
  }'
```

---

## Newsletter Subscription

Subscribes an email address to the newsletter.

**Endpoint:** `POST /backend/api/contact.php`

**Authentication:** Not required

**Request Body:**
```json
{
    "action": "newsletter",
    "email": "john@example.com",
    "name": "John Doe"
}
```

**Parameters:**
- `action` (string, required): Must be "newsletter"
- `email` (string, required): Valid email address
- `name` (string, optional): Subscriber's name

**Success Response (201):**
```json
{
    "success": true,
    "message": "Newsletter subscription successful",
    "data": {
        "email": "john@example.com",
        "subscribed_at": "2025-07-30T10:30:00Z",
        "welcome_email_sent": true
    }
}
```

**Error Responses:**

*Already Subscribed (400):*
```json
{
    "success": false,
    "message": "Email address is already subscribed to newsletter",
    "error_code": "ALREADY_EXISTS"
}
```

**cURL Example:**
```bash
curl -X POST "https://yourdomain.com/backend/api/contact.php" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "newsletter",
    "email": "john@example.com",
    "name": "John Doe"
  }'
```

---

# Rate Limiting

All API endpoints are protected by rate limiting to prevent abuse:

- **General endpoints**: 100 requests per hour per IP
- **Authentication endpoints**: 10 requests per 15 minutes per IP
- **Search endpoints**: 50 requests per hour per IP

When rate limit is exceeded, the API returns:

```json
{
    "success": false,
    "message": "Rate limit exceeded. Please try again later.",
    "error_code": "RATE_LIMIT_EXCEEDED",
    "details": {
        "limit": 100,
        "window": 3600,
        "retry_after": 1800
    }
}
```

**Response Headers:**
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests in current window
- `X-RateLimit-Reset`: Time when rate limit resets (Unix timestamp)

---

# Testing and Examples

## Postman Collection

A complete Postman collection is available with pre-configured requests for all endpoints. Import the collection to get started quickly with API testing.

## JavaScript Examples

### Authentication Flow
```javascript
// Send OTP
const sendOTP = async (mobile) => {
    const response = await fetch('/backend/api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'send_otp',
            mobile: mobile
        })
    });
    return response.json();
};

// Verify OTP and get token
const verifyOTP = async (mobile, otp) => {
    const response = await fetch('/backend/api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'verify_otp',
            mobile: mobile,
            otp: otp
        })
    });
    const data = await response.json();
    if (data.success && !data.data.is_new_user) {
        localStorage.setItem('auth_token', data.data.token);
    }
    return data;
};
```

### Product Management
```javascript
// Get products with authentication
const getProducts = async (page = 1) => {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/backend/api/products.php?action=list&page=${page}`, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    return response.json();
};

// Add to cart
const addToCart = async (productId, quantity) => {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/backend/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: quantity
        })
    });
    return response.json();
};
```

---

This comprehensive API documentation provides complete details for integrating with the KishansKraft e-commerce platform. All endpoints are fully tested and production-ready.
