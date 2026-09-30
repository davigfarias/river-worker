<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Natureza da demanda, espelhando os tipos de commit semântico.
 */
enum WorkItemKind: string
{
    case Feat = 'feat';
    case Fix = 'fix';
    case Refactor = 'refactor';
    case Clean = 'clean';
    case Perf = 'perf';
    case Style = 'style';
    case Docs = 'docs';
    case Test = 'test';
    case Build = 'build';
    case Ci = 'ci';
    case Chore = 'chore';
    case Revert = 'revert';

    public function label(): string
    {
        return match ($this) {
            self::Feat => 'Nova funcionalidade',
            self::Fix => 'Correção de bug',
            self::Refactor => 'Refatoração sem mudar comportamento',
            self::Clean => 'Limpeza de código morto ou sobra',
            self::Perf => 'Melhoria de desempenho',
            self::Style => 'Visual, formatação ou layout',
            self::Docs => 'Documentação',
            self::Test => 'Testes',
            self::Build => 'Build e dependências',
            self::Ci => 'Integração e deploy contínuos',
            self::Chore => 'Manutenção e tarefas gerais',
            self::Revert => 'Reversão de commit anterior',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Feat => 'green',
            self::Fix => 'red',
            self::Refactor => 'blue',
            self::Clean => 'teal',
            self::Perf => 'amber',
            self::Style => 'pink',
            self::Docs => 'sky',
            self::Test => 'purple',
            self::Build => 'orange',
            self::Ci => 'indigo',
            self::Chore => 'zinc',
            self::Revert => 'rose',
        };
    }

    /**
     * Classe literal (para o scan do Tailwind) da cor do marcador.
     */
    public function dotClass(): string
    {
        return match ($this) {
            self::Feat => 'bg-green-500',
            self::Fix => 'bg-red-500',
            self::Refactor => 'bg-blue-500',
            self::Clean => 'bg-teal-500',
            self::Perf => 'bg-amber-500',
            self::Style => 'bg-pink-500',
            self::Docs => 'bg-sky-500',
            self::Test => 'bg-purple-500',
            self::Build => 'bg-orange-500',
            self::Ci => 'bg-indigo-500',
            self::Chore => 'bg-zinc-400',
            self::Revert => 'bg-rose-400',
        };
    }
}
