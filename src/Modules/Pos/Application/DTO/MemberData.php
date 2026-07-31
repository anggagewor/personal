<?php

namespace Modules\Pos\Application\DTO;

readonly class MemberData
{
    public function __construct(
        public string $name,
        public string $phone,
        public ?string $email = null,
    ) {}
}
