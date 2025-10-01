<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IntrastatDirection;
use App\Enums\IntrastatStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class IntrastatDeclaration extends Model
{
    use HasFactory;

    protected $fillable = [
        'declaration_number',
        'direction',
        'reference_year',
        'reference_month',
        'declaration_date',
        'submitted_at',
        'submitted_by',
        'total_invoice_value',
        'total_statistical_value',
        'total_net_mass',
        'notes',
        'status',
    ];

    public function intrastatLines(): HasMany
    {
        return $this->hasMany(IntrastatLine::class);
    }

    public function calculateTotals(): void
    {
        $this->total_invoice_value = $this->intrastatLines()->sum('invoice_value');
        $this->total_statistical_value = $this->intrastatLines()->sum('statistical_value');
        $this->total_net_mass = $this->intrastatLines()->sum('net_mass');
        $this->save();
    }

    public function canBeEdited(): bool
    {
        return $this->status->isEditable();
    }

    protected function casts(): array
    {
        return [
            'direction' => IntrastatDirection::class,
            'declaration_date' => 'date',
            'submitted_at' => 'date',
            'total_invoice_value' => 'decimal:2',
            'total_statistical_value' => 'decimal:2',
            'total_net_mass' => 'decimal:3',
            'status' => IntrastatStatus::class,
        ];
    }
}
