<?php

namespace Modules\Pos\Domain\Enums;

enum BusinessType: string
{
    case Retail = 'retail';
    case Warung = 'warung';
    case Kafe = 'kafe';
    case Warkop = 'warkop';
}
