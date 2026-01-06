# 1️⃣ API.md — E-Commerce System

````markdown
# API Documentation — Supermarket / E-Commerce System

All endpoints return JSON.  
Errors follow this schema:

```json
{
  "error": {
    "code": "string",
    "message": "string"
  }
}
````

---

## Authentication

### POST /api/login

* Request:

```json
{
  "email": "user@example.com",
  "password": "string"
}
```

* Response 200:

```json
{
  "token": "jwt_token_here",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "roles": ["customer"]
  }
}
```

---

### POST /api/logout

* Headers: `Authorization: Bearer {token}`
* Response: 204 No Content

---

## Products & Categories

### GET /api/products

* Query parameters:

    * `category_id` (optional)
    * `search` (optional)
    * `page` (optional)
* Response 200:

```json
{
  "data": [
    {
      "id": 1,
      "sku": "SKU123",
      "name": "Product 1",
      "price_cents": 1000,
      "currency": "USD",
      "is_active": true
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

### GET /api/products/{id}

* Response 200: product object

---

### GET /api/categories

* Response 200: array of categories

```json
[
  {
    "id": 1,
    "name": "Beverages",
    "slug": "beverages"
  }
]
```

---

## Cart

### GET /api/cart

* Response 200:

```json
{
  "id": 1,
  "status": "active",
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "price_cents_snapshot": 1000,
      "tax_cents_snapshot": 100
    }
  ]
}
```

### POST /api/cart/items

* Request:

```json
{
  "product_id": 1,
  "quantity": 2
}
```

* Response 201: cart item object
* Error 400: out of stock

### DELETE /api/cart/items/{id}

* Response 204

---

## Orders

### POST /api/orders

* Request: none (cart is inferred from authenticated user)
* Response 201:

```json
{
  "id": 1,
  "status": "pending",
  "subtotal_cents": 2000,
  "tax_cents": 200,
  "total_cents": 2200,
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "price_cents_snapshot": 1000,
      "tax_cents_snapshot": 100
    }
  ]
}
```

* Error 400: cart empty, stock unavailable

### GET /api/orders

* Response 200: list of user's orders

### GET /api/orders/{id}

* Response 200: order detail
* Error 403: order does not belong to user

---

## Payments

### POST /api/orders/{id}/pay

* Request:

```json
{
  "provider": "stripe",
  "payment_method_id": "pm_123"
}
```

* Response 202: payment pending
* Error 400: order already paid

### GET /api/orders/{id}/payment

* Response 200: payment status

````

✅ This file defines **all core endpoints**, their inputs, outputs, and error contracts.  
It’s now your **API contract** — frontend can develop safely without guessing.

---

# 2️⃣ Backend endpoints — thin, high-signal controllers

### Principles

- Controllers are **thin** (5–7 lines max)  
- All logic lives in **Use Cases / Services / Domain Actions**  
- Transactions handled at **service layer**  
- JSON responses always consistent  

---

### Example — CartController

```php
class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = CartService::getActiveCart($request->user());
        return response()->json($cart);
    }

    public function store(Request $request)
    {
        $item = CartService::addItem(
            $request->user(),
            $request->input('product_id'),
            $request->input('quantity')
        );

        return response()->json($item, 201);
    }

    public function destroy(Request $request, int $itemId)
    {
        CartService::removeItem($request->user(), $itemId);
        return response()->noContent();
    }
}
````

---

### Example — OrderController

```php
class OrderController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(OrderService::listUserOrders($request->user()));
    }

    public function store(Request $request)
    {
        $order = OrderService::createFromCart($request->user());
        return response()->json($order, 201);
    }

    public function show(Request $request, int $orderId)
    {
        $order = OrderService::getUserOrder($request->user(), $orderId);
        return response()->json($order);
    }
}
```

---

### Example — PaymentController

```php
class PaymentController extends Controller
{
    public function pay(Request $request, int $orderId)
    {
        $payment = PaymentService::initiatePayment(
            $request->user(),
            $orderId,
            $request->input('provider'),
            $request->input('payment_method_id')
        );

        return response()->json($payment, 202);
    }

    public function status(Request $request, int $orderId)
    {
        return response()->json(PaymentService::getStatus($request->user(), $orderId));
    }
}
```
