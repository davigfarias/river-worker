<?php

declare(strict_types=1);

namespace App\Enums;

enum ReferencesIcon: string
{
    case BookOpen = 'book-open';
    case Newspaper = 'newspaper';
    case VideoCamera = 'video-camera';

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::BookOpen => 'Livro',
            self::Newspaper => 'Artigo',
            self::VideoCamera => 'Videoaula',
        };
    }

    /**
     * Valid Heroicon name for this type.
     */
    public function icon(): string
    {
        return $this->value;
    }

    public static function fromLabel(string $label): self
    {
        return match ($label) {
            'Livro' => self::BookOpen,
            'Artigo' => self::Newspaper,
            'Videoaula' => self::VideoCamera,
        };
    }
}
