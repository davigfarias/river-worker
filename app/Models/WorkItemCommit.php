<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WorkItemCommitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(WorkItemCommitFactory::class)]
#[Fillable([
    'work_item_id',
    'sha',
    'message',
    'author',
    'committed_at',
    'url',
])]
#[Table(name: 'work_item_commits')]
class WorkItemCommit extends Model
{
    /** @use HasFactory<WorkItemCommitFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    public function casts(): array
    {
        return [
            'committed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WorkItem, $this>
     */
    public function workItem(): BelongsTo
    {
        return $this->belongsTo(WorkItem::class);
    }
}
