<?php

namespace Modules\Pos\Domain\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
