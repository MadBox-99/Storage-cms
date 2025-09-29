<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'type',
        'capacity',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    // Relationships
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    // Helper methods
    public function getAvailableCapacity(): int
    {
        // TODO: Calculate based on current stock
        return $this->capacity;
    }

    public function findProduct(Product $product): ?Stock
    {
        return $this->stocks()->where('product_id', $product->id)->first();
    }
}
