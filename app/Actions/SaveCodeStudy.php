<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CodeStudy;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class SaveCodeStudy
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?int $id, array $attributes): Outcome
    {
        try {
            $record = $id === null
                ? CodeStudy::create($attributes)
                : tap(CodeStudy::findOrFail($id))->update($attributes);

            return Outcome::success(message: $id === null ? 'Estudo criado.' : 'Estudo atualizado.', data: $record);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível salvar: estudo.');
        }
    }
}
