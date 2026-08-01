<?php

namespace Modules\Pos\Domain\Enums;

enum ShiftStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
}
