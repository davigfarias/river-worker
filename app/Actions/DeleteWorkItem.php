<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\WorkItem;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class DeleteWorkItem
{
    public function handle(int $id): Outcome
    {
        try {
            WorkItem::findOrFail($id)->delete();

            return Outcome::success(message: 'Demanda excluída.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível excluir demanda.');
        }
    }
}
