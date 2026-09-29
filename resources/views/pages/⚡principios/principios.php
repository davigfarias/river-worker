<?php

use App\Actions\DeletePrinciple;
use App\Actions\SavePrinciple;
use App\Models\Principle;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Princípios')] class extends Component
{
    #[Url(as: 'busca')]
    public ?string $filter = null;

    #[Locked]
    public ?int $editingId = null;

    public string $name = '';

    public ?string $acronym = null;

    public ?string $category = null;

    public string $summary = '';

    public ?string $description = null;

    /**
     * @return Collection<int, Principle>
     */
    #[Computed]
    public function principles(): Collection
    {
        return Principle::query()
            ->when(filled($this->filter), fn ($query) => $query->where(
                fn ($query) => $query
                    ->where('name', 'like', "%{$this->filter}%")
                    ->orWhere('acronym', 'like', "%{$this->filter}%")
                    ->orWhere('category', 'like', "%{$this->filter}%")
                    ->orWhere('summary', 'like', "%{$this->filter}%")
            ))
            ->orderBy('name')
            ->get();
    }

    /**
     * Categorias já usadas, para sugerir no formulário.
     *
     * @return Collection<int, string>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Principle::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'acronym', 'category', 'summary', 'description');
        $this->resetValidation();
        $this->modal('principle-form')->show();
    }

    public function edit(int $id): void
    {
        $principle = Principle::findOrFail($id);

        $this->editingId = $principle->id;
        $this->fill($principle->only('name', 'acronym', 'category', 'summary', 'description'));
        $this->resetValidation();
        $this->modal('principle-form')->show();
    }

    public function save(SavePrinciple $action): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'acronym' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:60',
            'summary' => 'required|string|max:500',
            'description' => 'nullable|string',
        ]);

        $outcome = $action->handle($this->editingId, $validated);

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');

        if ($outcome->success) {
            $this->modal('principle-form')->close();
            unset($this->principles, $this->categories);
        }
    }

    public function delete(int $id, DeletePrinciple $action): void
    {
        $outcome = $action->handle($id);

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');
        unset($this->principles);
    }
};
