<?php

use App\Support\MarkdownDocument;

test('headings become ids and a table of contents', function () {
    $result = MarkdownDocument::render("# Visão geral\n\ntexto\n\n## Filas e Jobs\n\n#### Fora do TOC\n");

    expect($result['headings'])->toBe([
        ['level' => 1, 'title' => 'Visão geral', 'id' => 'visao-geral'],
        ['level' => 2, 'title' => 'Filas e Jobs', 'id' => 'filas-e-jobs'],
    ])->and($result['html'])->toContain('<h2 id="filas-e-jobs">');
});

test('headings inside code fences are not part of the table of contents', function () {
    $result = MarkdownDocument::render("# Real\n\n```md\n# Falso\n```\n");

    expect(array_column($result['headings'], 'title'))->toBe(['Real']);
});

test('raw html in the document is stripped', function () {
    expect(MarkdownDocument::render('<script>alert(1)</script>ok')['html'])->not->toContain('<script>');
});

test('repeated headings get unique ids', function () {
    $ids = array_column(MarkdownDocument::render("## Uso\n\n## Uso\n")['headings'], 'id');

    expect($ids)->toBe(['uso', 'uso-1']);
});
