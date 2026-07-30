<?php

namespace Modules\Accounting\Infrastructure\Resources;

/**
 * Thin helper for ledger response formatting.
 * The GetLedgerAction already returns structured data (opening_balance + lines),
 * so this resource is kept minimal as a passthrough utility.
 */
class LedgerResource
{
    public static function toArray(array $ledgerData): array
    {
        return $ledgerData;
    }
}
