<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ConceptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

#[UseFactory(ConceptFactory::class)]
#[Fillable([
    'title',
    'definition',
    'code_snippet',
    'principle_id',
])]
#[Table(name: 'concepts')]
class Concept extends Model
{
    /** @use HasFactory<ConceptFactory> */
    use HasFactory, Searchable;

    /**
     * @return BelongsTo<Principle, $this>
     */
    public function principle(): BelongsTo
    {
        return $this->belongsTo(Principle::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'definition' => $this->definition,
        ];
    }
}
