<?php

namespace Modules\Supplier\Application\DTO;

readonly class SupplierData
{
    public function __construct(
        public string $name,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $bankName = null,
        public ?string $bankAccountNumber = null,
        public ?string $bankAccountHolder = null,
        public ?string $notes = null,
    ) {}
}
