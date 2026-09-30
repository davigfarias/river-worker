<?php

declare(strict_types=1);

namespace App\Enums;

enum WorkItemStatus: string
{
    case Backlog = 'backlog';
    case InDevAi = 'in_dev_ai';
    case DesignReview = 'design_review';
    case SpecFix = 'spec_fix';
    case Testing = 'testing';
    case ReadyDeploy = 'ready_deploy';
    case Deployed = 'deployed';

    public function label(): string
    {
        return match ($this) {
            self::Backlog => 'Backlog',
            self::InDevAi => 'Em desenvolvimento com IA',
            self::DesignReview => 'Teste de Design',
            self::SpecFix => 'Correção de especificação (opcional)',
            self::Testing => 'Testes finais',
            self::ReadyDeploy => 'Pronto pra Deploy',
            self::Deployed => 'Em produção',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Backlog => 'zinc',
            self::InDevAi => 'blue',
            self::DesignReview => 'amber',
            self::SpecFix => 'orange',
            self::Testing => 'purple',
            self::ReadyDeploy => 'cyan',
            self::Deployed => 'green',
        };
    }

    /**
     * Classe literal (para o scan do Tailwind) da barra de cor da etapa.
     */
    public function barClass(): string
    {
        return match ($this) {
            self::Backlog => 'bg-zinc-400',
            self::InDevAi => 'bg-blue-500',
            self::DesignReview => 'bg-amber-400',
            self::SpecFix => 'bg-orange-400',
            self::Testing => 'bg-purple-500',
            self::ReadyDeploy => 'bg-cyan-500',
            self::Deployed => 'bg-green-500',
        };
    }
}
