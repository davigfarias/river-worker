<?php

use App\Actions\CreateWorkItem;
use App\Actions\GetDashboard;
use App\Enums\WorkItemKind;
use App\Models\CodeStudy;
use App\Models\Concept;
use App\Models\Principle;
use App\Models\Project;
use App\Models\ReferenceMaterial;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Foco')] class extends Component
{
    public string $title = '';

    public string $kind = 'feat';

    public ?int $project_id = null;

    public ?string $description = null;

    public function mount(): void
    {
        $this->project_id = $this->dashboard['project']?->id;
    }

    /**
     * @return array<string, mixed>
     */
    #[Computed]
    public function dashboard(): array
    {
        return app(GetDashboard::class)->handle()->data ?? [];
    }

    /**
     * Atalhos do dashboard: rota, ícone, rótulo e contagem de cada área.
     *
     * @return array<int, array{route: string, icon: string, label: string, description: string, count: int|null, unit: string, plural: string}>
     */
    #[Computed]
    public function shortcuts(): array
    {
        return [
            ['route' => 'projetos', 'icon' => 'folder', 'label' => 'Projetos', 'description' => 'Aplicações e documentação em Markdown', 'count' => Project::count(), 'unit' => 'projeto', 'plural' => 'projetos'],
            ['route' => 'principios', 'icon' => 'scale', 'label' => 'Princípios', 'description' => 'SOLID, padrões e arquitetura', 'count' => Principle::count(), 'unit' => 'princípio', 'plural' => 'princípios'],
            ['route' => 'estudos', 'icon' => 'code-bracket-square', 'label' => 'Estudos', 'description' => 'Código e anotações lado a lado', 'count' => CodeStudy::count(), 'unit' => 'estudo', 'plural' => 'estudos'],
            ['route' => 'conceitos', 'icon' => 'light-bulb', 'label' => 'Conceitos', 'description' => 'Glossário técnico', 'count' => Concept::count(), 'unit' => 'conceito', 'plural' => 'conceitos'],
            ['route' => 'referencias', 'icon' => 'book-open', 'label' => 'Recursos', 'description' => 'Livros, artigos e videoaulas', 'count' => ReferenceMaterial::count(), 'unit' => 'recurso', 'plural' => 'recursos'],
            ['route' => 'busca', 'icon' => 'magnifying-glass', 'label' => 'Busca', 'description' => 'Tudo em um lugar (Cmd/Ctrl + K)', 'count' => null, 'unit' => 'Cmd/Ctrl + K', 'plural' => ''],
        ];
    }

    /**
     * @return Collection<int, Project>
     */
    #[Computed]
    public function projects(): Collection
    {
        return Project::orderBy('name')->get();
    }

    public function createWorkItem(CreateWorkItem $action): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'kind' => ['required', Rule::enum(WorkItemKind::class)],
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
        ]);

        $outcome = $action->handle($validated);

        if (! $outcome->success) {
            Flux::toast(duration: 2500, heading: 'Ocorreu um erro', text: $outcome->message, variant: 'danger');

            return;
        }

        $this->redirectRoute('trabalho.show', $outcome->data->id, navigate: true);
    }
};
