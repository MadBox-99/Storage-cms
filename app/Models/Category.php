<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'description',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Helper methods
    public function getFullPath(): string
    {
        $path = $this->name;
        $parent = $this->parent;

        while ($parent) {
            $path = $parent->name.' > '.$path;
            $parent = $parent->parent;
        }

        return $path;
    }

    public function isSubcategoryOf(self $category): bool
    {
        $parent = $this->parent;

        while ($parent) {
            if ($parent->id === $category->id) {
                return true;
            }

            $parent = $parent->parent;
        }

        return false;
    }
}
