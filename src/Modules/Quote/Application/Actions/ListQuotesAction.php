<?php

namespace Modules\Quote\Application\Actions;

use Modules\Quote\Domain\Contracts\QuoteRepositoryInterface;

class ListQuotesAction
{
    public function __construct(
        private QuoteRepositoryInterface $repository,
    ) {}

    /**
     * @return array{items: \Modules\Quote\Domain\Entities\Quote[], total: int, per_page: int, current_page: int, last_page: int}
     */
    public function execute(int $page = 1, int $perPage = 10, ?string $search = null): array
    {
        return $this->repository->paginate($page, $perPage, $search);
    }
}
