<?php

namespace Modules\Quote\Application\Actions;

use Modules\Quote\Domain\Contracts\QuoteRepositoryInterface;
use Modules\Quote\Domain\Entities\Quote;

class GetTodayQuoteAction
{
    public function __construct(
        private QuoteRepositoryInterface $repository,
    ) {}

    public function execute(): ?Quote
    {
        return $this->repository->getToday();
    }
}
