<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Project;
use App\Support\Outcome;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final readonly class SaveProject
{
    /**
     * @param  array{name: string, repository_url?: string|null, description?: string|null}  $attributes
     */
    public function handle(?int $id, array $attributes): Outcome
    {
        try {
            if ($id === null) {
                $project = Project::create([...$attributes, 'slug' => $this->uniqueSlug($attributes['name'])]);
            } else {
                $project = tap(Project::findOrFail($id))->update($attributes);
            }

            return Outcome::success(message: $id === null ? 'Projeto criado.' : 'Projeto atualizado.', data: $project);
        } catch (\Throwable $e) {
            Log::error(self::class.': '.$e->getMessage());

            return Outcome::failure(message: 'Não foi possível salvar o projeto.');
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'projeto';
        $slug = $base;

        for ($suffix = 2; Project::where('slug', $slug)->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
