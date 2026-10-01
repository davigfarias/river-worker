<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BragEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(BragEntryFactory::class)]
#[Fillable([
    'goal',
    'deadline',
    'project',
    'contribution',
    'stakeholders',
    'impact_changed',
    'impact_easier',
    'impact_faster',
    'impact_clearer',
    'impact_problem_gone',
    'trainings',
    'feedbacks',
])]
#[Table(name: 'brag_entries')]
class BragEntry extends Model
{
    /** @use HasFactory<BragEntryFactory> */
    use HasFactory;

    /**
     * Perguntas de resposta longa, na ordem em que aparecem.
     *
     * @var array<string, string>
     */
    public const array QUESTIONS = [
        'contribution' => 'Contribuição',
        'stakeholders' => 'Stakeholders',
        'trainings' => 'Treinamentos',
        'feedbacks' => 'Feedbacks',
    ];

    /**
     * Perguntas do impacto na empresa.
     *
     * @var array<string, string>
     */
    public const array IMPACT_QUESTIONS = [
        'impact_changed' => 'O que mudou depois que eu fiz isso?',
        'impact_easier' => 'O que ficou mais fácil?',
        'impact_faster' => 'O que ficou mais rápido?',
        'impact_clearer' => 'O que ficou mais claro?',
        'impact_problem_gone' => 'Qual problema deixou de existir?',
    ];
}
