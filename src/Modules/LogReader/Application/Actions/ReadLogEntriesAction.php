<?php

namespace Modules\LogReader\Application\Actions;

use Modules\LogReader\Domain\Contracts\LogReaderInterface;
use Modules\LogReader\Domain\Enums\LogLevel;

class ReadLogEntriesAction
{
    public function __construct(
        private LogReaderInterface $reader,
    ) {}

    public function execute(
        string $filename,
        int $perPage = 30,
        int $offset = 0,
        ?string $level = null,
        ?string $search = null,
    ): array {
        $logLevel = $level ? LogLevel::tryFrom($level) : null;

        return $this->reader->read(
            filename: $filename,
            perPage: $perPage,
            offset: $offset,
            level: $logLevel,
            search: $search,
        );
    }
}
