<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Domain\Cart\Exceptions\EmptyCartException;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Throwable;

final class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (EmptyCartException $e) {
            return response()->json([
                'error' => 'CART_EMPTY',
                'message' => $e->getMessage(),
            ], 422);
        });

        $this->renderable(function (InsufficientStockException $e) {
            return response()->json([
                'error' => 'INSUFFICIENT_STOCK',
                'product_id' => $e->productId,
                'requested' => $e->requested,
                'available' => $e->available,
            ], 409);
        });

        $this->renderable(function (ConflictHttpException $e) {
            return response()->json([
                'error' => 'IDEMPOTENCY_CONFLICT',
                'message' => $e->getMessage(),
            ], 409);
        });
    }
}
