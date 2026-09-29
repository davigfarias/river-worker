<?php

declare(strict_types=1);

namespace App\Enums;

enum SearchResultType: string
{
    case Documentacao = 'documentacao';
    case Demanda = 'demanda';
    case Principio = 'principio';
    case Conceito = 'conceito';
    case Estudo = 'estudo';
    case Referencia = 'referencia';
    case Citacao = 'citacao';
    case AnotacaoLeitura = 'anotacao_leitura';

    public function label(): string
    {
        return match ($this) {
            self::Documentacao => 'Documentações',
            self::Demanda => 'Demandas',
            self::Principio => 'Princípios',
            self::Conceito => 'Conceitos',
            self::Estudo => 'Estudos',
            self::Referencia => 'Recursos',
            self::Citacao => 'Citações',
            self::AnotacaoLeitura => 'Anotações de Leitura',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Documentacao => 'document-text',
            self::Demanda => 'squares-2x2',
            self::Principio => 'scale',
            self::Conceito => 'light-bulb',
            self::Estudo => 'code-bracket-square',
            self::Referencia => 'book-open',
            self::Citacao => 'chat-bubble-left-right',
            self::AnotacaoLeitura => 'pencil-square',
        };
    }
}
