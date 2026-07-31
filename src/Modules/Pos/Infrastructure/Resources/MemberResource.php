<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\Member;

class MemberResource
{
    public static function toArray(Member $member): array
    {
        return [
            'id' => $member->id,
            'outlet_id' => $member->outletId,
            'name' => $member->name,
            'phone' => $member->phone,
            'email' => $member->email,
            'created_at' => $member->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $members): array
    {
        return array_map(fn (Member $member) => self::toArray($member), $members);
    }
}
