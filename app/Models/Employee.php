<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Employee extends Model
{
    use HasFactory, SoftDeletes;

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

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
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
}
