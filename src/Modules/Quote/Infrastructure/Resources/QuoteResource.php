<?php

namespace Modules\Quote\Infrastructure\Resources;

use Modules\Quote\Domain\Entities\Quote;

class QuoteResource
{
    public static function toArray(Quote $quote): array
    {
        return [
            'id' => $quote->id,
            'content' => $quote->content,
            'author' => $quote->author,
        ];
    }

    public static function collection(array $quotes): array
    {
        return array_map(fn (Quote $quote) => self::toArray($quote), $quotes);
    }
}
