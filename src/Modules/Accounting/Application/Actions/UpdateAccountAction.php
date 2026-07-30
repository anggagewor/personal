<?php

namespace Modules\Accounting\Application\Actions;

use Modules\Accounting\Application\DTO\AccountData;
use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\Exceptions\MaxDepthExceededException;

class UpdateAccountAction
{
    private const MAX_DEPTH = 3;

    public function __construct(
        private AccountRepositoryInterface $repository,
    ) {}

    public function execute(Account $account, AccountData $data): Account
    {
        // Code and type are immutable — only update name and parentId
        $account->name = $data->name;

        $depth = 1;

        if ($data->parentId !== null) {
            $parent = $this->repository->findById($data->parentId);

            if ($parent === null || $parent->type !== $account->type) {
                throw new \DomainException('Parent akun harus memiliki tipe yang sama.');
            }

            $depth = $parent->depth + 1;

            if ($depth > self::MAX_DEPTH) {
                throw MaxDepthExceededException::create(self::MAX_DEPTH);
            }
        }

        $account->parentId = $data->parentId;
        $account->depth = $depth;

        return $this->repository->save($account);
    }
}
