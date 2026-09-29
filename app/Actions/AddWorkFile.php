<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\WorkItem;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class AddWorkFile
{
    public function handle(int $workItemId, string $filePath, ?string $reasonNotes): Outcome
    {
        try {
            $file = WorkItem::findOrFail($workItemId)->files()->create([
                'file_path' => $filePath,
                'reason_notes' => $reasonNotes,
            ]);

            return Outcome::success(message: 'Arquivo adicionado.', data: $file);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível adicionar o arquivo.');
        }
    }
}
