<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WorkStepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(WorkStepFactory::class)]
#[Fillable([
    'work_item_id',
    'title',
    'is_completed',
    'order',
])]
#[Table(name: 'work_steps')]
class WorkStep extends Model
{
    /** @use HasFactory<WorkStepFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    public function casts(): array
    {
        return [
            'is_completed' => 'boolean',
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
