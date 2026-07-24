<?php

namespace Modules\Quote\Application\Actions;

use Modules\Quote\Domain\Contracts\QuoteRepositoryInterface;
use Modules\Quote\Domain\Entities\Quote;

class CreateQuoteAction
{
    public function __construct(
        private QuoteRepositoryInterface $repository,
    ) {}

    public function execute(string $content, ?string $author = null): Quote
    {
        $quote = new Quote(
            id: null,
            content: $content,
            author: $author,
        );

        return $this->repository->save($quote);
    }
}
