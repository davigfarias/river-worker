<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PdiItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(PdiItemFactory::class)]
#[Fillable([
    'objective',
    'current_situation',
    'action',
    'measurement',
    'deadline',
])]
#[Table(name: 'pdi_items')]
class PdiItem extends Model
{
    /** @use HasFactory<PdiItemFactory> */
    use HasFactory;

    /**
     * Perguntas de resposta longa, na ordem em que aparecem.
     *
     * @var array<string, string>
     */
    public const array QUESTIONS = [
        'current_situation' => 'Situação atual',
        'action' => 'Ação',
        'measurement' => 'Como vou medir?',
    ];
}
