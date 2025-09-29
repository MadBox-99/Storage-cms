<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'company_name',
        'trade_name',
        'headquarters',
        'mailing_address',
        'tax_number',
        'eu_tax_number',
        'company_registration_number',
        'bank_account_number',
        'contact_person',
        'email',
        'phone',
        'website',
        'rating',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'headquarters' => 'array',
        'mailing_address' => 'array',
    ];

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Helper methods
    public function updateRating(string $rating): void
    {
        $this->update(['rating' => $rating]);
    }

    public function hasCertification(string $type): bool
    {
        // TODO: Implement certification check
        return false;
    }
}
