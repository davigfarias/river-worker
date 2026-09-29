<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\WorkFile;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class DeleteWorkFile
{
    public function handle(int $id): Outcome
    {
        try {
            WorkFile::findOrFail($id)->delete();

            return Outcome::success(message: 'Arquivo excluído.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível excluir arquivo.');
        }
    }
}
