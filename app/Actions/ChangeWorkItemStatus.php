<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\WorkItemStatus;
use App\Models\WorkItem;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class ChangeWorkItemStatus
{
    public function handle(int $id, WorkItemStatus $status): Outcome
    {
        try {
            $item = WorkItem::findOrFail($id);

            $item->update([
                'status' => $status,
                'deployed_at' => $status === WorkItemStatus::Deployed ? ($item->deployed_at ?? now()) : null,
            ]);

            return Outcome::success(message: 'Estado atualizado.', data: $item);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível alterar o estado da demanda.');
        }
    }
}
