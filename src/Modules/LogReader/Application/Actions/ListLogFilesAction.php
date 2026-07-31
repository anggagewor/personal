<?php

namespace Modules\LogReader\Application\Actions;

use Modules\LogReader\Domain\Contracts\LogReaderInterface;

class ListLogFilesAction
{
    public function __construct(
        private LogReaderInterface $reader,
    ) {}

    public function execute(): array
    {
        return $this->reader->listFiles();
    }
}
