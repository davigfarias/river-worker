<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Project;
use App\Support\Outcome;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final readonly class ListRepositoryCommits
{
    public const int PER_PAGE = 30;

    public const int MAX_PAGES = 10;

    /**
     * Carrega as primeiras $pages páginas de commits (mais recentes primeiro).
     *
     * Data: array{commits: list<array{sha: string, message: string, author: ?string, committed_at: ?string, url: string}>, hasMore: bool}
     */
    public function handle(Project $project, int $pages = 1): Outcome
    {
        $repository = $project->githubRepository();

        if ($repository === null) {
            return Outcome::failure(message: 'O projeto não tem um repositório do GitHub.');
        }

        try {
            $commits = [];
            $pages = max(1, min($pages, self::MAX_PAGES));

            foreach (range(1, $pages) as $page) {
                $pageCommits = $this->page($repository, $page);
                $commits = [...$commits, ...$pageCommits];

                if (count($pageCommits) < self::PER_PAGE) {
                    return Outcome::success(data: ['commits' => $commits, 'hasMore' => false]);
                }
            }

            return Outcome::success(data: ['commits' => $commits, 'hasMore' => $pages < self::MAX_PAGES]);
        } catch (RequestException $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: match ($e->response->status()) {
                404 => 'Repositório não encontrado. Se for privado, configure o GITHUB_TOKEN.',
                403, 429 => 'Limite de requisições do GitHub atingido. Tente mais tarde ou configure o GITHUB_TOKEN.',
                409 => 'O repositório ainda não tem commits.',
                default => 'Não foi possível carregar os commits.',
            });
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível carregar os commits.');
        }
    }

    /**
     * @return list<array{sha: string, message: string, author: ?string, committed_at: ?string, url: string}>
     */
    private function page(string $repository, int $page): array
    {
        // ponytail: store file fixo porque o CACHE_STORE do app é array (não sobrevive entre requisições).
        return Cache::store('file')->remember("github-commits:{$repository}:{$page}", now()->addMinutes(5), fn (): array => Http::acceptJson()
            ->when(filled(config('services.github.token')), fn ($request) => $request->withToken(config('services.github.token')))
            ->get("https://api.github.com/repos/{$repository}/commits", ['per_page' => self::PER_PAGE, 'page' => $page])
            ->throw()
            ->collect()
            ->map(fn (array $commit): array => [
                'sha' => $commit['sha'],
                'message' => strtok($commit['commit']['message'], "\n"),
                'author' => $commit['author']['login'] ?? $commit['commit']['author']['name'] ?? null,
                'committed_at' => $commit['commit']['author']['date'] ?? null,
                'url' => $commit['html_url'],
            ])
            ->all());
    }
}
