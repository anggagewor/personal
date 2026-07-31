<?php

namespace Modules\Pos\Domain\Enums;

enum PaymentFlowMode: string
{
    case PayFirst = 'pay_first';
    case PayLater = 'pay_later';
    case Both = 'both';
}
