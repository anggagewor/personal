<?php

namespace Modules\Accounting\Infrastructure\Resources;

use Modules\Accounting\Domain\Entities\Account;

class AccountResource
{
    public static function toArray(Account $account): array
    {
        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'type' => $account->type->value,
            'normal_balance' => $account->normalBalance->value,
            'parent_id' => $account->parentId,
            'depth' => $account->depth,
            'created_at' => $account->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $accounts): array
    {
        return array_map(fn (Account $account) => self::toArray($account), $accounts);
    }
}
