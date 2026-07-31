<?php

namespace Modules\Pos\Domain\Enums;

enum DiscountType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
    case BuyXGetY = 'buy_x_get_y';
}
