<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code',
        'name',
        'email',
        'phone',
        'billing_address',
        'shipping_address',
        'credit_limit',
        'balance',
        'type',
    ];

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Helper methods
    public function checkCreditLimit(float $amount): bool
    {
        return ($this->balance + $amount) <= $this->credit_limit;
    }

    public function updateBalance(float $amount): void
    {
        $this->increment('balance', $amount);
    }
}
