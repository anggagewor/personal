<?php

namespace Modules\Tag\Domain\Entities;

class Tag
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $name,
        public ?string $color = null,
    ) {}
}
