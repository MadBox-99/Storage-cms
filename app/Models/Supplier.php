<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CountryCode;
use App\Enums\SupplierRating;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'company_name',
        'trade_name',
        'headquarters',
        'mailing_address',
        'country_code',
        'is_eu_member',
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

    // Relationships
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function hasCertification(string $type): bool
    {
        // TODO: Implement certification check
        return false;
    }

    protected function casts(): array
    {
        return [
            'country_code' => CountryCode::class,
            'is_eu_member' => 'boolean',
            'is_active' => 'boolean',
            'headquarters' => 'array',
            'mailing_address' => 'array',
            'rating' => SupplierRating::class,
        ];
    }
}
