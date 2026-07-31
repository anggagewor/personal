<?php

namespace Modules\Pos\Domain\Enums;

enum PaymentMethodType: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case EWallet = 'e_wallet';
    case Custom = 'custom';
}
