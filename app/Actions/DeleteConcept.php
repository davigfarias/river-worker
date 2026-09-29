<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Concept;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class DeleteConcept
{
    public function handle(int $id): Outcome
    {
        try {
            Concept::findOrFail($id)->delete();

            return Outcome::success(message: 'Conceito excluído.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível excluir conceito.');
        }
    }
}
