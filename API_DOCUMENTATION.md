# Creakers Backend — API Documentation

## Base URL

```
http://localhost/creakers-backend/public/api
```

> Every endpoint is prefixed with a **site slug**.  
> Replace `{site}` with your site's slug (e.g. `creakers`).

---

## General

- All responses are **JSON**
- Successful responses include `"success": true`
- Failed responses return appropriate HTTP status codes with an `"message"` field
- Dates are in `"dd MMM YYYY, hh:mm AM/PM"` format (IST)
- Prices are in **Indian Rupees (₹)**

---

## 1. Site Info

### Get Site Details

```
GET /api/{site}
```

Use this on app load to fetch the site name and logo.

**Example**
```
GET /api/creakers
```

**Response `200`**
```json
{
  "success": true,
  "data": {
    "name": "Creakers",
    "slug": "creakers",
    "logo": "http://localhost/creakers-backend/public/storage/sites/logo.png"
  }
}
```

> `logo` is `null` if no logo has been uploaded.

**Response `404`** — site not found or inactive

---

## 3. Categories

### List All Categories

```
GET /api/{site}/categories
```

**Example**
```
GET /api/creakers/categories
```

**Response `200`**
```json
{
  "success": true,
  "site": {
    "name": "Creakers",
    "slug": "creakers"
  },
  "data": [
    {
      "id": 1,
      "name": "Ground Chakkar",
      "slug": "ground-chakkar",
      "image": "http://localhost/creakers-backend/public/storage/categories/abc.jpg"
    },
    {
      "id": 2,
      "name": "Rockets",
      "slug": "rockets",
      "image": null
    }
  ]
}
```

---

## 4. Products

### List All Products

```
GET /api/{site}/products
```

**Query Parameters**

| Parameter  | Type    | Required | Description                              |
|------------|---------|----------|------------------------------------------|
| `page`     | integer | No       | Page number (default: 1)                 |
| `per_page` | integer | No       | Items per page (default: 20, max: 100)   |
| `category` | string  | No       | Filter by category slug                  |
| `search`   | string  | No       | Search by product name                   |

**Example**
```
GET /api/creakers/products?page=1&per_page=20&category=rockets&search=flower
```

**Response `200`**
```json
{
  "success": true,
  "site": {
    "name": "Creakers",
    "slug": "creakers"
  },
  "data": [
    {
      "id": 1,
      "name": "Flower Pot",
      "slug": "flower-pot",
      "description": "Beautiful flower pot cracker",
      "image": "http://localhost/creakers-backend/public/storage/products/flower-pot.jpg",
      "gallery": [],
      "category": {
        "id": 2,
        "name": "Rockets",
        "slug": "rockets"
      },
      "pricing": {
        "mrp": 140.00,
        "discount_type": "flat",
        "discount_value": 112.00,
        "our_price": 28.00,
        "savings": 112.00
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 98
  }
}
```

> **`pricing.discount_type`** — `"percentage"` or `"flat"`  
> **`pricing.discount_value`** — the discount amount (% or ₹ depending on type)  
> **`pricing.savings`** — always `mrp - our_price` regardless of discount type  
> **`pricing`** — will be `null` if the product has no price configured for this site

---

### Get Single Product

```
GET /api/{site}/products/{slug}
```

**Example**
```
GET /api/creakers/products/flower-pot
```

**Response `200`**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Flower Pot",
    "slug": "flower-pot",
    "description": "Beautiful flower pot cracker",
    "image": "http://localhost/creakers-backend/public/storage/products/flower-pot.jpg",
    "gallery": [],
    "category": {
      "id": 2,
      "name": "Rockets",
      "slug": "rockets"
    },
    "pricing": {
      "mrp": 140.00,
      "discount_type": "percentage",
      "discount_value": 20.00,
      "our_price": 112.00,
      "savings": 28.00
    }
  }
}
```

**Response `404`** — product not found or not available on this site

---

### Products by Category

```
GET /api/{site}/categories/{categorySlug}/products
```

**Query Parameters**

| Parameter  | Type    | Required | Description                              |
|------------|---------|----------|------------------------------------------|
| `page`     | integer | No       | Page number (default: 1)                 |
| `per_page` | integer | No       | Items per page (default: 20, max: 100)   |

**Example**
```
GET /api/creakers/categories/rockets/products?page=1
```

**Response `200`**
```json
{
  "success": true,
  "category": {
    "name": "Rockets",
    "slug": "rockets"
  },
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 20,
    "total": 35
  }
}
```

---

## 5. Orders

### Place an Order

```
POST /api/{site}/orders
Content-Type: application/json
```

**Request Body**

```json
{
  "customer_name": "Ravi Kumar",
  "customer_phone": "9876543210",
  "customer_email": "ravi@example.com",
  "customer_address": "12, MG Road",
  "customer_city": "Chennai",
  "customer_district": "Chennai",
  "customer_state": "Tamil Nadu",
  "customer_pincode": "600001",
  "notes": "Please pack carefully",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 5,
      "quantity": 1
    }
  ]
}
```

**Field Reference**

| Field               | Type    | Required | Notes                          |
|---------------------|---------|----------|--------------------------------|
| `customer_name`     | string  | Yes      | Max 255 chars                  |
| `customer_phone`    | string  | Yes      | Max 20 chars                   |
| `customer_email`    | string  | No       | Valid email — gets confirmation mail |
| `customer_address`  | string  | Yes      | Full street address            |
| `customer_city`     | string  | No       |                                |
| `customer_district` | string  | No       |                                |
| `customer_state`    | string  | No       |                                |
| `customer_pincode`  | string  | Yes      | Max 10 chars                   |
| `notes`             | string  | No       | Delivery instructions etc.     |
| `items`             | array   | Yes      | Min 1 item                     |
| `items[].product_id`| integer | Yes      | Must exist in products table   |
| `items[].quantity`  | integer | Yes      | Min 1                          |

**Response `201`**
```json
{
  "success": true,
  "message": "Order placed successfully.",
  "data": {
    "order_number": "ORD-CREAKERS-A1B2C3D4",
    "total_amount": 168.00,
    "status": "pending",
    "items_count": 2
  }
}
```

**Response `422`** — validation error
```json
{
  "message": "The customer name field is required.",
  "errors": {
    "customer_name": ["The customer name field is required."]
  }
}
```

**Response `500`** — if a product is not available for the site
```json
{
  "message": "Product 'Flower Pot' is not available for this site."
}
```

> After a successful order:
> - Admin receives an email notification at the site's configured admin email
> - Customer receives a confirmation email (only if `customer_email` is provided)

---

### Get Order Details

```
GET /api/{site}/orders/{orderNumber}
```

**Example**
```
GET /api/creakers/orders/ORD-CREAKERS-A1B2C3D4
```

**Response `200`**
```json
{
  "success": true,
  "data": {
    "order_number": "ORD-CREAKERS-A1B2C3D4",
    "status": "pending",
    "customer_name": "Ravi Kumar",
    "customer_phone": "9876543210",
    "customer_email": "ravi@example.com",
    "customer_address": "12, MG Road",
    "customer_district": "Chennai",
    "customer_pincode": "600001",
    "total_amount": 168.00,
    "notes": "Please pack carefully",
    "created_at": "02 Jun 2026, 11:30 AM",
    "items": [
      {
        "product_name": "Flower Pot",
        "category_name": "Rockets",
        "mrp": 140.00,
        "our_price": 28.00,
        "quantity": 2,
        "subtotal": 56.00
      }
    ]
  }
}
```

**Order Status Values**

| Status       | Meaning                        |
|--------------|--------------------------------|
| `pending`    | Order received, not confirmed  |
| `confirmed`  | Admin confirmed the order      |
| `processing` | Being packed / prepared        |
| `dispatched` | Shipped / out for delivery     |
| `delivered`  | Delivered to customer          |
| `cancelled`  | Order cancelled                |

---

## 6. Error Responses

| HTTP Code | Meaning                                          |
|-----------|--------------------------------------------------|
| `404`     | Site, product, category, or order not found      |
| `422`     | Validation failed — check `errors` object        |
| `500`     | Server error — check `message` for details       |

---

## 7. Pagination

All list endpoints that support pagination return a `meta` object:

```json
"meta": {
  "current_page": 1,
  "last_page": 5,
  "per_page": 20,
  "total": 98
}
```

To navigate pages use `?page=2`, `?page=3`, etc.

---

## 8. Quick Reference

| Method | Endpoint                                        | Description               |
|--------|-------------------------------------------------|---------------------------|
| GET    | `/api/{site}`                                   | Site info (name, logo)    |
| GET    | `/api/{site}/categories`                        | List all categories       |
| GET    | `/api/{site}/categories/{slug}/products`        | Products by category      |
| GET    | `/api/{site}/products`                          | List products (+ filters) |
| GET    | `/api/{site}/products/{slug}`                   | Single product detail     |
| POST   | `/api/{site}/orders`                            | Place an order            |
| GET    | `/api/{site}/orders/{orderNumber}`              | Get order details         |
