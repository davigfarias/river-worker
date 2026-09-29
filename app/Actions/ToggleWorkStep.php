<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\WorkStep;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class ToggleWorkStep
{
    public function handle(int $id): Outcome
    {
        try {
            $step = WorkStep::findOrFail($id);
            $step->update(['is_completed' => ! $step->is_completed]);

            return Outcome::noViewMessage(data: $step);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível atualizar o passo.');
        }
    }
}
