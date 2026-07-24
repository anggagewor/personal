<?php

namespace Modules\Quote\Application\Actions;

use Modules\Quote\Domain\Contracts\QuoteRepositoryInterface;

class DeleteQuoteAction
{
    public function __construct(
        private QuoteRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $this->repository->delete($id);
    }
}
