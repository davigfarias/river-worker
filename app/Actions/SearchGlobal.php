<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTO\SearchResultDTO;
use App\Models\Citation;
use App\Models\CodeStudy;
use App\Models\Concept;
use App\Models\Principle;
use App\Models\ProjectDoc;
use App\Models\ReadingNote;
use App\Models\ReferenceMaterial;
use App\Models\WorkItem;
use App\Support\Outcome;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final readonly class SearchGlobal
{
    public function handle(string $term, int $accessTokenId, int $limitPerType = 5): Outcome
    {
        try {
            $term = trim($term);

            if ($term === '') {
                return Outcome::noViewMessage(data: collect());
            }

            $docs = ProjectDoc::search($term)->take($limitPerType)->get()
                ->map(fn (ProjectDoc $doc): SearchResultDTO => SearchResultDTO::fromDoc($doc));

            $items = WorkItem::search($term)->take($limitPerType)->get()
                ->map(fn (WorkItem $item): SearchResultDTO => SearchResultDTO::fromWorkItem($item));

            $principles = Principle::search($term)->take($limitPerType)->get()
                ->map(fn (Principle $principle): SearchResultDTO => SearchResultDTO::fromPrinciple($principle));

            $concepts = Concept::search($term)->take($limitPerType)->get()
                ->map(fn (Concept $concept): SearchResultDTO => SearchResultDTO::fromConcept($concept));

            $studies = CodeStudy::search($term)->take($limitPerType)->get()
                ->map(fn (CodeStudy $study): SearchResultDTO => SearchResultDTO::fromStudy($study));

            $references = ReferenceMaterial::search($term)
                ->where('access_token_id', $accessTokenId)
                ->take($limitPerType)
                ->get()
                ->map(fn (ReferenceMaterial $reference): SearchResultDTO => SearchResultDTO::fromReference($reference));

            $citations = Citation::search($term)
                ->where('access_token_id', $accessTokenId)
                ->take($limitPerType)
                ->get()
                ->load('referenceMaterial')
                ->map(fn (Citation $citation): SearchResultDTO => SearchResultDTO::fromCitation($citation));

            $readingNotes = ReadingNote::search($term)
                ->where('access_token_id', $accessTokenId)
                ->take($limitPerType)
                ->get()
                ->map(fn (ReadingNote $note): SearchResultDTO => SearchResultDTO::fromReadingNote($note));

            /** @var Collection<int, SearchResultDTO> $results */
            $results = $docs->concat($items)->concat($principles)->concat($concepts)->concat($studies)
                ->concat($references)->concat($citations)->concat($readingNotes);

            return Outcome::noViewMessage(data: $results);
        } catch (\Exception $e) {
            Log::error("Erro ao realizar busca global: {$e->getMessage()}");

            return Outcome::failure(message: 'Não foi possível realizar a busca.', data: collect());
        }
    }
}
