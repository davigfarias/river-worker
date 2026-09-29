<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CodeStudy;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class SaveCodeStudyStep
{
    /**
     * @param  array{title: string, language: string, snippet?: string|null, markdown?: string|null}  $attributes
     */
    public function handle(int $codeStudyId, ?int $id, array $attributes): Outcome
    {
        try {
            $study = CodeStudy::findOrFail($codeStudyId);

            $step = $id === null
                ? $study->steps()->create([...$attributes, 'position' => $study->steps()->count()])
                : tap($study->steps()->findOrFail($id))->update($attributes);

            return Outcome::success(message: $id === null ? 'Passo criado.' : 'Passo atualizado.', data: $step);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível salvar o passo.');
        }
    }
}
