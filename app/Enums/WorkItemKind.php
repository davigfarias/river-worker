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

    /**
     * Perguntas de sim/não que só fazem sentido para esta natureza na avaliação de prioridade.
     *
     * @return array<string, string>
     */
    public function priorityQuestions(): array
    {
        return match ($this) {
            self::Fix => [
                'in_production' => 'Está em produção?',
                'has_workaround' => 'Existe contorno (workaround)?',
            ],
            self::Feat => ['blocks_customer' => 'Algum cliente está bloqueado sem isso?'],
            self::Clean, self::Chore => [
                'has_dependents' => 'Algo depende disso?',
                'hinders_others' => 'Atrapalha outras demandas?',
            ],
            default => [],
        };
    }

    /**
     * Perguntas sugeridas para a especificação: cada uma vira um bloco a preencher.
     *
     * @return array<string, string>
     */
    public function specQuestions(): array
    {
        return match ($this) {
            self::Fix => [
                'reproduce' => 'Como reproduzir?',
                'reported' => 'O que o usuário relatou?',
                'hypotheses' => 'Quais são as hipóteses?',
                'tried' => 'O que já foi tentado (inclusive pela IA) e não funcionou?',
                'changed' => 'O que mudou desde que funcionava?',
            ],
            self::Feat => [
                'problem' => 'Que problema isso resolve e para quem?',
                'acceptance' => 'Qual o critério de aceite?',
                'out_of_scope' => 'O que fica fora do escopo?',
                'affects' => 'Afeta dados ou telas existentes?',
            ],
            self::Chore => [
                'why_now' => 'Por que agora?',
                'risk' => 'Qual o risco de quebrar algo?',
                'validate' => 'Como validar que nada mudou?',
            ],
            self::Clean => [
                'pain' => 'Qual a dor do código atual?',
                'identical' => 'O comportamento deve ficar idêntico?',
                'tests' => 'Que testes provam isso?',
            ],
            self::Refactor => [
                'pain' => 'Qual a dor da estrutura atual?',
                'tests' => 'Que testes garantem o mesmo comportamento?',
            ],
            self::Perf => [
                'metric' => 'Qual a métrica atual e qual a meta?',
                'measure' => 'Como medir antes e depois?',
            ],
            self::Test => [
                'coverage' => 'Que comportamento fica sem cobertura hoje?',
                'cases' => 'Quais casos precisam ser cobertos?',
            ],
            self::Docs => [
                'audience' => 'Quem vai ler e o que precisa entender?',
                'outdated' => 'O que está desatualizado ou faltando?',
            ],
            self::Ci => [
                'pipeline' => 'Que etapa do pipeline muda e por quê?',
                'rollback' => 'Como desfazer se quebrar o deploy?',
            ],
            self::Revert => [
                'commit' => 'Qual commit reverter?',
                'why' => 'Por que reverter?',
            ],
            self::Style => ['where' => 'Quais telas ou componentes mudam?'],
            self::Build => ['why' => 'O que muda nas dependências ou no build, e por quê?'],
        };
    }
}
