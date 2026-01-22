# Modular E-Commerce System

A Domain-Driven Design (DDD) e-commerce system built with Laravel, featuring strict invariant enforcement, event-driven architecture, and comprehensive business rule validation.

## Architecture Overview

```
app/
├── Application/              # Use Cases & Application DTOs
│   ├── UseCases/            # Orchestration layer
│   │   ├── Order/           # CheckoutUseCase
│   │   ├── Payment/         # ProcessPaymentUseCase
│   │   └── Refund/          # RequestRefundUseCase
│   └── DTOs/                # Request/Response DTOs with Value Objects
│
├── Domain/                   # Core business logic
│   ├── Cart/                # Shopping cart bounded context
│   ├── Inventory/           # Stock management
│   ├── Order/               # Order processing
│   ├── Payment/             # Payment handling
│   ├── Product/             # Product catalog
│   ├── Refund/              # Refund management
│   └── User/                # User management
│   └── {Domain}/
│       ├── Actions/         # Domain services/commands
│       ├── DTOs/            # Domain data transfer objects
│       ├── Enums/           # State machines
│       ├── Events/          # Domain events
│       ├── Models/          # Eloquent aggregates
│       ├── Specifications/  # Business rule validators
│       └── ValueObjects/    # Identity & value types
│
├── Infrastructure/           # External services
│   └── Payment/Stripe/      # Payment gateway implementation
│
└── Shared/                   # Cross-cutting concerns
    ├── Specifications/      # Specification pattern base
    ├── ValueObjects/        # AbstractId base class
    └── Domain/              # Domain event infrastructure
```

## Key DDD Patterns

### Specification Pattern
Business rules are encapsulated in composable Specification classes:

```php
use App\Domain\Order\Specifications\OrderIsRefundable;
use App\Domain\Refund\Specifications\RefundAmountIsValid;

$spec = (new OrderIsRefundable())
    ->and(new RefundAmountIsValid($amountCents));

$spec->assertSatisfiedBy($order); // Throws DomainException if invalid
```

### Identity Value Objects
Type-safe IDs prevent accidental ID confusion:

```php
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\User\ValueObjects\UserId;

$orderId = OrderId::fromInt(123);
$userId = UserId::fromInt(456);

// Type system prevents: $orderId->equals($userId) // Different types!
```

### Use Cases (Application Layer)
Complex operations are orchestrated by Use Cases:

```php
use App\Application\UseCases\Order\CheckoutUseCase;
use App\Application\DTOs\Request\CheckoutRequest;

$response = $useCase->execute(new CheckoutRequest(
    userId: UserId::fromInt($userId),
    cartId: CartId::fromInt($cartId),
));
```

## Documentation

- [DECISIONS.md](DECISIONS.md) - Architectural decisions and rationale
- [INVARIANTS.md](INVARIANTS.md) - System invariants and guards
- [PRODUCTION_READINESS_REVIEW.md](PRODUCTION_READINESS_REVIEW.md) - Production checklist

## Testing

```bash
# Run all tests
php artisan test

# Run specification tests
php artisan test --filter=Specification

# Run with coverage
php artisan test --coverage
```

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
