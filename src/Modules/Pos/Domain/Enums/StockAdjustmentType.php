<?php

namespace Modules\Pos\Domain\Enums;

enum StockAdjustmentType: string
{
    case Restock = 'restock';
    case Correction = 'correction';
    case Sale = 'sale';
    case Void = 'void';
}
