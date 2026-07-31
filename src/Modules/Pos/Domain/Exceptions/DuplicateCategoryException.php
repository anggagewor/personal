<?php

namespace Modules\Pos\Domain\Exceptions;

class DuplicateCategoryException extends \DomainException
{
    public static function create(string $name): self
    {
        return new self("Kategori dengan nama '{$name}' sudah ada.");
    }
}
