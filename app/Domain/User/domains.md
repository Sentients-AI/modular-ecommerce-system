# Proposed Domain Structure — E-Commerce System

```
app/
 ├─ Domains/
 │   ├─ Users/
 │   │   ├─ Models/
 │   │   │   └─ User.php
 │   │   ├─ Actions/
 │   │   │   ├─ RegisterUser.php
 │   │   │   ├─ AuthenticateUser.php
 │   │   │   └─ AssignRole.php
 │   │   ├─ DTOs/
 │   │   │   ├─ RegisterUserData.php
 │   │   │   └─ LoginData.php
 │   │   └─ Policies
 │   │       └─ UserPolicy.php
 │   │
 │   ├─ Roles/
 │   │   ├─ Models/
 │   │   │   └─ Role.php
 │   │   ├─ Actions/
 │   │   │   └─ AssignRoleToUser.php
 │   │   └─ DTOs/
 │   │       └─ AssignRoleData.php
 │   │
 │   ├─ Products/
 │   │   ├─ Models/
 │   │   │   └─ Product.php
 │   │   ├─ Actions/
 │   │   │   ├─ CreateProduct.php
 │   │   │   └─ UpdateProductStock.php
 │   │   └─ DTOs/
 │   │       ├─ ProductData.php
 │   │       └─ UpdateStockData.php
 │   │
 │   ├─ Categories/
 │   │   ├─ Models/
 │   │   │   └─ Category.php
 │   │   ├─ Actions/
 │   │   │   └─ AssignCategoryToProduct.php
 │   │   └─ DTOs/
 │   │       └─ CategoryAssignmentData.php
 │   │
 │   ├─ Inventory/
 │   │   ├─ Models/
 │   │   │   ├─ Stock.php
 │   │   │   └─ StockMovement.php
 │   │   ├─ Actions/
 │   │   │   ├─ ReserveStock.php
 │   │   │   ├─ ReleaseStock.php
 │   │   │   └─ AdjustStock.php
 │   │   └─ DTOs/
 │   │       ├─ ReserveStockData.php
 │   │       └─ AdjustStockData.php
 │   │
 │   ├─ Carts/
 │   │   ├─ Models/
 │   │   │   ├─ Cart.php
 │   │   │   └─ CartItem.php
 │   │   ├─ Actions/
 │   │   │   ├─ AddItemToCart.php
 │   │   │   ├─ RemoveItemFromCart.php
 │   │   │   └─ GetCart.php
 │   │   └─ DTOs/
 │   │       ├─ CartItemData.php
 │   │       └─ CartData.php
 │   │
 │   ├─ Orders/
 │   │   ├─ Models/
 │   │   │   ├─ Order.php
 │   │   │   └─ OrderItem.php
 │   │   ├─ Actions/
 │   │   │   ├─ CreateOrderFromCart.php
 │   │   │   ├─ CancelOrder.php
 │   │   │   └─ ListUserOrders.php
 │   │   └─ DTOs/
 │   │       └─ CreateOrderData.php
 │   │
 │   ├─ Payments/
 │   │   ├─ Models/
 │   │   │   └─ Payment.php
 │   │   ├─ Actions/
 │   │   │   ├─ InitiatePayment.php
 │   │   │   └─ GetPaymentStatus.php
 │   │   └─ DTOs/
 │   │       └─ PaymentData.php
 │   │
 │   └─ Idempotency/
 │       ├─ Models/
 │       │   └─ IdempotencyKey.php
 │       └─ Actions/
 │           └─ RegisterKey.php
 │
 └─ Shared/
     ├─ Models/
     │   └─ BaseModel.php
     ├─ Services/
     │   └─ JsonResponseService.php
     └─ DTOs/
         └─ BaseData.php
```

---

## ✅ Explanation of structure

1. **Models in Domains**

    * Each domain owns its models
    * Keeps `App\Models` clean
    * Models are **rich in domain rules**, but do not contain controllers or presentation logic

2. **DTOs in Domains**

    * DTOs live next to the domain they describe
    * They carry **validated, typed input/output** between controllers and actions
    * Example: `CreateOrderData` carries user ID and cart snapshot for `CreateOrderFromCart` action

3. **Actions / Services**

    * Each **action encapsulates one business operation**
    * Contains transactions, domain logic, validation rules
    * Keeps controllers **thin and declarative**

4. **Shared**

    * Base model / DTOs for common traits (timestamps, UUIDs, JSON helpers)
    * JSON response service ensures **consistent API responses**

---

## Example: flow for “Create Order”

```
Controller
 └─> DTO: CreateOrderData
      └─> Action: CreateOrderFromCart
           ├─ Validate cart
           ├─ Reserve stock (Inventory/Actions/ReserveStock)
           ├─ Persist order & items
           └─ Dispatch payment job
```

No controller ever:

* Queries DB directly
* Calculates totals
* Handles concurrency

Everything lives **in actions + DTOs + models**.
