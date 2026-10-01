<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WorkItemKind;
use App\Enums\WorkItemStatus;
use Database\Factories\WorkItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

#[UseFactory(WorkItemFactory::class)]
#[Fillable([
    'project_id',
    'title',
    'kind',
    'description',
    'specs',
    'status',
    'deployed_at',
    'deploy_version',
    'release_notes',
    'assessment',
])]
#[Table(name: 'work_items')]
class WorkItem extends Model
{
    /** @use HasFactory<WorkItemFactory> */
    use HasFactory, Searchable;

    /**
     * @return array<string, mixed>
     */
    public function casts(): array
    {
        return [
            'kind' => WorkItemKind::class,
            'status' => WorkItemStatus::class,
            'deployed_at' => 'datetime',
            'assessment' => 'array',
            'specs' => 'array',
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
     * @return HasMany<WorkStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkStep::class)->orderBy('order');
    }

    /**
     * @return HasMany<WorkFile, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(WorkFile::class);
    }

    /**
     * @return HasMany<WorkItemCommit, $this>
     */
    public function commits(): HasMany
    {
        return $this->hasMany(WorkItemCommit::class)->latest('committed_at');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
