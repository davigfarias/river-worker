<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\WorkItemStatus;
use App\Models\WorkItem;
use App\Support\Outcome;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cria a demanda já com o checklist fixo de config('worker.checklist').
 */
final readonly class CreateWorkItem
{
    /**
     * @param  array{title: string, project_id?: int|null, description?: string|null}  $attributes
     */
    public function handle(array $attributes): Outcome
    {
        try {
            $item = DB::transaction(function () use ($attributes): WorkItem {
                $item = WorkItem::create([...$attributes, 'status' => WorkItemStatus::Backlog]);

                $item->steps()->createMany(
                    collect(config('worker.checklist'))
                        ->map(fn (string $title, int $order): array => ['title' => $title, 'order' => $order])
                        ->all()
                );

                return $item;
            });

            return Outcome::success(message: 'Demanda criada.', data: $item);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível criar a demanda.');
        }
    }
}
