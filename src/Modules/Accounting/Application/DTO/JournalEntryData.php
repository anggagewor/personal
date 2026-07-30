<?php

namespace Modules\Accounting\Application\DTO;

readonly class JournalEntryData
{
    /**
     * @param JournalLineData[] $lines
     */
    public function __construct(
        public string $date,
        public string $description,
        public array $lines,
    ) {}

    public static function fromArray(array $data): self
    {
        $lines = array_map(
            fn (array $line) => JournalLineData::fromArray($line),
            $data['lines'] ?? [],
        );

        return new self(
            date: $data['date'],
            description: $data['description'],
            lines: $lines,
        );
    }
}
