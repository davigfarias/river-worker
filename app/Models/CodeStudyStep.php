<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CodeStudyStepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(CodeStudyStepFactory::class)]
#[Fillable([
    'code_study_id',
    'title',
    'language',
    'snippet',
    'markdown',
    'position',
])]
#[Table(name: 'code_study_steps')]
class CodeStudyStep extends Model
{
    /** @use HasFactory<CodeStudyStepFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    public function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<CodeStudy, $this>
     */
    public function codeStudy(): BelongsTo
    {
        return $this->belongsTo(CodeStudy::class);
    }
}
