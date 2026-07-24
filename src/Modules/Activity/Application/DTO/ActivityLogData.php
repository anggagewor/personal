<?php

namespace Modules\Activity\Application\DTO;

readonly class ActivityLogData
{
    public function __construct(
        public string $type,
        public string $description,
        public array $properties = [],
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
