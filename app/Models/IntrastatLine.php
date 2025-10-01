<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IntrastatDeliveryTerms;
use App\Enums\IntrastatTransactionType;
use App\Enums\IntrastatTransportMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class IntrastatLine extends Model
{
    protected $fillable = [
        'intrastat_declaration_id',
        'order_id',
        'product_id',
        'supplier_id',
        'cn_code',
        'quantity',
        'net_mass',
        'supplementary_unit',
        'supplementary_quantity',
        'invoice_value',
        'statistical_value',
        'country_of_origin',
        'country_of_consignment',
        'country_of_destination',
        'transaction_type',
        'transport_mode',
        'delivery_terms',
        'description',
    ];

    public function intrastatDeclaration(): BelongsTo
    {
        return $this->belongsTo(IntrastatDeclaration::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'net_mass' => 'decimal:3',
            'supplementary_quantity' => 'decimal:2',
            'invoice_value' => 'decimal:2',
            'statistical_value' => 'decimal:2',
            'transaction_type' => IntrastatTransactionType::class,
            'transport_mode' => IntrastatTransportMode::class,
            'delivery_terms' => IntrastatDeliveryTerms::class,
        ];
    }
}
