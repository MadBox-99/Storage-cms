<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CnCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'supplementary_unit',
    ];

    public function intrastatLines(): HasMany
    {
        return $this->hasMany(IntrastatLine::class);
    }
}
