<?php

namespace Modules\Pos\Infrastructure\Resources;

class ReportResource
{
    /**
     * Generic resource that passes through report data.
     * Report queries already return structured arrays.
     */
    public static function toArray(array $data): array
    {
        return $data;
    }

    public static function collection(array $reports): array
    {
        return $reports;
    }
}
