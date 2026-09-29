<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CodeStudyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

#[UseFactory(CodeStudyFactory::class)]
#[Fillable([
    'title',
    'topic',
    'principle_id',
    'description',
])]
#[Table(name: 'code_studies')]
class CodeStudy extends Model
{
    /** @use HasFactory<CodeStudyFactory> */
    use HasFactory, Searchable;

    /**
     * @return BelongsTo<Principle, $this>
     */
    public function principle(): BelongsTo
    {
        return $this->belongsTo(Principle::class);
    }

    /**
     * @return HasMany<CodeStudyStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(CodeStudyStep::class)->orderBy('position');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'topic' => $this->topic,
            'description' => $this->description,
        ];
    }
}
