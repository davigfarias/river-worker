<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PdiItem;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class SavePdiItem
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?int $id, array $attributes): Outcome
    {
        try {
            $record = $id === null
                ? PdiItem::create($attributes)
                : tap(PdiItem::findOrFail($id))->update($attributes);

            return Outcome::success(message: $id === null ? 'Objetivo registrado.' : 'Objetivo atualizado.', data: $record);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível salvar: objetivo.');
        }
    }
}
