<?php

namespace App\Domain\Payment\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Authorized = 'authorized';
    case Capture = 'capture';
    case Failed  = 'failed';
    case Refunded  = 'refunded';


}
