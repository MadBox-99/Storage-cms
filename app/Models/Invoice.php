<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

final class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'order_id',
        'receipt_id',
        'supplier_id',
        'customer_id',
        'type',
        'status',
        'invoice_date',
        'due_date',
        'payment_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'currency',
        'exchange_rate',
        'notes',
        'billing_address',
        'shipping_address',
    ];

    public static function createFromReceipt(Receipt $receipt): self
    {
        return DB::transaction(function () use ($receipt): self {
            $order = $receipt->order;

            $invoice = self::create([
                'invoice_number' => self::generateInvoiceNumber($order->type),
                'order_id' => $order->id,
                'receipt_id' => $receipt->id,
                'supplier_id' => $order->supplier_id,
                'customer_id' => $order->customer_id,
                'type' => $order->type,
                'status' => InvoiceStatus::DRAFT,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'currency' => $order->currency ?? 'HUF',
                'billing_address' => $order->billing_address,
                'shipping_address' => $order->delivery_address,
            ]);

            // Create invoice lines from receipt lines
            foreach ($receipt->receiptLines as $receiptLine) {
                $orderLine = $receiptLine->orderLine;
                $taxRate = 27.0; // Default Hungarian VAT
                $lineSubtotal = $receiptLine->quantity * $orderLine->unit_price;
                $taxAmount = $lineSubtotal * ($taxRate / 100);

                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $orderLine->product_id,
                    'order_line_id' => $orderLine->id,
                    'receipt_line_id' => $receiptLine->id,
                    'description' => $orderLine->product->name ?? $orderLine->description,
                    'quantity' => $receiptLine->quantity,
                    'unit' => $orderLine->product->unit ?? 'pcs',
                    'unit_price' => $orderLine->unit_price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineSubtotal + $taxAmount,
                ]);
            }

            $invoice->calculateTotals();

            return $invoice;
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoiceLines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->invoiceLines()->sum(DB::raw('quantity * unit_price'));
        $this->tax_amount = $this->invoiceLines()->sum('tax_amount');
        $this->discount_amount = $this->invoiceLines()->sum('discount_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->save();
    }

    public function getRemainingAmount(): float
    {
        return (float) ($this->total_amount - $this->paid_amount);
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    public function updatePaidAmount(): void
    {
        $this->paid_amount = $this->payments()->where('status', 'COMPLETED')->sum('amount');

        if ($this->isFullyPaid()) {
            $this->status = InvoiceStatus::PAID;
            $this->payment_date = now();
        } elseif ($this->paid_amount > 0) {
            $this->status = InvoiceStatus::PARTIALLY_PAID;
        }

        $this->save();
    }

    protected function casts(): array
    {
        return [
            'type' => OrderType::class,
            'status' => InvoiceStatus::class,
            'invoice_date' => 'date',
            'due_date' => 'date',
            'payment_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'billing_address' => 'json',
            'shipping_address' => 'json',
        ];
    }

    private static function generateInvoiceNumber(OrderType $type): string
    {
        $prefix = $type === OrderType::PURCHASE ? 'PI' : 'SI';
        $year = now()->year;
        $month = mb_str_pad((string) now()->month, 2, '0', STR_PAD_LEFT);

        $lastInvoice = self::query()
            ->where('invoice_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderByDesc('invoice_number')
            ->first();

        $sequence = $lastInvoice
            ? ((int) mb_substr($lastInvoice->invoice_number, -4)) + 1
            : 1;

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }
}
