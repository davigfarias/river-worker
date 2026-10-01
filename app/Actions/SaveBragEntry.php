<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\BragEntry;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class SaveBragEntry
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?int $id, array $attributes): Outcome
    {
        try {
            $record = $id === null
                ? BragEntry::create($attributes)
                : tap(BragEntry::findOrFail($id))->update($attributes);

            return Outcome::success(message: $id === null ? 'Conquista registrada.' : 'Conquista atualizada.', data: $record);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível salvar: conquista.');
        }
    }
}
