<?php

namespace Modules\Pos\Application\DTO;

readonly class CategoryData
{
    public function __construct(
        public string $name,
        public ?string $icon = null,
        public ?int $sortOrder = null,
        public ?int $parentId = null,
    ) {}
}
