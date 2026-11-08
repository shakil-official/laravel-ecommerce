<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Support\Search;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A customer order for one product ("Order Now").
 *
 * The product name and unit price are copied onto the order (snapshot), so the
 * order stays correct even if the product is edited or deleted later.
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_number',
        'tracking_token',
        'product_id',
        'product_name',
        'unit_price',
        'quantity',
        'total',
        'customer_name',
        'phone',
        'email',
        'address',
        'note',
        'payment_method',
        'status',
        'payment_status',
    ];

    /** Money as 2-decimals; status columns are cast to backed enums. */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
            'quantity' => 'integer',
            'payment_method' => PaymentMethod::class,
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
        ];
    }

    /** Model lifecycle hooks (slug, cascades, generated numbers). */
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateNumber();
            }

            if (empty($order->tracking_token)) {
                $order->tracking_token = static::generateToken();
            }
        });
    }

    /** Human friendly unique number, e.g. BS-261107-K3F9Q. */
    public static function generateNumber(): string
    {
        do {
            $number = 'BS-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    /** Characters used in tracking tokens (no 0/O/1/I/L to avoid typing mistakes). */
    public const TOKEN_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /**
     * Unique, hard-to-guess tracking token, e.g. K7M2-9QXA-W4TP
     * (12 characters from a 31 character alphabet = about 59 bits).
     */
    public static function generateToken(): string
    {
        do {
            $chars = '';
            for ($i = 0; $i < 12; $i++) {
                $chars .= self::TOKEN_ALPHABET[random_int(0, strlen(self::TOKEN_ALPHABET) - 1)];
            }
            $token = implode('-', str_split($chars, 4));
        } while (static::where('tracking_token', $token)->exists());

        return $token;
    }

    /** Turn user input ("k7m2 9qxa-w4tp") into the stored format ("K7M2-9QXA-W4TP"). */
    public static function normalizeToken(string $input): string
    {
        $clean = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $input));

        return implode('-', str_split($clean, 4));
    }

    /** Public link to this order's tracking page. */
    public function getTrackingUrlAttribute(): string
    {
        return route('shop.track.show', $this->tracking_token);
    }

    /** Phone number with the middle digits hidden, e.g. 017******78. */
    public function getMaskedPhoneAttribute(): string
    {
        $phone = (string) $this->phone;

        if (strlen($phone) <= 5) {
            return $phone;
        }

        return substr($phone, 0, 3).str_repeat('*', strlen($phone) - 5).substr($phone, -2);
    }

    /** The ordered product (null when it has been deleted since). */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Match order number, tracking token, customer name or phone. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        $like = Search::like($term);

        return $query->when($term !== '', fn (Builder $q) => $q->where(function (Builder $w) use ($like) {
            $w->whereRaw("order_number like ? escape '!'", [$like])
                ->orWhereRaw("tracking_token like ? escape '!'", [$like])
                ->orWhereRaw("customer_name like ? escape '!'", [$like])
                ->orWhereRaw("phone like ? escape '!'", [$like]);
        }));
    }
}
