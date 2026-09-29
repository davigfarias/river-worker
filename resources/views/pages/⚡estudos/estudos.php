<?php

use App\Actions\SaveCodeStudy;
use App\Models\CodeStudy;
use App\Models\Principle;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Estudos')] class extends Component
{
    public string $title = '';

    public ?string $topic = null;

    public ?int $principle_id = null;

    /**
     * @return Collection<int, CodeStudy>
     */
    #[Computed]
    public function studies(): Collection
    {
        return CodeStudy::query()->with('principle')->withCount('steps')->latest('updated_at')->get();
    }

    /**
     * @return Collection<int, Principle>
     */
    #[Computed]
    public function principles(): Collection
    {
        return Principle::orderBy('name')->get();
    }

    public function save(SaveCodeStudy $action): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'topic' => 'nullable|string|max:255',
            'principle_id' => 'nullable|exists:principles,id',
        ]);

        $outcome = $action->handle(null, $validated);

        if (! $outcome->success) {
            Flux::toast(duration: 2500, text: $outcome->message, variant: 'danger');

            return;
        }

        $this->redirectRoute('estudos.show', $outcome->data->id, navigate: true);
    }
};
