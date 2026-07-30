<?php

namespace Modules\Accounting\Domain\Exceptions;

class MaxDepthExceededException extends \DomainException
{
    public static function create(int $maxDepth): self
    {
        return new self("Kedalaman akun tidak boleh melebihi {$maxDepth} level.");
    }
}
