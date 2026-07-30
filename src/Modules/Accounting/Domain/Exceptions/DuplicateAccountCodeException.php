<?php

namespace Modules\Accounting\Domain\Exceptions;

class DuplicateAccountCodeException extends \DomainException
{
    public static function create(string $code): self
    {
        return new self("Akun dengan kode '{$code}' sudah ada.");
    }
}
