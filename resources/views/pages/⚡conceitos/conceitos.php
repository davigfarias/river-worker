<?php

use App\Actions\DeleteConcept;
use App\Actions\SaveConcept;
use App\Models\Concept;
use App\Models\Principle;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Conceitos')] class extends Component
{
    #[Url(as: 'busca')]
    public ?string $filter = null;

    #[Locked]
    public ?int $editingId = null;

    public string $title = '';

    public string $definition = '';

    public ?string $code_snippet = null;

    public ?int $principle_id = null;

    /**
     * @return Collection<int, Concept>
     */
    #[Computed]
    public function concepts(): Collection
    {
        return Concept::query()
            ->with('principle')
            ->when(filled($this->filter), fn ($query) => $query->where(
                fn ($query) => $query
                    ->where('title', 'like', "%{$this->filter}%")
                    ->orWhere('definition', 'like', "%{$this->filter}%")
            ))
            ->orderBy('title')
            ->get();
    }

    /**
     * @return Collection<int, Principle>
     */
    #[Computed]
    public function principles(): Collection
    {
        return Principle::orderBy('name')->get();
    }

    public function create(): void
    {
        $this->reset('editingId', 'title', 'definition', 'code_snippet', 'principle_id');
        $this->resetValidation();
        $this->modal('concept-form')->show();
    }

    public function edit(int $id): void
    {
        $concept = Concept::findOrFail($id);

        $this->editingId = $concept->id;
        $this->fill($concept->only('title', 'definition', 'code_snippet', 'principle_id'));
        $this->resetValidation();
        $this->modal('concept-form')->show();
    }

    public function save(SaveConcept $action): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'definition' => 'required|string',
            'code_snippet' => 'nullable|string',
            'principle_id' => 'nullable|exists:principles,id',
        ]);

        $outcome = $action->handle($this->editingId, $validated);

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');

        if ($outcome->success) {
            $this->modal('concept-form')->close();
            unset($this->concepts);
        }
    }

    public function delete(int $id, DeleteConcept $action): void
    {
        $outcome = $action->handle($id);

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');
        unset($this->concepts);
    }
};
