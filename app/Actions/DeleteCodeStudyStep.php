<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CodeStudyStep;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class DeleteCodeStudyStep
{
    public function handle(int $id): Outcome
    {
        try {
            CodeStudyStep::findOrFail($id)->delete();

            return Outcome::success(message: 'Passo excluído.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível excluir passo.');
        }
    }
}
