<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CodeStudy;
use App\Support\Outcome;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final readonly class ReorderCodeStudyStep
{
    /**
     * Move o passo para a posição informada (0-based), reindexando os demais.
     */
    public function handle(int $codeStudyId, int $stepId, int $position): Outcome
    {
        try {
            DB::transaction(function () use ($codeStudyId, $stepId, $position): void {
                $ids = CodeStudy::findOrFail($codeStudyId)->steps()->pluck('id')->all();

                $ids = array_values(array_filter($ids, fn (int $id): bool => $id !== $stepId));
                array_splice($ids, max(0, min($position, count($ids))), 0, [$stepId]);

                foreach ($ids as $index => $id) {
                    CodeStudy::findOrFail($codeStudyId)->steps()->whereKey($id)->update(['position' => $index]);
                }
            });

            return Outcome::noViewMessage();
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível reordenar os passos.');
        }
    }
}
