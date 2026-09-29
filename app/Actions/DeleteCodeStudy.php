<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CodeStudy;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class DeleteCodeStudy
{
    public function handle(int $id): Outcome
    {
        try {
            CodeStudy::findOrFail($id)->delete();

            return Outcome::success(message: 'Estudo excluído.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível excluir estudo.');
        }
    }
}
