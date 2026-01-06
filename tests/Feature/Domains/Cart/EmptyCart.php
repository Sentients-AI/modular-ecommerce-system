<?php

declare(strict_types=1);

use App\Domain\Cart\Exceptions\EmptyCartException;
use App\Domain\Order\Actions\CheckoutAction;

it('checkout fails when cart is empty', function () {

    $this->expectException(EmptyCartException::class);

    $action = app(CheckoutAction::class);
    $action->execute($this->emptyCart());
});
