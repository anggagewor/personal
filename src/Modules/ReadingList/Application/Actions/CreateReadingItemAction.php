<?php

namespace Modules\ReadingList\Application\Actions;

use Modules\ReadingList\Application\DTO\ReadingItemData;
use Modules\ReadingList\Domain\Contracts\ReadingListRepositoryInterface;
use Modules\ReadingList\Domain\Entities\ReadingItem;

class CreateReadingItemAction
{
    public function __construct(
        private ReadingListRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, ReadingItemData $data): ReadingItem
    {
        $domain = null;
        if ($data->url) {
            $domain = parse_url($data->url, PHP_URL_HOST);
        }

        $item = new ReadingItem(
            id: null,
            userId: $userId,
            title: $data->title ?? $data->url ?? '',
            url: $data->url,
            domain: $domain,
        );

        return $this->repository->save($item);
    }
}
