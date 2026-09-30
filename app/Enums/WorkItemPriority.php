<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Faixa de prioridade de uma demanda. O valor numérico permite comparar faixas.
 */
enum WorkItemPriority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
    case Critical = 4;

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Baixa',
            self::Medium => 'Média',
            self::High => 'Alta',
            self::Critical => 'Crítica',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Low => 'zinc',
            self::Medium => 'amber',
            self::High => 'orange',
            self::Critical => 'red',
        };
    }
}
