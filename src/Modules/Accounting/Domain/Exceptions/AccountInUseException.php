<?php

namespace Modules\Accounting\Domain\Exceptions;

class AccountInUseException extends \DomainException
{
    public static function create(string $code): self
    {
        return new self("Akun dengan kode '{$code}' tidak dapat dihapus karena masih digunakan dalam jurnal.");
    }
}
