<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\WorkItemStatus;
use App\Models\WorkItem;
use App\Support\Outcome;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

final readonly class RegisterDeploy
{
    public function handle(int $id, ?string $version, ?string $releaseNotes, ?CarbonImmutable $deployedAt = null): Outcome
    {
        try {
            WorkItem::findOrFail($id)->update([
                'status' => WorkItemStatus::Deployed,
                'deployed_at' => $deployedAt ?? now(),
                'deploy_version' => $version,
                'release_notes' => $releaseNotes,
            ]);

            return Outcome::success(message: 'Deploy registrado.');
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível registrar o deploy.');
        }
    }
}
