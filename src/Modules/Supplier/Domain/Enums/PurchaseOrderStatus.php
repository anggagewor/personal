<?php

namespace Modules\Supplier\Domain\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Partial = 'partial';
    case Received = 'received';
    case Cancelled = 'cancelled';
}
