<?php

namespace App\Enums;

/**
 * Fulfilment status of an order (changed by the admin).
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Packaging = 'packaging';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /** Human readable name, e.g. "Packaging". */
    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Bootstrap colour name used for the badge. */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::Packaging => 'primary',
            self::Shipped => 'secondary',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };
    }

    /** Delivered and cancelled orders are finished. */
    public function isFinal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled], true);
    }

    /** Bootstrap Icons class for the tracking timeline. */
    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'bi-receipt',
            self::Confirmed => 'bi-patch-check',
            self::Packaging => 'bi-box-seam',
            self::Shipped => 'bi-truck',
            self::Delivered => 'bi-house-check',
            self::Cancelled => 'bi-x-octagon',
        };
    }

    /** Short sentence shown to the customer on the tracking page. */
    public function description(): string
    {
        return match ($this) {
            self::Pending => 'We have received your order and will confirm it shortly.',
            self::Confirmed => 'Your order is confirmed.',
            self::Packaging => 'We are packing your items.',
            self::Shipped => 'Your order is on its way.',
            self::Delivered => 'Your order has been delivered. Enjoy!',
            self::Cancelled => 'This order was cancelled.',
        };
    }

    /**
     * The statuses shown as steps on the tracking timeline (cancelled is not a step).
     *
     * @return array<int, self>
     */
    public static function trackingSteps(): array
    {
        return [self::Pending, self::Confirmed, self::Packaging, self::Shipped, self::Delivered];
    }

    /** Position on the timeline (0 = first step), or null for cancelled. */
    public function step(): ?int
    {
        $index = array_search($this, self::trackingSteps(), true);

        return $index === false ? null : $index;
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
