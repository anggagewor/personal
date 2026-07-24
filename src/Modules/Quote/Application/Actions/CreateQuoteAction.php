<?php

namespace Modules\Quote\Application\Actions;

use Modules\Quote\Application\DTO\QuoteData;
use Modules\Quote\Domain\Contracts\QuoteRepositoryInterface;
use Modules\Quote\Domain\Entities\Quote;

class CreateQuoteAction
{
    public function __construct(
        private QuoteRepositoryInterface $repository,
    ) {}

    public function execute(QuoteData $data): Quote
    {
        $quote = new Quote(
            id: null,
            content: $data->content,
            author: $data->author,
        );

        return $this->repository->save($quote);
    }
}
