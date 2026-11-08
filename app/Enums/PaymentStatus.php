<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    /** Human readable name, e.g. "Refunded". */
    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Badge colour name used by the `pill-*` CSS classes. */
    public function color(): string
    {
        return match ($this) {
            self::Unpaid => 'warning',
            self::Paid => 'success',
            self::Failed => 'danger',
            self::Refunded => 'info',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
