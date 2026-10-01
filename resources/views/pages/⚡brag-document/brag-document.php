<?php

use App\Actions\DeleteBragEntry;
use App\Actions\SaveBragEntry;
use App\Models\BragEntry;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Brag document')] class extends Component
{
    #[Locked]
    public ?int $editingId = null;

    #[Locked]
    public ?int $viewingId = null;

    /** @var array<string, string|null> */
    public array $form = [];

    public function mount(): void
    {
        $this->form = $this->emptyForm();
    }

    /**
     * @return Collection<int, BragEntry>
     */
    #[Computed]
    public function entries(): Collection
    {
        return BragEntry::query()->latest()->get();
    }

    #[Computed]
    public function viewing(): ?BragEntry
    {
        return $this->viewingId ? BragEntry::find($this->viewingId) : null;
    }

    public function show(int $id): void
    {
        $this->viewingId = $id;
        unset($this->viewing);
        $this->modal('brag-view')->show();
    }

    public function create(): void
    {
        $this->editingId = null;
        $this->form = $this->emptyForm();
        $this->resetValidation();
        $this->modal('brag-form')->show();
    }

    public function edit(int $id): void
    {
        $this->editingId = $id;
        $this->form = BragEntry::findOrFail($id)->only(array_keys($this->emptyForm()));
        $this->resetValidation();
        $this->modal('brag-view')->close();
        $this->modal('brag-form')->show();
    }

    public function save(SaveBragEntry $action): void
    {
        $rules = ['form.goal' => 'required|string|max:255', 'form.deadline' => 'nullable|string|max:100', 'form.project' => 'nullable|string|max:255'];
        foreach ([...array_keys(BragEntry::QUESTIONS), ...array_keys(BragEntry::IMPACT_QUESTIONS)] as $field) {
            $rules["form.{$field}"] = 'nullable|string|max:5000';
        }

        $this->validate($rules, [], ['form.goal' => 'meta']);

        $outcome = $action->handle($this->editingId, $this->form);

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');

        if ($outcome->success) {
            $this->modal('brag-form')->close();
            unset($this->entries, $this->viewing);
        }
    }

    public function delete(int $id, DeleteBragEntry $action): void
    {
        $outcome = $action->handle($id);

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');
        $this->modal('brag-view')->close();
        unset($this->entries, $this->viewing);
    }

    /**
     * @return array<string, null>
     */
    private function emptyForm(): array
    {
        return array_fill_keys([
            'goal', 'deadline', 'project',
            ...array_keys(BragEntry::QUESTIONS),
            ...array_keys(BragEntry::IMPACT_QUESTIONS),
        ], null);
    }
};
