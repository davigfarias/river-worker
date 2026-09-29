<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\WorkFile;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class ToggleWorkFileReviewed
{
    public function handle(int $id): Outcome
    {
        try {
            $file = WorkFile::findOrFail($id);
            $file->update(['is_reviewed' => ! $file->is_reviewed]);

            return Outcome::noViewMessage(data: $file);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível atualizar o arquivo.');
        }
    }
}
