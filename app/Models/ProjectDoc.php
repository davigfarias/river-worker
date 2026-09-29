<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProjectDocFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

#[UseFactory(ProjectDocFactory::class)]
#[Fillable([
    'project_id',
    'title',
    'slug',
    'category',
    'content',
    'order',
])]
#[Table(name: 'project_docs')]
class ProjectDoc extends Model
{
    /** @use HasFactory<ProjectDocFactory> */
    use HasFactory, Searchable;

    /**
     * @return array<string, mixed>
     */
    public function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->category,
            'content' => $this->content,
        ];
    }
}
