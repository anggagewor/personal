<?php

namespace Modules\Accounting\Application\Actions;

use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\Enums\AccountType;

class ProvisionDefaultAccountsAction
{
    public function __construct(
        private AccountRepositoryInterface $repository,
    ) {}

    public function execute(int $userId): void
    {
        if ($this->repository->countByUser($userId) > 0) {
            return;
        }

        $defaults = [
            ['code' => '1000', 'name' => 'Kas', 'type' => AccountType::Asset],
            ['code' => '1100', 'name' => 'Bank', 'type' => AccountType::Asset],
            ['code' => '1200', 'name' => 'Piutang Usaha', 'type' => AccountType::Asset],
            ['code' => '1300', 'name' => 'Perlengkapan', 'type' => AccountType::Asset],
            ['code' => '1400', 'name' => 'Peralatan', 'type' => AccountType::Asset],
            ['code' => '2000', 'name' => 'Utang Usaha', 'type' => AccountType::Liability],
            ['code' => '2100', 'name' => 'Utang Bank', 'type' => AccountType::Liability],
            ['code' => '3000', 'name' => 'Modal', 'type' => AccountType::Equity],
            ['code' => '3100', 'name' => 'Prive', 'type' => AccountType::Equity],
            ['code' => '4000', 'name' => 'Pendapatan Jasa', 'type' => AccountType::Revenue],
            ['code' => '4100', 'name' => 'Pendapatan Lain-lain', 'type' => AccountType::Revenue],
            ['code' => '5000', 'name' => 'Beban Gaji', 'type' => AccountType::Expense],
            ['code' => '5100', 'name' => 'Beban Sewa', 'type' => AccountType::Expense],
            ['code' => '5200', 'name' => 'Beban Listrik', 'type' => AccountType::Expense],
            ['code' => '5300', 'name' => 'Beban Perlengkapan', 'type' => AccountType::Expense],
            ['code' => '5400', 'name' => 'Beban Lain-lain', 'type' => AccountType::Expense],
        ];

        foreach ($defaults as $item) {
            $account = new Account(
                id: null,
                userId: $userId,
                code: $item['code'],
                name: $item['name'],
                type: $item['type'],
                normalBalance: $item['type']->normalBalance(),
                parentId: null,
                depth: 1,
            );

            $this->repository->save($account);
        }
    }
}
