<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\WorkItem;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class UpdateWorkItem
{
    /**
     * @param  array{title?: string, kind?: string, project_id?: int|null, description?: string|null, assessment?: array<string, mixed>|null}  $attributes
     */
    public function handle(int $id, array $attributes): Outcome
    {
        try {
            tap(WorkItem::findOrFail($id))->update($attributes);

            return Outcome::success(message: 'Demanda atualizada.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível atualizar a demanda.');
        }
    }
}
