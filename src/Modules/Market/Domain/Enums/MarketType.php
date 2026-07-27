<?php

namespace Modules\Market\Domain\Enums;

enum MarketType: string
{
    case Forex = 'forex';
    case Crypto = 'crypto';
    case Stock = 'stock';
    case Commodity = 'commodity';
}
