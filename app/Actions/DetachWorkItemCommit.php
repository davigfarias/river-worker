<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\WorkItemCommit;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class DetachWorkItemCommit
{
    public function handle(int $id): Outcome
    {
        try {
            WorkItemCommit::findOrFail($id)->delete();

            return Outcome::success(message: 'Commit desvinculado.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível desvincular o commit.');
        }
    }
}
