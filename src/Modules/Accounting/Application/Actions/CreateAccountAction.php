<?php

namespace Modules\Accounting\Application\Actions;

use Modules\Accounting\Application\DTO\AccountData;
use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\Enums\AccountType;
use Modules\Accounting\Domain\Exceptions\DuplicateAccountCodeException;
use Modules\Accounting\Domain\Exceptions\MaxDepthExceededException;

class CreateAccountAction
{
    private const MAX_DEPTH = 3;

    public function __construct(
        private AccountRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, AccountData $data): Account
    {
        $existing = $this->repository->findByCode($userId, $data->code);

        if ($existing !== null) {
            throw DuplicateAccountCodeException::create($data->code);
        }

        $type = AccountType::from($data->type);
        $depth = 1;

        if ($data->parentId !== null) {
            $parent = $this->repository->findById($data->parentId);

            if ($parent === null || $parent->type !== $type) {
                throw new \DomainException('Parent akun harus memiliki tipe yang sama.');
            }

            $depth = $parent->depth + 1;

            if ($depth > self::MAX_DEPTH) {
                throw MaxDepthExceededException::create(self::MAX_DEPTH);
            }
        }

        $account = new Account(
            id: null,
            userId: $userId,
            code: $data->code,
            name: $data->name,
            type: $type,
            normalBalance: $type->normalBalance(),
            parentId: $data->parentId,
            depth: $depth,
        );

        return $this->repository->save($account);
    }
}
