<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(ProjectFactory::class)]
#[Fillable([
    'name',
    'slug',
    'repository_url',
    'description',
])]
#[Table(name: 'projects')]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * @return HasMany<ProjectDoc, $this>
     */
    public function docs(): HasMany
    {
        return $this->hasMany(ProjectDoc::class)->orderBy('order');
    }

    /**
     * @return HasMany<WorkItem, $this>
     */
    public function workItems(): HasMany
    {
        return $this->hasMany(WorkItem::class);
    }
}
