<?php

namespace Modules\LogReader\Infrastructure\Services;

use DateTimeImmutable;
use Modules\LogReader\Domain\Contracts\LogReaderInterface;
use Modules\LogReader\Domain\Entities\LogEntry;
use Modules\LogReader\Domain\Enums\LogLevel;

/**
 * Memory-efficient log reader using reverse file reading (fseek from end).
 *
 * Strategy:
 * - Read file from the end in configurable chunk sizes (default 8KB).
 * - Parse Laravel log entries on-the-fly using regex.
 * - Never load full file into memory — even 5GB files use ~16KB of buffer.
 * - Use byte offset for cursor-based pagination (no page numbers needed).
 */
class ReverseFileLogReader implements LogReaderInterface
{
    private const CHUNK_SIZE = 8192; // 8KB chunks

    // Laravel log pattern: [YYYY-MM-DD HH:MM:SS] environment.LEVEL: message
    private const LOG_PATTERN = '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}\.?\d*[+-]?\d*)\]\s+(\w+)\.(\w+):\s+(.*)$/s';

    public function read(
        string $filename,
        int $perPage = 30,
        int $offset = 0,
        ?LogLevel $level = null,
        ?string $search = null,
    ): array {
        $path = $this->resolveLogPath($filename);

        if (!file_exists($path)) {
            return ['data' => [], 'meta' => $this->buildMeta(0, 0, 0)];
        }

        $fileSize = filesize($path);

        if ($fileSize === 0) {
            return ['data' => [], 'meta' => $this->buildMeta(0, 0, 0)];
        }

        $handle = fopen($path, 'rb');

        if (!$handle) {
            return ['data' => [], 'meta' => $this->buildMeta($fileSize, 0, 0)];
        }

        try {
            // Start position: offset=0 means start from end
            $position = $offset === 0 ? $fileSize : $offset;
            $entries = [];
            $buffer = '';
            $scanned = 0;

            while ($position > 0 && count($entries) < $perPage) {
                // Calculate chunk to read
                $chunkSize = min(self::CHUNK_SIZE, $position);
                $position -= $chunkSize;

                fseek($handle, $position);
                $chunk = fread($handle, $chunkSize);
                $scanned += $chunkSize;

                // Prepend chunk to buffer
                $buffer = $chunk . $buffer;

                // Extract complete log entries from buffer
                $this->extractEntries($buffer, $entries, $perPage, $level, $search);

                // Safety: don't scan more than 2MB for a single page request
                if ($scanned > 2 * 1024 * 1024 && count($entries) < $perPage) {
                    break;
                }
            }

            // Handle remaining buffer (first entry in file)
            if ($position === 0 && $buffer !== '' && count($entries) < $perPage) {
                $this->extractEntries($buffer, $entries, $perPage, $level, $search, flush: true);
            }

            $nextOffset = $position > 0 ? $position : null;

            return [
                'data' => $entries,
                'meta' => $this->buildMeta($fileSize, $nextOffset, count($entries)),
            ];
        } finally {
            fclose($handle);
        }
    }

    public function listFiles(): array
    {
        $logPath = storage_path('logs');
        $files = [];

        if (!is_dir($logPath)) {
            return [];
        }

        $items = scandir($logPath);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $logPath . '/' . $item;

            if (!is_file($fullPath) || !str_ends_with($item, '.log')) {
                continue;
            }

            $files[] = [
                'name' => $item,
                'size' => filesize($fullPath),
                'modified_at' => date('Y-m-d\TH:i:s\Z', filemtime($fullPath)),
            ];
        }

        // Sort by modified date, newest first
        usort($files, fn ($a, $b) => $b['modified_at'] <=> $a['modified_at']);

        return $files;
    }

    /**
     * Extract complete log entries from buffer, leaving incomplete entry at start.
     */
    private function extractEntries(
        string &$buffer,
        array &$entries,
        int $limit,
        ?LogLevel $level,
        ?string $search,
        bool $flush = false,
    ): void {
        // Split buffer by log entry boundaries (lines starting with [YYYY-)
        $parts = preg_split('/(?=^\[\d{4}-)/m', $buffer, -1, PREG_SPLIT_NO_EMPTY);

        if (!$parts) {
            return;
        }

        // Keep the first part in buffer (might be incomplete) unless flushing
        if (!$flush) {
            $buffer = array_shift($parts);
        } else {
            $buffer = '';
        }

        // Process parts from end (newest first)
        $parts = array_reverse($parts);

        foreach ($parts as $raw) {
            if (count($entries) >= $limit) {
                break;
            }

            $entry = $this->parseEntry(trim($raw));

            if (!$entry) {
                continue;
            }

            // Apply filters
            if ($level !== null && $entry->level !== $level) {
                continue;
            }

            if ($search !== null && stripos($entry->message . $entry->stackTrace, $search) === false) {
                continue;
            }

            $entries[] = $entry;
        }
    }

    private function parseEntry(string $raw): ?LogEntry
    {
        // Separate first line from stack trace
        $lines = explode("\n", $raw, 2);
        $firstLine = $lines[0];
        $stackTrace = isset($lines[1]) ? trim($lines[1]) : '';

        if (!preg_match(self::LOG_PATTERN, $firstLine, $matches)) {
            return null;
        }

        $datetime = $this->parseDateTime($matches[1]);

        if (!$datetime) {
            return null;
        }

        $levelStr = strtolower($matches[3]);
        $logLevel = LogLevel::tryFrom($levelStr);

        if (!$logLevel) {
            return null;
        }

        // Try to parse JSON context from message
        $message = $matches[4];
        $context = [];

        if (($jsonStart = strpos($message, ' {"')) !== false || ($jsonStart = strpos($message, ' []')) !== false) {
            $jsonPart = substr($message, $jsonStart + 1);
            $message = substr($message, 0, $jsonStart);
            $decoded = json_decode($jsonPart, true);
            if (is_array($decoded)) {
                $context = $decoded;
            }
        }

        return new LogEntry(
            datetime: $datetime,
            level: $logLevel,
            environment: $matches[2],
            message: $message,
            stackTrace: $stackTrace,
            context: $context,
        );
    }

    private function parseDateTime(string $value): ?DateTimeImmutable
    {
        // Try various Laravel datetime formats
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d\TH:i:s.uP',
            'Y-m-d\TH:i:sP',
            'Y-m-d H:i:s.u',
        ];

        foreach ($formats as $format) {
            $dt = DateTimeImmutable::createFromFormat($format, $value);
            if ($dt !== false) {
                return $dt;
            }
        }

        // Fallback
        try {
            return new DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }

    private function resolveLogPath(string $filename): string
    {
        // Prevent directory traversal
        $filename = basename($filename);

        return storage_path('logs/' . $filename);
    }

    private function buildMeta(int $fileSize, ?int $nextOffset, int $count): array
    {
        return [
            'file_size' => $fileSize,
            'next_offset' => $nextOffset,
            'count' => $count,
            'has_more' => $nextOffset !== null && $nextOffset > 0,
        ];
    }
}
