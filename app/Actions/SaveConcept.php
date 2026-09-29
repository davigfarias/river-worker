<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Concept;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class SaveConcept
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?int $id, array $attributes): Outcome
    {
        try {
            $record = $id === null
                ? Concept::create($attributes)
                : tap(Concept::findOrFail($id))->update($attributes);

            return Outcome::success(message: $id === null ? 'Conceito criado.' : 'Conceito atualizado.', data: $record);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível salvar: conceito.');
        }
    }
}
