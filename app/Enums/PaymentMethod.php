<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cod = 'cod';
    case Bkash = 'bkash';
    case Nagad = 'nagad';
    case BankTransfer = 'bank_transfer';

    /** Human readable name shown in forms and tables. */
    public function label(): string
    {
        return match ($this) {
            self::Cod => 'Cash on delivery',
            self::Bkash => 'bKash',
            self::Nagad => 'Nagad',
            self::BankTransfer => 'Bank transfer',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
