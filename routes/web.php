<?php

use App\Http\Controllers\DownloadExportController;
use App\Http\Middleware\EnsureAccessTokenIsValid;
use Illuminate\Support\Facades\Route;

Route::middleware(EnsureAccessTokenIsValid::class)->group(function () {
    Route::livewire('/', 'pages::dashboard')->name('dashboard');
    Route::livewire('/busca', 'pages::busca')->name('busca');

    Route::livewire('/trabalho/{id}', 'pages::trabalho')->whereNumber('id')->name('trabalho.show');

    Route::livewire('/projetos', 'pages::projetos')->name('projetos');
    Route::livewire('/projetos/{project}/docs/{doc}', 'pages::doc')->whereNumber(['project', 'doc'])->name('projetos.docs.show');
    Route::livewire('/projetos/{slug}', 'pages::projeto')->name('projetos.show');

    Route::livewire('/operacional/brag-document', 'pages::brag-document')->name('operacional.brag-document');
    Route::livewire('/operacional/pdi', 'pages::pdi')->name('operacional.pdi');

    Route::livewire('/principios', 'pages::principios')->name('principios');
    Route::livewire('/conceitos', 'pages::conceitos')->name('conceitos');
    Route::livewire('/estudos', 'pages::estudos')->name('estudos');
    Route::livewire('/estudos/{id}', 'pages::estudo')->whereNumber('id')->name('estudos.show');

    Route::get('/referencias/exportacoes/{export}/download', DownloadExportController::class)->name('referencias.exportacoes.download');
    Route::livewire('/referencias', 'pages::referencias')->name('referencias');
    Route::livewire('/referencias/busca', 'pages::buscar-referencias')->name('referencias.busca');
    Route::livewire('/referencias/exportacoes', 'pages::exportacoes')->name('referencias.exportacoes');
    Route::livewire('/referencias/{id}/capitulos/{chapterId}/estudar', 'pages::estudar')
        ->whereNumber('id')
        ->whereNumber('chapterId')
        ->name('referencias.study');
    Route::livewire('/referencias/{id}/capitulos/{chapterId}/revisao', 'pages::revisao')
        ->whereNumber('id')
        ->whereNumber('chapterId')
        ->name('referencias.study.review');
    Route::livewire('/referencias/{id}/capitulos/{chapterId}/resultados', 'pages::resultados')
        ->whereNumber('id')
        ->whereNumber('chapterId')
        ->name('referencias.study.results');
    Route::livewire('/referencias/{id}', 'pages::referencia')->whereNumber('id')->name('referencias.show');
});

Route::livewire('/entrar', 'pages::entrar')->name('entrar');
