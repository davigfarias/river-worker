<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\BragEntry;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class DeleteBragEntry
{
    public function handle(int $id): Outcome
    {
        try {
            BragEntry::findOrFail($id)->delete();

            return Outcome::success(message: 'Conquista excluída.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível excluir conquista.');
        }
    }
}
