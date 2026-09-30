<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Termos da conta de prioridade: (impacto + urgência + risco) ÷ esforço.
 * Cada um é uma pergunta de múltipla escolha com valores de 1 a 4.
 */
enum PriorityFactor: string
{
    case Impact = 'impact';
    case Urgency = 'urgency';
    case Risk = 'risk';
    case Effort = 'effort';

    public function question(): string
    {
        return match ($this) {
            self::Impact => 'Quantas pessoas ou processos são afetados?',
            self::Urgency => 'Existe prazo?',
            self::Risk => 'O que piora se adiar?',
            self::Effort => 'Quanto trabalho dá?',
        };
    }

    /**
     * @return array<int, string>
     */
    public function options(): array
    {
        return match ($this) {
            self::Impact => [1 => 'Só eu', 2 => 'Um cliente', 3 => 'Vários clientes', 4 => 'Todos'],
            self::Urgency => [1 => 'Nenhum', 2 => 'Este mês', 3 => 'Esta semana', 4 => 'Hoje'],
            self::Risk => [1 => 'Nada', 2 => 'Incômodo', 3 => 'Perda de dados ou dinheiro', 4 => 'Sistema parado'],
            self::Effort => [1 => 'Minutos', 2 => 'Horas', 3 => 'Um dia', 4 => 'Vários dias'],
        };
    }

    /**
     * Rótulo curto usado na explicação do resultado.
     */
    public function reasonLabel(): string
    {
        return match ($this) {
            self::Impact => 'impacto',
            self::Urgency => 'prazo',
            self::Risk => 'risco',
            self::Effort => 'esforço',
        };
    }
}
