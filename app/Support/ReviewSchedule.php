<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Escada de repetição espaçada em dias corridos: o estágio 1 cobra a anotação
 * amanhã, o 2 em dois dias, e assim por diante. Esgotada a escada, a anotação
 * é considerada consolidada e sai da fila.
 */
final readonly class ReviewSchedule
{
    /**
     * Intervalo, em dias, de cada estágio da escada.
     *
     * @var array<int, int>
     */
    public const array STEPS = [1 => 1, 2 => 2, 3 => 4, 4 => 8];

    public const int FINAL_STAGE = 4;

    /**
     * Estágio a que a nota vai depois da revisão: acertou avança um degrau,
     * travou volta para o primeiro.
     */
    public static function nextStage(int $currentStage, bool $recalled): int
    {
        if (! $recalled) {
            return 1;
        }

        return min(self::normalizeStage($currentStage) + 1, self::FINAL_STAGE + 1);
    }

    /**
     * Notas criadas antes da fila de revisão existir carregam estágio zero.
     * Elas valem como nunca revisadas, ou seja, o primeiro degrau da escada.
     */
    public static function normalizeStage(int $stage): int
    {
        return max(1, min($stage, self::FINAL_STAGE + 1));
    }

    public static function isConsolidated(int $stage): bool
    {
        return $stage > self::FINAL_STAGE;
    }

    /**
     * Data em que a anotação volta a ser cobrada, ou null quando ela já
     * percorreu a escada inteira.
     */
    public static function dueDateForDaily(int $stage, CarbonInterface $from): ?CarbonImmutable
    {
        if (self::isConsolidated(self::normalizeStage($stage))) {
            return null;
        }

        $daysAhead = self::STEPS[self::normalizeStage($stage)] ?? 1;

        return CarbonImmutable::instance($from)->startOfDay()->addDays($daysAhead);
    }

    /**
     * Data da primeira cobrança de uma anotação recém-criada: amanhã.
     */
    public static function firstDueDateDaily(CarbonInterface $from): ?CarbonImmutable
    {
        return self::dueDateForDaily(1, $from);
    }

    /**
     * Rótulo humano do estágio, usado nos cartões da fila do dia.
     */
    public static function stageLabel(int $stage): string
    {
        if (self::isConsolidated($stage)) {
            return 'Consolidada';
        }

        return match (self::normalizeStage($stage)) {
            1 => '1ª revisão',
            2 => '2ª revisão',
            3 => '3ª revisão',
            default => '4ª revisão',
        };
    }
}
