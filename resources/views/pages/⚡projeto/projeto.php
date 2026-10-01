<?php

use App\Actions\CreateWorkItem;
use App\Actions\DeleteProject;
use App\Actions\DeleteWorkItem;
use App\Actions\ListRepositoryCommits;
use App\Actions\SaveProject;
use App\Actions\SaveProjectDoc;
use App\Enums\WorkItemKind;
use App\Enums\WorkItemStatus;
use App\Models\Project;
use App\Models\ProjectDoc;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $projectId;

    public string $docFilter = '';

    public string $workItemTitle = '';

    public string $workItemKind = 'feat';

    #[Url(as: 'demandas')]
    public string $workItemTab = 'ativas';

    public ?int $workItemToDeleteId = null;

    public string $docTitle = '';

    public string $docCategory = '';

    public string $name = '';

    public ?string $repository_url = null;

    public ?string $description = null;

    public function mount(string $slug): void
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        $this->projectId = $project->id;
        $this->fill([
            'name' => $project->name,
            'repository_url' => $project->repository_url,
            'description' => $project->description,
        ]);
    }

    #[Computed]
    public function project(): Project
    {
        return Project::with(['docs', 'workItems' => fn ($query) => $query->latest('updated_at')])->findOrFail($this->projectId);
    }

    /**
     * @return Collection<string, Collection<int, ProjectDoc>>
     */
    #[Computed]
    public function docsByCategory(): Collection
    {
        $term = mb_strtolower(trim($this->docFilter));

        return $this->project->docs
            ->when($term !== '', fn ($docs) => $docs->filter(
                fn ($doc): bool => str_contains(mb_strtolower($doc->title.' '.$doc->category), $term)
            ))
            ->groupBy('category')
            ->sortKeys();
    }

    public int $commitPages = 1;

    /**
     * @return array{commits: list<array{sha: string, message: string, author: ?string, committed_at: ?string, url: string}>, error: ?string, hasMore: bool}
     */
    #[Computed]
    public function commits(): array
    {
        $action = app(ListRepositoryCommits::class);
        $commits = [];

        foreach (range(1, $this->commitPages) as $page) {
            $outcome = $action->handle($this->project, $page);

            if (! $outcome->success) {
                return ['commits' => $commits, 'error' => $outcome->message, 'hasMore' => false];
            }

            $commits = [...$commits, ...$outcome->data];
        }

        return ['commits' => $commits, 'error' => null, 'hasMore' => count($outcome->data) === ListRepositoryCommits::PER_PAGE];
    }

    public function loadMoreCommits(): void
    {
        $this->commitPages = min($this->commitPages + 1, 10);
    }

    public function createWorkItem(CreateWorkItem $action): void
    {
        $validated = $this->validate([
            'workItemTitle' => 'required|string|max:255',
            'workItemKind' => ['required', Rule::enum(WorkItemKind::class)],
        ]);

        $outcome = $action->handle(['title' => $validated['workItemTitle'], 'kind' => $validated['workItemKind'], 'project_id' => $this->projectId]);

        if (! $outcome->success) {
            Flux::toast(duration: 2500, heading: 'Ocorreu um erro', text: $outcome->message, variant: 'danger');

            return;
        }

        $this->redirectRoute('trabalho.show', $outcome->data->id, navigate: true);
    }

    /**
     * @return Collection<int, \App\Models\WorkItem>
     */
    #[Computed]
    public function activeWorkItems(): Collection
    {
        return $this->project->workItems->where('status', '!==', WorkItemStatus::Deployed)->values();
    }

    /**
     * @return Collection<int, \App\Models\WorkItem>
     */
    #[Computed]
    public function deliveredWorkItems(): Collection
    {
        return $this->project->workItems->where('status', WorkItemStatus::Deployed)->values();
    }

    public function confirmDeleteWorkItem(int $id): void
    {
        abort_unless($this->project->workItems->contains('id', $id), 404);

        $this->workItemToDeleteId = $id;
        $this->modal('delete-work-item')->show();
    }

    public function deleteWorkItem(DeleteWorkItem $action): void
    {
        abort_unless($this->project->workItems->contains('id', $this->workItemToDeleteId), 404);

        $outcome = $action->handle($this->workItemToDeleteId);

        $this->reset('workItemToDeleteId');
        $this->modal('delete-work-item')->close();
        unset($this->project);

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');
    }

    public function createDoc(SaveProjectDoc $action): void
    {
        $validated = $this->validate([
            'docTitle' => 'required|string|max:255',
            'docCategory' => 'required|string|max:255',
        ]);

        $outcome = $action->handle($this->projectId, null, ['title' => $validated['docTitle'], 'category' => $validated['docCategory']]);

        if (! $outcome->success) {
            Flux::toast(duration: 2500, heading: 'Ocorreu um erro', text: $outcome->message, variant: 'danger');

            return;
        }

        $this->redirectRoute('projetos.docs.show', ['project' => $this->projectId, 'doc' => $outcome->data->id, 'editar' => 1], navigate: true);
    }

    public function updateProject(SaveProject $action): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'repository_url' => 'nullable|url|max:2048',
            'description' => 'nullable|string|max:5000',
        ]);

        $outcome = $action->handle($this->projectId, $validated);

        $this->modal('edit-project')->close();
        unset($this->project, $this->commits);
        $this->reset('commitPages');

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');
    }

    public function deleteProject(DeleteProject $action): void
    {
        $outcome = $action->handle($this->projectId);

        if (! $outcome->success) {
            Flux::toast(duration: 2500, text: $outcome->message, variant: 'danger');

            return;
        }

        $this->redirectRoute('projetos', navigate: true);
    }
};
