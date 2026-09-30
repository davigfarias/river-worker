<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Highlight\HighlightExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\NodeIterator;
use League\CommonMark\Node\StringContainerHelper;
use League\CommonMark\Normalizer\TextNormalizerInterface;

/**
 * Renderiza um documento Markdown e devolve, junto do HTML, o sumário (TOC)
 * montado a partir dos títulos h1–h3. Os títulos recebem o slug como `id`.
 */
final readonly class MarkdownDocument
{
    /**
     * @return array{html: string, headings: list<array{level: int, title: string, id: string}>}
     */
    public static function render(string $markdown): array
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'slug_normalizer' => [
                'instance' => new class implements TextNormalizerInterface
                {
                    public function normalize(string $text, array|\ArrayAccess|null $context = null): string
                    {
                        return Str::slug($text) ?: 'secao';
                    }
                },
                'unique' => 'document',
            ],
            'heading_permalink' => [
                'id_prefix' => '',
                'apply_id_to_heading' => true,
                'insert' => 'none',
                'min_heading_level' => 1,
                'max_heading_level' => 3,
            ],
        ]);

        $environment
            ->addExtension(new CommonMarkCoreExtension)
            ->addExtension(new FootnoteExtension)
            ->addExtension(new HighlightExtension)
            ->addExtension(new HeadingPermalinkExtension);

        $result = (new MarkdownConverter($environment))->convert($markdown);

        $headings = [];

        foreach ($result->getDocument()->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if ($node instanceof Heading && filled($id = $node->data->get('attributes/id', ''))) {
                $headings[] = [
                    'level' => $node->getLevel(),
                    'title' => StringContainerHelper::getChildText($node),
                    'id' => (string) $id,
                ];
            }
        }

        return ['html' => (string) $result, 'headings' => $headings];
    }
}
