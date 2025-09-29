<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'employee_code',
        'first_name',
        'last_name',
        'position',
        'department',
        'phone',
        'is_active',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Helper methods
    public function getFullName(): string
    {
        return mb_trim($this->first_name.' '.$this->last_name);
    }

    public function hasAccess(Warehouse $warehouse): bool
    {
        return $this->warehouse_id === $warehouse->id || $this->user->is_super_admin;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
