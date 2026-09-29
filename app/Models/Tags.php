<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Sushi\Sushi;

/**
 * @property-read int|string $id
 * @property-read string $title
 */
#[Table(name: 'tags')]
class Tags extends Model
{
    use Searchable, Sushi;

    /**
     * @var list<array{
     *     id: int,
     *     title: string,
     * }>
     */
    protected $rows = [
        [
            'id' => 1,
            'title' => 'Arquitetura',
        ],
        [
            'id' => 2,
            'title' => 'Clean Code',
        ],
        [
            'id' => 3,
            'title' => 'SOLID',
        ],
        [
            'id' => 4,
            'title' => 'Padrões de Projeto',
        ],
        [
            'id' => 5,
            'title' => 'Refatoração',
        ],
        [
            'id' => 6,
            'title' => 'Laravel',
        ],
        [
            'id' => 7,
            'title' => 'PHP',
        ],
        [
            'id' => 8,
            'title' => 'Livewire',
        ],
        [
            'id' => 9,
            'title' => 'Banco de Dados',
        ],
        [
            'id' => 10,
            'title' => 'Filas e Jobs',
        ],
        [
            'id' => 11,
            'title' => 'APIs',
        ],
        [
            'id' => 12,
            'title' => 'Testes',
        ],
        [
            'id' => 13,
            'title' => 'Performance',
        ],
        [
            'id' => 14,
            'title' => 'Segurança',
        ],
        [
            'id' => 15,
            'title' => 'DevOps',
        ],
        [
            'id' => 16,
            'title' => 'Frontend',
        ],
        [
            'id' => 17,
            'title' => 'DDD',
        ],
        [
            'id' => 18,
            'title' => 'IA e Agentes',
        ],
        [
            'id' => 19,
            'title' => 'Git',
        ],
        [
            'id' => 20,
            'title' => 'Algoritmos',
        ],
    ];

    /**
     * @return array{
     *     id: int|string,
     *     title: string
     * }
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
