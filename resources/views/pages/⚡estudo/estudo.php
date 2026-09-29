<?php

use App\Actions\DeleteCodeStudy;
use App\Actions\DeleteCodeStudyStep;
use App\Actions\ReorderCodeStudyStep;
use App\Actions\SaveCodeStudy;
use App\Actions\SaveCodeStudyStep;
use App\Models\CodeStudy;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $studyId;

    public string $title = '';

    public ?string $topic = null;

    public string $summary = '';

    public bool $editingSummary = false;

    #[Locked]
    public ?int $editingStepId = null;

    public bool $stepFormOpen = false;

    public string $stepTitle = '';

    public string $stepLanguage = 'php';

    public string $stepSnippet = '';

    public string $stepMarkdown = '';

    public function mount(int $id): void
    {
        $study = CodeStudy::findOrFail($id);

        $this->studyId = $study->id;
        $this->fill(['title' => $study->title, 'topic' => $study->topic, 'summary' => (string) $study->description]);
    }

    #[Computed]
    public function study(): CodeStudy
    {
        return CodeStudy::with(['steps', 'principle'])->findOrFail($this->studyId);
    }

    public function saveSummary(SaveCodeStudy $action): void
    {
        $validated = $this->validate(['title' => 'required|string|max:255', 'topic' => 'nullable|string|max:255', 'summary' => 'nullable|string']);

        $outcome = $action->handle($this->studyId, ['title' => $validated['title'], 'topic' => $validated['topic'], 'description' => $validated['summary']]);

        if ($outcome->success) {
            $this->editingSummary = false;
            unset($this->study);
        }

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');
    }

    public function newStep(): void
    {
        $this->reset('editingStepId', 'stepTitle', 'stepSnippet', 'stepMarkdown');
        $this->stepLanguage = 'php';
        $this->stepFormOpen = true;
    }

    public function editStep(int $id): void
    {
        $step = $this->study->steps->firstWhere('id', $id);

        abort_if($step === null, 404);

        $this->editingStepId = $step->id;
        $this->fill([
            'stepTitle' => $step->title,
            'stepLanguage' => $step->language,
            'stepSnippet' => (string) $step->snippet,
            'stepMarkdown' => (string) $step->markdown,
        ]);
        $this->stepFormOpen = true;
    }

    public function saveStep(SaveCodeStudyStep $action): void
    {
        $validated = $this->validate([
            'stepTitle' => 'required|string|max:255',
            'stepLanguage' => 'required|string|max:30',
            'stepSnippet' => 'nullable|string',
            'stepMarkdown' => 'nullable|string',
        ]);

        $outcome = $action->handle($this->studyId, $this->editingStepId, [
            'title' => $validated['stepTitle'],
            'language' => $validated['stepLanguage'],
            'snippet' => $validated['stepSnippet'],
            'markdown' => $validated['stepMarkdown'],
        ]);

        if ($outcome->success) {
            $this->stepFormOpen = false;
            unset($this->study);
        }

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');
    }

    public function removeStep(int $id, DeleteCodeStudyStep $action): void
    {
        abort_if($this->study->steps->firstWhere('id', $id) === null, 404);

        $action->handle($id);
        unset($this->study);
    }

    public function sortStep(int|string $item, int $position, ReorderCodeStudyStep $action): void
    {
        $action->handle($this->studyId, (int) $item, $position);
        unset($this->study);
    }

    public function delete(DeleteCodeStudy $action): void
    {
        $outcome = $action->handle($this->studyId);

        if (! $outcome->success) {
            Flux::toast(duration: 2500, text: $outcome->message, variant: 'danger');

            return;
        }

        $this->redirectRoute('estudos', navigate: true);
    }
};
