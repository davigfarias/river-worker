<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTO\PriorityResultData;
use App\Enums\PriorityFactor;
use App\Enums\WorkItemKind;
use App\Enums\WorkItemPriority;

/**
 * WSJF adaptado: score = (impacto + urgência + risco) ÷ esforço. Antes da conta,
 * respostas de escalada impõem uma faixa mínima, sem depender do score.
 */
final readonly class ScoreWorkItem
{
    /** Score mínimo de cada faixa, da maior para a menor. */
    private const array THRESHOLDS = [
        [8.0, WorkItemPriority::Critical],
        [5.0, WorkItemPriority::High],
        [3.0, WorkItemPriority::Medium],
    ];

    /**
     * @param  array<string, mixed>|null  $answers
     */
    public function handle(WorkItemKind $kind, ?array $answers): ?PriorityResultData
    {
        $values = [];

        foreach (PriorityFactor::cases() as $factor) {
            $value = $answers[$factor->value] ?? null;

            if (! isset($factor->options()[$value])) {
                return null;
            }

            $values[$factor->value] = (int) $value;
        }

        $score = ($values['impact'] + $values['urgency'] + $values['risk']) / $values['effort'];
        $byScore = collect(self::THRESHOLDS)->first(fn (array $t) => $score >= $t[0])[1] ?? WorkItemPriority::Low;

        [$floor, $escalationReason] = $this->escalation($kind, $answers);
        $escalated = $floor !== null && $floor->value > $byScore->value;
        $priority = $escalated ? $floor : $byScore;

        $reasons = collect(PriorityFactor::cases())
            ->map(fn (PriorityFactor $f) => $f->reasonLabel().' '.mb_strtolower($f->options()[$values[$f->value]]))
            ->when($escalationReason, fn ($c) => $c->prepend($escalationReason))
            ->implode(', ');

        return new PriorityResultData(
            $priority,
            $score,
            $escalated,
            "{$priority->label()}: {$reasons}",
        );
    }

    /**
     * @param  array<string, mixed>  $answers
     * @return array{0: ?WorkItemPriority, 1: ?string}
     */
    private function escalation(WorkItemKind $kind, array $answers): array
    {
        $yes = fn (string $key) => ($answers[$key] ?? false) === true;

        return match (true) {
            $kind === WorkItemKind::Fix && $yes('in_production') && ! $yes('has_workaround') => [WorkItemPriority::Critical, 'bug em produção sem contorno'],
            $kind === WorkItemKind::Feat && $yes('blocks_customer') => [WorkItemPriority::High, 'cliente bloqueado sem isso'],
            in_array($kind, [WorkItemKind::Clean, WorkItemKind::Chore], true) && ($yes('has_dependents') || $yes('hinders_others')) => [WorkItemPriority::Medium, 'outras demandas dependem disso'],
            default => [null, null],
        };
    }
}
