<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WorkFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(WorkFileFactory::class)]
#[Fillable([
    'work_item_id',
    'file_path',
    'reason_notes',
    'is_reviewed',
])]
#[Table(name: 'work_files')]
class WorkFile extends Model
{
    /** @use HasFactory<WorkFileFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    public function casts(): array
    {
        return [
            'is_reviewed' => 'boolean',
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
