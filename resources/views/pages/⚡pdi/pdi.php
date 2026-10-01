<?php

use App\Actions\DeletePdiItem;
use App\Actions\SavePdiItem;
use App\Models\PdiItem;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('PDI')] class extends Component
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
     * @return Collection<int, PdiItem>
     */
    #[Computed]
    public function items(): Collection
    {
        return PdiItem::query()->latest()->get();
    }

    #[Computed]
    public function viewing(): ?PdiItem
    {
        return $this->viewingId ? PdiItem::find($this->viewingId) : null;
    }

    public function show(int $id): void
    {
        $this->viewingId = $id;
        unset($this->viewing);
        $this->modal('pdi-view')->show();
    }

    public function create(): void
    {
        $this->editingId = null;
        $this->form = $this->emptyForm();
        $this->resetValidation();
        $this->modal('pdi-form')->show();
    }

    public function edit(int $id): void
    {
        $this->editingId = $id;
        $this->form = PdiItem::findOrFail($id)->only(array_keys($this->emptyForm()));
        $this->resetValidation();
        $this->modal('pdi-view')->close();
        $this->modal('pdi-form')->show();
    }

    public function save(SavePdiItem $action): void
    {
        $rules = ['form.objective' => 'required|string|max:255', 'form.deadline' => 'nullable|string|max:100'];
        foreach (array_keys(PdiItem::QUESTIONS) as $field) {
            $rules["form.{$field}"] = 'nullable|string|max:5000';
        }

        $this->validate($rules, [], ['form.objective' => 'objetivo']);

        $outcome = $action->handle($this->editingId, $this->form);

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');

        if ($outcome->success) {
            $this->modal('pdi-form')->close();
            unset($this->items, $this->viewing);
        }
    }

    public function delete(int $id, DeletePdiItem $action): void
    {
        $outcome = $action->handle($id);

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');
        $this->modal('pdi-view')->close();
        unset($this->items, $this->viewing);
    }

    /**
     * @return array<string, null>
     */
    private function emptyForm(): array
    {
        return array_fill_keys(['objective', 'deadline', ...array_keys(PdiItem::QUESTIONS)], null);
    }
};
