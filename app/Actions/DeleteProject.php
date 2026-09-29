<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Project;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class DeleteProject
{
    public function handle(int $id): Outcome
    {
        try {
            Project::findOrFail($id)->delete();

            return Outcome::success(message: 'Projeto excluído.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível excluir projeto.');
        }
    }
}
