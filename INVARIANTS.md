# System Invariants

This document defines the critical invariants for the order-payment-refund lifecycle. Code must enforce these invariants, and tests must prove them.

---

## 1. What Must Never Happen

### Order Domain

| Invariant | Description | Guard Location |
|-----------|-------------|----------------|
| **INV-ORD-001** | An order must never be created from an empty cart | `CreateOrderFromCart`, `CheckoutAction` |
| **INV-ORD-002** | Order total must never be negative | Model validation |
| **INV-ORD-003** | Order status must never regress (e.g., Paid → Pending) | `OrderStatus::canTransitionTo()` |
| **INV-ORD-004** | A cancelled/refunded order must never transition to Shipped/Delivered | `OrderStateGuard` |
| **INV-ORD-005** | Order items must never reference non-existent products | Foreign key constraint |
| **INV-ORD-006** | Duplicate orders must never be created for the same idempotency key | `EnsureIdempotentAction` |

### Payment Domain

| Invariant | Description | Guard Location |
|-----------|-------------|----------------|
| **INV-PAY-001** | A payment intent must never be confirmed twice | `PaymentStatus::isTerminal()` check |
| **INV-PAY-002** | A payment must never exceed the order total | `CreatePaymentIntentAction` |
| **INV-PAY-003** | A payment intent must never exist without an associated order | Foreign key constraint |
| **INV-PAY-004** | The same idempotency key must never create different payment intents | Unique constraint + fingerprint validation |
| **INV-PAY-005** | A terminal payment state (Succeeded/Failed/Cancelled) must never change | `isTerminal()` guard |

### Refund Domain

| Invariant | Description | Guard Location |
|-----------|-------------|----------------|
| **INV-REF-001** | A refund must never be requested for an unpaid order | `RequestRefundAction`, `InitiateRefundAction` |
| **INV-REF-002** | Total refunded amount must never exceed order total | `Order::markPartiallyRefunded()` |
| **INV-REF-003** | A refund must never be processed without approval | `ProcessRefundAction` |
| **INV-REF-004** | A terminal refund (Succeeded/Failed/Rejected/Cancelled) must never be re-approved | `ApproveRefundAction` |
| **INV-REF-005** | A fully refunded order must never accept additional refunds | `Order::isRefundable()` |

### Inventory Domain

| Invariant | Description | Guard Location |
|-----------|-------------|----------------|
| **INV-INV-001** | Stock quantity_available must never go negative | `ReserveStock` with lock |
| **INV-INV-002** | Stock quantity_reserved must never exceed quantity_available | `Stock::isAvailable()` |
| **INV-INV-003** | Stock reservation must never succeed without sufficient available quantity | `InsufficientStockException` |
| **INV-INV-004** | Released stock must never exceed reserved quantity | `ReleaseStockAction` clamps to reserved |

---

## 2. What Happens on Failure

### Payment Failures

| Scenario | System Response | Recovery Path |
|----------|----------------|---------------|
| Gateway timeout during payment creation | Transaction rolled back, no payment intent persisted | Retry with same idempotency key |
| Gateway rejects payment confirmation | `PaymentIntent` marked as `Failed`, exception re-thrown | Create new payment intent |
| Webhook delivery fails | Stripe retries automatically | Webhook handler is idempotent |
| Database failure during payment confirmation | Transaction rolled back, payment state unchanged | Webhook will retry |

### Order Failures

| Scenario | System Response | Recovery Path |
|----------|----------------|---------------|
| Insufficient stock during checkout | `InsufficientStockException` thrown, order not created | User retries with reduced quantity |
| Payment not completed within timeout | Order remains `Pending` | Expire stale orders via scheduled job |
| Idempotency key reused with different payload | `ConflictHttpException` (409) thrown | Use new idempotency key |

### Refund Failures

| Scenario | System Response | Recovery Path |
|----------|----------------|---------------|
| Gateway rejects refund | `Refund` marked as `Failed`, `RefundFailed` event recorded | Admin reviews and retries or rejects |
| Partial refund exceeds remaining amount | Clamped to order total | None needed |
| Concurrent approval attempts | Only first succeeds due to state check | None needed |

---

## 3. What Can Be Retried Safely

### Safe to Retry (Idempotent Operations)

| Operation | Idempotency Mechanism | Notes |
|-----------|----------------------|-------|
| Create payment intent | `idempotency_key` unique constraint | Returns existing intent if key matches |
| Confirm payment (webhook) | `isTerminal()` check | No-op if already succeeded/failed |
| Order creation | `EnsureIdempotentAction` with fingerprint | Returns cached response |
| Stock reservation (within transaction) | Database transaction | Rolled back on failure |

### Not Safe to Retry Without Guard

| Operation | Risk | Required Guard |
|-----------|------|----------------|
| Process refund | Double refund | Must check `status === Approved` |
| Mark order paid | Double state change | Must check `status === Pending` |
| Release stock | Over-release | Clamp to `quantity_reserved` |

---

## 4. What Is Eventually Consistent

### Asynchronous State Updates

| Data | Update Trigger | Consistency Window | Source of Truth |
|------|---------------|-------------------|-----------------|
| `Order.status` (Pending → Paid) | `PaymentSucceeded` event via webhook | Seconds to minutes | `PaymentIntent.status` |
| `Order.status` (Paid → Refunded) | `RefundSucceeded` event | Seconds to minutes | `Refund.status` |
| `Order.refunded_amount_cents` | `RefundSucceeded` listener | Seconds to minutes | Sum of succeeded refunds |
| `Stock.quantity_available` (after refund) | `RefundSucceeded` + compensation policy | Seconds to minutes | Stock movements ledger |
| `OrderFinancialProjection` | Event listeners | Seconds to minutes | Source events |
| `RefundProjection` | Event listeners | Seconds to minutes | Source events |

### Projection Reconciliation

Projections (`OrderFinancialProjection`, `RefundProjection`) are eventually consistent read models. In case of drift:
- Re-project from `domain_events` table
- Compare against source-of-truth aggregates

---

## 5. State Machine Definitions

### Order Status Transitions

```
┌─────────┐   payment    ┌──────┐   ship    ┌─────────┐  deliver  ┌───────────┐
│ Pending │─────────────▶│ Paid │──────────▶│ Shipped │──────────▶│ Fulfilled │
└─────────┘              └──────┘           └─────────┘           └───────────┘
     │                      │                                           │
     │ cancel               │ cancel                                    │
     ▼                      ▼                                           │
┌───────────┐          ┌───────────┐                                    │
│ Cancelled │          │ Cancelled │                                    │
└───────────┘          └───────────┘                                    │
                            │                                           │
                            │ refund (full)                             │
                            ▼                                           │
                       ┌──────────┐                                     │
                       │ Refunded │◀────────────────────────────────────┘
                       └──────────┘         (can also be refunded)
                            ▲
                            │ refund (partial then full)
                       ┌────────────────────┐
                       │ PartiallyRefunded  │
                       └────────────────────┘
```

### Payment Status Transitions

```
┌──────────────────┐   create    ┌────────────┐   confirm   ┌───────────┐
│ RequiresPayment  │────────────▶│ Processing │────────────▶│ Succeeded │
└──────────────────┘             └────────────┘             └───────────┘
                                       │                          │
                                       │ fail                     │
                                       ▼                          │
                                 ┌──────────┐                     │
                                 │  Failed  │                     │
                                 └──────────┘                     │
                                                                  │
                                       │ cancel                   │
                                       ▼                          │
                                 ┌───────────┐                    │
                                 │ Cancelled │                    │
                                 └───────────┘                    │
```

### Refund Status Transitions

```
┌───────────┐                ┌─────────────────┐
│ Requested │                │ PendingApproval │
└───────────┘                └─────────────────┘
      │                              │
      │ approve                      │ approve
      ▼                              ▼
┌──────────┐   process   ┌────────────┐   gateway   ┌───────────┐
│ Approved │────────────▶│ Processing │────────────▶│ Succeeded │
└──────────┘             └────────────┘             └───────────┘
      │                        │
      │ reject                 │ fail
      ▼                        ▼
┌──────────┐             ┌──────────┐
│ Rejected │             │  Failed  │
└──────────┘             └──────────┘
```

---

## 6. Compensation / Saga Pattern

When a refund succeeds, the following compensating actions must occur:

1. **Update Order State** → `Order::markPartiallyRefunded()` or `markRefunded()`
2. **Release Stock** (if applicable) → Based on `RefundCompensationPolicy::shouldReleaseStock()`
3. **Update Projections** → `OrderFinancialProjection`, `RefundProjection`

Failure in any step must be logged and retried, but must NOT roll back the gateway refund (money already returned to customer).

---

## 7. Concurrency Controls

| Operation | Lock Type | Scope |
|-----------|----------|-------|
| Stock reservation | `lockForUpdate()` | Per-product row |
| Stock release | `lockForUpdate()` | Per-product row |
| Order creation (idempotent) | `lockForUpdate()` | Idempotency key row |
| Payment intent lookup | `lockForUpdate()` | (recommended for webhook handlers) |

---

## 8. Testing Requirements

Each invariant must have at least one test proving it holds:

- **INV-ORD-001**: `EmptyCartTest`
- **INV-ORD-003**: `OrderSpecificationsTest::OrderCanTransitionToStatus validates transitions`
- **INV-PAY-001**: Webhook idempotency test
- **INV-REF-001**: `RefundActionsTest::throws exception for unpaid order`
- **INV-REF-003**: `RefundSpecificationsTest::RefundCanBeProcessed is not satisfied for non-approved refunds`
- **INV-INV-001**: `CreateOrderConcurrencyTest`
- **INV-INV-003**: `InsufficientStock` test

---

## 9. Specification Pattern Guards

Business rules are now encapsulated in composable Specification classes that provide consistent validation.

### 9.1 Order Specifications

| Specification | Validates | Used By |
|--------------|-----------|---------|
| `OrderIsRefundable` | Order status allows refunds | `RequestRefundUseCase` |
| `OrderCanTransitionToStatus` | Valid state machine transition | `ProcessPaymentUseCase` |
| `OrderCanBeCancelled` | Order can be cancelled | `CancelOrder` action |

### 9.2 Refund Specifications

| Specification | Validates | Used By |
|--------------|-----------|---------|
| `RefundCanBeApproved` | Refund in approvable state | `ApproveRefundAction` |
| `RefundCanBeProcessed` | Refund has been approved | `ProcessRefundAction` |
| `RefundAmountIsValid` | Amount > 0 and ≤ remaining | `RequestRefundUseCase` |

### 9.3 Cart Specifications

| Specification | Validates | Used By |
|--------------|-----------|---------|
| `CartIsNotCompleted` | Cart not yet checked out | `CheckoutUseCase` |
| `CartHasItems` | Cart is not empty | `CheckoutUseCase` |

### 9.4 Payment Specifications

| Specification | Validates | Used By |
|--------------|-----------|---------|
| `PaymentCanBeConfirmed` | Payment in Processing state | `ProcessPaymentUseCase` |
| `OrderHasNoActivePaymentIntent` | No duplicate payment intents | `CreatePaymentIntentAction` |

### 9.5 Inventory Specifications

| Specification | Validates | Used By |
|--------------|-----------|---------|
| `HasSufficientStock` | Available quantity ≥ requested | `ReserveStockAction` |

### 9.6 Composition Examples

```php
// Validate refund request with composed specifications
$spec = (new OrderIsRefundable())
    ->and(new RefundAmountIsValid($amountCents));

$spec->assertSatisfiedBy($order); // Throws DomainException if not satisfied

// Validate checkout with composed specifications
$cartSpec = (new CartIsNotCompleted())->and(new CartHasItems());
$cartSpec->assertSatisfiedBy($cart);
```

---

## Audit Findings (Requiring Fixes)

### Missing Guards

1. **`OrderStatus::canTransitionTo()`** is incomplete - does not handle all states (Packed, Delivered, PartiallyRefunded, Refunded)
2. **`OrderStateGuard`** is incomplete - missing guards for:
   - Refund transitions
   - Ship/pack transitions
   - Delivered → Refunded
3. **`Refund` model** has no `canTransitionTo()` method for state machine enforcement
4. **`HandleStripeEventJob::handleFailure()`** calls `ConfirmPaymentIntentAction` which expects `Processing` state - this is a bug
5. **`Order::markPartiallyRefunded()`** has no guard against being called on non-paid orders
6. **`Order::markRefunded()`** has no guard against being called on non-paid orders
7. **Missing guard**: Refund amount validation against order total - currently only in `Order::markPartiallyRefunded()` (clamps), but should also validate in `RequestRefundAction` and `InitiateRefundAction`

### Missing Tests

1. Order backward transition rejection
2. Payment intent double-confirm protection
3. Refund amount exceeding order total
4. Concurrent refund approval
5. Stock release clamping behavior
