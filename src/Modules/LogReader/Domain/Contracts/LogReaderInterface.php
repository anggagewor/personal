<?php

namespace Modules\LogReader\Domain\Contracts;

use Modules\LogReader\Domain\Enums\LogLevel;

interface LogReaderInterface
{
    /**
     * Read log entries from end of file (newest first), memory-efficient.
     *
     * @param string $filename Log filename (relative to storage/logs)
     * @param int $perPage Entries per page
     * @param int $offset Byte offset to start reading backwards from (0 = end of file)
     * @param LogLevel|null $level Filter by log level
     * @param string|null $search Search in message
     * @return array{data: array, meta: array}
     */
    public function read(
        string $filename,
        int $perPage = 30,
        int $offset = 0,
        ?LogLevel $level = null,
        ?string $search = null,
    ): array;

    /**
     * List available log files with size info.
     *
     * @return array<array{name: string, size: int, modified_at: string}>
     */
    public function listFiles(): array;
}
