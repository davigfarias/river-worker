<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Project;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final readonly class SaveProjectDoc
{
    /**
     * @param  array{title: string, category: string, content?: string|null}  $attributes
     */
    public function handle(int $projectId, ?int $id, array $attributes): Outcome
    {
        try {
            $project = Project::findOrFail($projectId);

            if ($id === null) {
                $doc = $project->docs()->create([
                    ...$attributes,
                    'slug' => $this->uniqueSlug($project, $attributes['title']),
                    'order' => $project->docs()->count(),
                ]);
            } else {
                $doc = tap($project->docs()->findOrFail($id))->update($attributes);
            }

            return Outcome::success(message: $id === null ? 'Documento criado.' : 'Documento atualizado.', data: $doc);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível salvar o documento.');
        }
    }

    private function uniqueSlug(Project $project, string $title): string
    {
        $base = Str::slug($title) ?: 'documento';
        $slug = $base;

        for ($suffix = 2; $project->docs()->where('slug', $slug)->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
