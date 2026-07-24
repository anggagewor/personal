<?php

namespace Modules\Quote\Domain\Entities;

class Quote
{
    public function __construct(
        public ?int $id,
        public string $content,
        public ?string $author = null,
    ) {}
}
