<?php

declare(strict_types=1);

namespace App\Models;

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
    'description',
    'status',
    'deployed_at',
    'deploy_version',
    'release_notes',
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
            'status' => WorkItemStatus::class,
            'deployed_at' => 'datetime',
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
