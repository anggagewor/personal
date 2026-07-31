<?php

namespace Modules\Pos\Domain\Enums;

enum TableSessionStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
}
