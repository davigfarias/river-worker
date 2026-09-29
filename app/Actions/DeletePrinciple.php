<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Principle;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class DeletePrinciple
{
    public function handle(int $id): Outcome
    {
        try {
            Principle::findOrFail($id)->delete();

            return Outcome::success(message: 'Princípio excluído.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível excluir princípio.');
        }
    }
}
