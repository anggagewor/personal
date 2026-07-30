<?php

namespace Modules\Accounting\Domain\Enums;

enum NormalBalance: string
{
    case Debit = 'debit';
    case Credit = 'credit';
}
