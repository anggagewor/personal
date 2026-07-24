<?php

namespace Modules\Activity\Application\DTO;

class ActivityLogData
{
    public function __construct(
        public readonly string $type,
        public readonly string $description,
        public readonly array $properties = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            description: $data['description'],
            properties: $data['properties'] ?? [],
        );
    }
}
