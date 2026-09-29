<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\SearchResultType;
use App\Models\Citation;
use App\Models\CodeStudy;
use App\Models\Concept;
use App\Models\Principle;
use App\Models\ProjectDoc;
use App\Models\ReadingNote;
use App\Models\ReferenceMaterial;
use App\Models\WorkItem;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Livewire\Wireable;

/**
 * @phpstan-type SearchResultArray array{
 *     type: string,
 *     id: int,
 *     title: string,
 *     snippet: string|null,
 *     url: string
 * }
 */
class SearchResultDTO implements Arrayable, Wireable
{
    public function __construct(
        public ?SearchResultType $type = null,
        public ?int $id = null,
        public ?string $title = null,
        public ?string $snippet = null,
        public ?string $url = null,
    ) {}

    /**
     * @return SearchResultArray
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type?->value,
            'id' => $this->id,
            'title' => $this->title,
            'snippet' => $this->snippet,
            'url' => $this->url,
        ];
    }

    /**
     * @return SearchResultArray
     */
    public function toLivewire(): array
    {
        return $this->toArray();
    }

    /**
     * @param  SearchResultArray  $value
     */
    public static function fromLivewire($value): self
    {
        return new self(
            type: isset($value['type']) ? SearchResultType::from($value['type']) : null,
            id: isset($value['id']) ? (int) $value['id'] : null,
            title: $value['title'] ?? null,
            snippet: $value['snippet'] ?? null,
            url: $value['url'] ?? null,
        );
    }

    public static function fromDoc(ProjectDoc $doc): self
    {
        return new self(
            type: SearchResultType::Documentacao,
            id: $doc->id,
            title: $doc->title,
            snippet: $doc->content ? Str::limit(strip_tags($doc->content), 140) : $doc->category,
            url: route('projetos.docs.show', ['project' => $doc->project_id, 'doc' => $doc->id]),
        );
    }

    public static function fromWorkItem(WorkItem $item): self
    {
        return new self(
            type: SearchResultType::Demanda,
            id: $item->id,
            title: $item->title,
            snippet: $item->description ? Str::limit($item->description, 140) : null,
            url: route('trabalho.show', $item->id),
        );
    }

    public static function fromPrinciple(Principle $principle): self
    {
        return new self(
            type: SearchResultType::Principio,
            id: $principle->id,
            title: $principle->acronym ? "{$principle->acronym} — {$principle->name}" : $principle->name,
            snippet: Str::limit($principle->summary, 140),
            url: route('principios', ['busca' => $principle->name]),
        );
    }

    public static function fromConcept(Concept $concept): self
    {
        return new self(
            type: SearchResultType::Conceito,
            id: $concept->id,
            title: $concept->title,
            snippet: Str::limit($concept->definition, 140),
            url: route('conceitos', ['busca' => $concept->title]),
        );
    }

    public static function fromStudy(CodeStudy $study): self
    {
        return new self(
            type: SearchResultType::Estudo,
            id: $study->id,
            title: $study->title,
            snippet: $study->topic,
            url: route('estudos.show', $study->id),
        );
    }

    public static function fromReference(ReferenceMaterial $reference): self
    {
        return new self(
            type: SearchResultType::Referencia,
            id: $reference->id,
            title: $reference->title,
            snippet: $reference->author,
            url: route('referencias.show', $reference->id),
        );
    }

    public static function fromCitation(Citation $citation): self
    {
        return new self(
            type: SearchResultType::Citacao,
            id: $citation->id,
            title: $citation->referenceMaterial->title,
            snippet: Str::limit($citation->quote_text, 140),
            url: route('referencias.show', $citation->reference_material_id),
        );
    }

    public static function fromReadingNote(ReadingNote $note): self
    {
        return new self(
            type: SearchResultType::AnotacaoLeitura,
            id: $note->id,
            title: $note->displayTitle(),
            snippet: Str::limit($note->body, 140),
            url: route('referencias.show', $note->reference_material_id),
        );
    }
}
