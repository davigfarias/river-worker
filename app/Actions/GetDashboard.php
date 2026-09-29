<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ReadingStatus;
use App\Enums\WorkItemStatus;
use App\Models\ProjectDoc;
use App\Models\ReadingNote;
use App\Models\ReferenceMaterial;
use App\Models\WorkItem;
use App\Support\Outcome;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

/**
 * Apanhado geral da home. O projeto "ativo" é o da demanda mais recentemente
 * atualizada; o foco é a demanda em andamento mais recente dele.
 */
final readonly class GetDashboard
{
    public function handle(): Outcome
    {
        try {
            $latest = WorkItem::query()->latest('updated_at')->latest('id')->first();
            $project = $latest?->project;

            $scoped = fn () => WorkItem::query()
                ->when($project, fn ($query) => $query->where('project_id', $project->id));

            $open = [WorkItemStatus::Backlog->value, WorkItemStatus::Deployed->value];

            $focus = $scoped()
                ->whereNotIn('status', $open)
                ->with(['steps', 'files'])
                ->latest('updated_at')
                ->first();

            $steps = $focus?->steps ?? collect();
            $today = CarbonImmutable::now()->startOfDay();

            $counts = $scoped()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

            return Outcome::noViewMessage(data: [
                'project' => $project,
                'focus' => $focus,
                'nextStep' => $steps->firstWhere('is_completed', false),
                'progress' => $steps->isEmpty() ? 0 : (int) round($steps->where('is_completed', true)->count() / $steps->count() * 100),
                'doneSteps' => $steps->where('is_completed', true)->count(),
                'totalSteps' => $steps->count(),
                'pendingFiles' => $focus?->files->where('is_reviewed', false)->count() ?? 0,
                'pipeline' => collect(WorkItemStatus::cases())->mapWithKeys(fn (WorkItemStatus $status): array => [$status->value => (int) ($counts[$status->value] ?? 0)])->all(),
                'inDev' => $scoped()->whereNotIn('status', $open)->latest('updated_at')->limit(6)->get(),
                'backlog' => $scoped()->where('status', WorkItemStatus::Backlog->value)->latest('updated_at')->limit(6)->get(),
                'deploys' => $scoped()->where('status', WorkItemStatus::Deployed->value)->latest('deployed_at')->limit(5)->get(),
                'weekDeploys' => WorkItem::where('status', WorkItemStatus::Deployed->value)->where('deployed_at', '>=', $today->subDays(6))->count(),
                'dueReviews' => ReadingNote::query()->whereNull('consolidated_at')->whereDate('next_review_at', '<=', $today->toDateString())->count(),
                'reading' => ReferenceMaterial::query()->where('reading_status', ReadingStatus::Reading->value)->latest('updated_at')->limit(4)->get(),
                'recentDocs' => ProjectDoc::query()->with('project')->latest('updated_at')->limit(5)->get(),
            ]);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível carregar o painel.');
        }
    }
}
