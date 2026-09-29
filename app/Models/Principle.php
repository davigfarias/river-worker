<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PrincipleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

#[UseFactory(PrincipleFactory::class)]
#[Fillable([
    'name',
    'acronym',
    'category',
    'summary',
    'description',
])]
#[Table(name: 'principles')]
class Principle extends Model
{
    /** @use HasFactory<PrincipleFactory> */
    use HasFactory, Searchable;

    /**
     * @return HasMany<Concept, $this>
     */
    public function concepts(): HasMany
    {
        return $this->hasMany(Concept::class);
    }

    /**
     * @return HasMany<CodeStudy, $this>
     */
    public function codeStudies(): HasMany
    {
        return $this->hasMany(CodeStudy::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'acronym' => $this->acronym,
            'category' => $this->category,
            'summary' => $this->summary,
            'description' => $this->description,
        ];
    }
}
