<?php

namespace Modules\Pos\Domain\Enums;

enum TransactionStatus: string
{
    case Completed = 'completed';
    case Pending = 'pending';
    case Voided = 'voided';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';
}
