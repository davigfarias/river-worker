<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\WorkItem;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;

final readonly class AttachWorkItemCommit
{
    /**
     * @param  array{sha: string, message: string, author: ?string, committed_at: ?string, url: string}  $commit
     */
    public function handle(int $workItemId, array $commit): Outcome
    {
        try {
            $linked = WorkItem::findOrFail($workItemId)->commits()->firstOrCreate(
                ['sha' => $commit['sha']],
                [
                    'message' => mb_substr($commit['message'], 0, 500),
                    'author' => $commit['author'],
                    'committed_at' => $commit['committed_at'],
                    'url' => $commit['url'],
                ],
            );

            return Outcome::success(message: 'Commit vinculado.', data: $linked);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível vincular o commit.');
        }
    }
}
