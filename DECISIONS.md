2026-01-03 — Chose domain-driven folder boundaries

Decision
Organised the codebase around domain boundaries (Product, Inventory, Cart, Order, Payment) rather than technical layers alone.

Reason
Business rules evolve faster than frameworks. Domain boundaries reduce coupling and make reasoning about invariants easier.

Trade-offs

Slightly more upfront structure

Higher cognitive load for beginners

Consequences

Easier refactoring

Clear ownership of business rules

Scales better as complexity grows

2026-01-03 — Monetary values stored as integers

Decision
All monetary values are stored as integers representing the smallest currency unit (e.g. cents).

Reason
Avoids floating-point precision errors in calculations and comparisons.

Trade-offs

Requires formatting at the presentation layer

Consequences

Financial correctness

Safer calculations in transactions

2026-01-03 — Inventory managed via stock movements

Decision
Inventory is tracked using a stock_movements table instead of directly mutating stock counts.

Reason
Allows auditing, debugging, and reconciliation of stock changes over time.

Trade-offs

Slightly more complex queries

Consequences

Full traceability

Easier to debug discrepancies

2026-01-03 — Cart implemented as a price snapshot

Decision
Cart items store product price and tax at the time they are added.

Reason
Prices and taxes can change; orders must reflect what the customer saw.

Trade-offs

Data duplication

Consequences

Correct historical orders

Fewer edge-case disputes

2026-01-03 — Payment handled asynchronously

Decision
Payment confirmation relies on webhooks and background jobs rather than synchronous responses.

Reason
External systems are unreliable; async processing improves resilience.

Trade-offs

More complex flow

Consequences

Fault tolerance

Better scalability

2026-01-22 — Specification pattern for business rules

Decision
Business rule validation is encapsulated in Specification classes that can be composed using AND/OR/NOT logic.

Reason
Complex validation logic was scattered across Actions, Models, and Enums. Specifications make rules explicit, testable, and reusable.

Trade-offs

Additional abstraction layer

More files to maintain

Consequences

Clear separation of business rules from orchestration

Composable validation (e.g., `OrderIsRefundable AND RefundAmountIsValid`)

Consistent error messages via `getFailureReason()`

Easier to test individual rules in isolation

2026-01-22 — Identity Value Objects for type safety

Decision
Aggregate root IDs are wrapped in typed Value Objects (e.g., `OrderId`, `UserId`, `CartId`).

Reason
Scalar IDs (int) can be accidentally swapped between different entity types. Value Objects prevent passing an `order_id` where a `user_id` is expected.

Trade-offs

Verbose factory calls (`OrderId::fromInt($id)`)

Requires `->toInt()` for database queries

Consequences

Compile-time type safety

Self-documenting code

Prevents ID confusion bugs

2026-01-22 — Application layer Use Cases for orchestration

Decision
Complex multi-step operations are orchestrated by Use Case classes in the Application layer, separate from Domain Actions.

Reason
Domain Actions should focus on single responsibilities. Use Cases coordinate multiple Actions and apply Specifications before execution.

Trade-offs

Additional layer between HTTP and Domain

More indirection

Consequences

Clear entry points for complex operations

Specifications applied at orchestration level

Request/Response DTOs with Value Object IDs

Easier to test orchestration logic
