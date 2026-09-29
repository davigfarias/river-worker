<?php

use App\Actions\AddWorkFile;
use App\Actions\ChangeWorkItemStatus;
use App\Actions\DeleteWorkFile;
use App\Actions\DeleteWorkItem;
use App\Actions\RegisterDeploy;
use App\Actions\ToggleWorkFileReviewed;
use App\Actions\ToggleWorkStep;
use App\Actions\UpdateWorkItem;
use App\Enums\WorkItemStatus;
use App\Models\Project;
use App\Models\WorkItem;
use App\Support\Outcome;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $workItemId;

    public string $title = '';

    public ?int $project_id = null;

    public string $description = '';

    public bool $editingSpec = false;

    public string $filePath = '';

    public ?string $reasonNotes = null;

    public ?string $deployVersion = null;

    public ?string $releaseNotes = null;

    public function mount(int $id): void
    {
        $item = WorkItem::findOrFail($id);

        $this->workItemId = $item->id;
        $this->fill([
            'title' => $item->title,
            'project_id' => $item->project_id,
            'description' => (string) $item->description,
            'deployVersion' => $item->deploy_version,
            'releaseNotes' => $item->release_notes,
        ]);
    }

    #[Computed]
    public function item(): WorkItem
    {
        return WorkItem::with(['project', 'steps', 'files'])->findOrFail($this->workItemId);
    }

    /**
     * @return Collection<int, Project>
     */
    #[Computed]
    public function projects(): Collection
    {
        return Project::orderBy('name')->get();
    }

    private function report(Outcome $outcome): bool
    {
        unset($this->item);

        if ($outcome->message) {
            Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');
        }

        return $outcome->success;
    }

    public function saveSpec(UpdateWorkItem $action): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
        ]);

        if ($this->report($action->handle($this->workItemId, $validated))) {
            $this->editingSpec = false;
        }
    }

    /**
     * Etapas do ciclo de vida clicáveis na timeline. "Em produção" só entra
     * pelo botão de deploy, que carimba deployed_at com versão e notas.
     */
    public function changeStatus(string $status, ChangeWorkItemStatus $action): void
    {
        $target = WorkItemStatus::from($status);

        if ($target === WorkItemStatus::Deployed) {
            return;
        }

        $this->report($action->handle($this->workItemId, $target));
    }

    public function toggleStep(int $id, ToggleWorkStep $action): void
    {
        $this->item->steps->firstWhere('id', $id) && $this->report($action->handle($id));
    }

    public function addFile(AddWorkFile $action): void
    {
        $validated = $this->validate(['filePath' => 'required|string|max:500', 'reasonNotes' => 'nullable|string|max:2000']);

        if ($this->report($action->handle($this->workItemId, $validated['filePath'], $validated['reasonNotes']))) {
            $this->reset('filePath', 'reasonNotes');
        }
    }

    public function toggleFile(int $id, ToggleWorkFileReviewed $action): void
    {
        $this->item->files->firstWhere('id', $id) && $this->report($action->handle($id));
    }

    public function removeFile(int $id, DeleteWorkFile $action): void
    {
        $this->item->files->firstWhere('id', $id) && $this->report($action->handle($id));
    }

    public function registerDeploy(RegisterDeploy $action): void
    {
        $validated = $this->validate(['deployVersion' => 'nullable|string|max:100', 'releaseNotes' => 'nullable|string']);

        if ($this->report($action->handle($this->workItemId, $validated['deployVersion'], $validated['releaseNotes']))) {
            $this->modal('confirm-deploy')->close();
        }
    }

    public function delete(DeleteWorkItem $action): void
    {
        $outcome = $action->handle($this->workItemId);

        if (! $outcome->success) {
            Flux::toast(duration: 2500, text: $outcome->message, variant: 'danger');

            return;
        }

        $this->redirectRoute('dashboard', navigate: true);
    }
};
