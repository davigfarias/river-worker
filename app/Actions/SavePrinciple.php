<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Principle;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class SavePrinciple
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?int $id, array $attributes): Outcome
    {
        try {
            $record = $id === null
                ? Principle::create($attributes)
                : tap(Principle::findOrFail($id))->update($attributes);

            return Outcome::success(message: $id === null ? 'Princípio criado.' : 'Princípio atualizado.', data: $record);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível salvar: princípio.');
        }
    }
}
