<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'payment_number',
        'payment_date',
        'amount',
        'currency',
        'exchange_rate',
        'payment_method',
        'status',
        'transaction_id',
        'notes',
    ];

    public static function generatePaymentNumber(): string
    {
        $year = now()->year;
        $month = mb_str_pad((string) now()->month, 2, '0', STR_PAD_LEFT);

        $lastPayment = self::query()
            ->where('payment_number', 'like', "PAY-{$year}{$month}-%")
            ->orderByDesc('payment_number')
            ->first();

        $sequence = $lastPayment
            ? ((int) mb_substr($lastPayment->payment_number, -4)) + 1
            : 1;

        return sprintf('PAY-%s%s-%04d', $year, $month, $sequence);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    protected static function booted(): void
    {
        self::created(function (Payment $payment): void {
            $payment->invoice->updatePaidAmount();
        });

        self::updated(function (Payment $payment): void {
            if ($payment->wasChanged('amount') || $payment->wasChanged('status')) {
                $payment->invoice->updatePaidAmount();
            }
        });

        self::deleted(function (Payment $payment): void {
            $payment->invoice->updatePaidAmount();
        });
    }

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'payment_method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
        ];
    }
}
