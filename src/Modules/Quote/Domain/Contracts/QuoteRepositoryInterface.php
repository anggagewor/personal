<?php

namespace Modules\Quote\Domain\Contracts;

use Modules\Quote\Domain\Entities\Quote;

interface QuoteRepositoryInterface
{
    public function getToday(): ?Quote;

    /**
     * @return array{items: Quote[], total: int, per_page: int, current_page: int, last_page: int}
     */
    public function paginate(int $page = 1, int $perPage = 10, ?string $search = null): array;

    public function save(Quote $quote): Quote;

    public function delete(int $id): void;
}
