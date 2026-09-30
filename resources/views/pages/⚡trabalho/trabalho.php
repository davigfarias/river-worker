<?php

use App\Actions\AddWorkFile;
use App\Actions\ChangeWorkItemStatus;
use App\Actions\DeleteWorkFile;
use App\Actions\DeleteWorkItem;
use App\Actions\RegisterDeploy;
use App\Actions\ScoreWorkItem;
use App\Actions\ToggleWorkFileReviewed;
use App\Actions\ToggleWorkStep;
use App\Actions\UpdateWorkItem;
use App\DTO\PriorityResultData;
use App\Enums\PriorityFactor;
use App\Enums\WorkItemKind;
use App\Enums\WorkItemStatus;
use App\Models\Project;
use App\Models\WorkItem;
use App\Support\Outcome;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $workItemId;

    public string $title = '';

    public string $kind = 'feat';

    public bool $editingTitle = false;

    public string $description = '';

    public bool $editingSpec = false;

    public ?string $editingBlock = null;

    public string $blockAnswer = '';

    /** @var array<string, mixed> */
    public array $assessment = [];

    public int $assessmentStep = 0;

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
            'kind' => $item->kind->value,
            'description' => (string) $item->description,
            'deployVersion' => $item->deploy_version,
            'releaseNotes' => $item->release_notes,
            'assessment' => $item->assessment ?? [],
        ]);
    }

    #[Computed]
    public function item(): WorkItem
    {
        return WorkItem::with(['project', 'steps', 'files'])->findOrFail($this->workItemId);
    }

    #[Computed]
    public function priority(): ?PriorityResultData
    {
        return app(ScoreWorkItem::class)->handle($this->item->kind, $this->item->assessment);
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
        unset($this->item, $this->availableSpecQuestions);

        if ($outcome->message) {
            Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');
        }

        return $outcome->success;
    }

    public function saveTitle(UpdateWorkItem $action): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'kind' => ['required', Rule::enum(WorkItemKind::class)],
        ]);

        if ($this->report($action->handle($this->workItemId, $validated))) {
            $this->editingTitle = false;
        }
    }

    public function cancelTitleEdit(): void
    {
        $this->reset('editingTitle');
        $this->title = $this->item->title;
        $this->kind = $this->item->kind->value;
        $this->resetValidation(['title', 'kind']);
    }

    public function saveSpec(UpdateWorkItem $action): void
    {
        $validated = $this->validate([
            'description' => 'nullable|string',
        ]);

        if ($this->report($action->handle($this->workItemId, $validated))) {
            $this->editingSpec = false;
        }
    }

    /**
     * Perguntas da natureza que ainda não viraram bloco.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function availableSpecQuestions(): array
    {
        $used = collect($this->item->specs)->pluck('key')->all();

        return collect($this->item->kind->specQuestions())->except($used)->all();
    }

    public function addSpecBlock(string $key, UpdateWorkItem $action): void
    {
        $question = $this->availableSpecQuestions[$key] ?? null;

        if ($question === null) {
            return;
        }

        $specs = [...($this->item->specs ?? []), ['key' => $key, 'question' => $question, 'answer' => '']];

        if ($this->report($action->handle($this->workItemId, ['specs' => $specs]))) {
            unset($this->availableSpecQuestions);
            $this->editSpecBlock($key);
            $this->modal('spec-questions')->close();
        }
    }

    public function editSpecBlock(string $key): void
    {
        $block = collect($this->item->specs)->firstWhere('key', $key);

        $this->editingBlock = $block ? $key : null;
        $this->blockAnswer = $block['answer'] ?? '';
    }

    public function saveSpecBlock(UpdateWorkItem $action): void
    {
        $this->validate(['blockAnswer' => 'nullable|string']);

        $specs = collect($this->item->specs)
            ->map(fn (array $b) => $b['key'] === $this->editingBlock ? [...$b, 'answer' => $this->blockAnswer] : $b)
            ->all();

        if ($this->report($action->handle($this->workItemId, ['specs' => $specs]))) {
            $this->reset('editingBlock', 'blockAnswer');
        }
    }

    public function removeSpecBlock(string $key, UpdateWorkItem $action): void
    {
        $specs = collect($this->item->specs)->reject(fn (array $b) => $b['key'] === $key)->values()->all();

        if ($this->report($action->handle($this->workItemId, ['specs' => $specs]))) {
            unset($this->availableSpecQuestions);
            $this->reset('editingBlock', 'blockAnswer');
        }
    }

    /**
     * Passos do questionário: os quatro fatores e as perguntas de sim/não da natureza.
     *
     * @return list<array{key: string, question: string, options: array<int|string, string>}>
     */
    #[Computed]
    public function assessmentSteps(): array
    {
        $factors = collect(PriorityFactor::cases())->map(fn (PriorityFactor $f) => [
            'key' => $f->value, 'question' => $f->question(), 'options' => $f->options(),
        ]);

        $questions = collect($this->item->kind->priorityQuestions())->map(fn (string $q, string $key) => [
            'key' => $key, 'question' => $q, 'options' => ['1' => 'Sim', '0' => 'Não'],
        ])->values();

        return $factors->concat($questions)->all();
    }

    public function startAssessment(): void
    {
        $this->assessment = $this->item->assessment ?? [];
        $this->assessmentStep = 0;
        $this->modal('assessment')->show();
    }

    public function answer(string $value, UpdateWorkItem $action): void
    {
        $step = $this->assessmentSteps[$this->assessmentStep] ?? null;

        if (! $step || ! array_key_exists($value, $step['options'])) {
            return;
        }

        $this->assessment[$step['key']] = PriorityFactor::tryFrom($step['key']) ? (int) $value : $value === '1';

        if ($this->assessmentStep < count($this->assessmentSteps) - 1) {
            $this->assessmentStep++;

            return;
        }

        if ($this->report($action->handle($this->workItemId, ['assessment' => $this->assessment]))) {
            unset($this->priority);
            $this->modal('assessment')->close();
        }
    }

    public function previousAssessmentStep(): void
    {
        $this->assessmentStep = max(0, $this->assessmentStep - 1);
    }

    public function clearAssessment(UpdateWorkItem $action): void
    {
        if ($this->report($action->handle($this->workItemId, ['assessment' => null]))) {
            unset($this->priority);
            $this->reset('assessment');
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
