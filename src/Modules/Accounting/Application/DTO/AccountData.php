<?php

namespace Modules\Accounting\Application\DTO;

readonly class AccountData
{
    public function __construct(
        public string $code,
        public string $name,
        public string $type,
        public ?int $parentId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            name: $data['name'],
            type: $data['type'],
            parentId: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
        );
    }
}
