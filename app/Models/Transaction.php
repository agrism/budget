<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'type',
        'amount',
        'transacted_at',
        'note',
        'destination_account_id',
        'is_recurring',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transacted_at' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'destination_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the formatted transacted_at date string according to locale and time presence.
     */
    public function formatTransactedAt(?string $locale = null): string
    {
        if (!$this->transacted_at) {
            return '';
        }

        $locale = $locale ?: app()->getLocale();
        $dt = $this->transacted_at instanceof \Carbon\CarbonInterface
            ? $this->transacted_at->copy()->locale($locale)
            : \Carbon\Carbon::parse($this->transacted_at)->locale($locale);

        $hasTime = $dt->format('H:i') !== '00:00';
        $isCurrentYear = (int) $dt->format('Y') === (int) \Carbon\Carbon::now()->format('Y');

        if ($locale === 'lv') {
            $lvMonths = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mai', 6 => 'Jūn', 7 => 'Jūl', 8 => 'Aug',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dec',
            ];
            $month = $lvMonths[(int) $dt->format('n')] ?? $dt->format('M');
            $day = $dt->format('d');
            $yearPart = $isCurrentYear ? '' : ' ' . $dt->format('Y');

            return $hasTime
                ? "{$month} {$day}{$yearPart}, {$dt->format('H:i')}"
                : "{$month} {$day}{$yearPart}";
        }

        $format = $isCurrentYear
            ? ($hasTime ? 'M d, H:i' : 'M d')
            : ($hasTime ? 'M d, Y, H:i' : 'M d, Y');

        return \Illuminate\Support\Str::ucfirst($dt->translatedFormat($format));
    }

    /**
     * Accessor for formatted_date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->formatTransactedAt();
    }
}
