<?php

namespace Modules\Accounting\Infrastructure\Resources;

/**
 * Thin helper for report response formatting.
 * The report actions (GetTrialBalanceAction, GetIncomeStatementAction, GetBalanceSheetAction)
 * already return fully structured arrays, so this resource is a passthrough utility.
 */
class ReportResource
{
    public static function toArray(array $reportData): array
    {
        return $reportData;
    }
}
