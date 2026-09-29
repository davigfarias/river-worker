<?php

use App\Actions\SaveProject;
use App\Models\Project;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Projetos')] class extends Component
{
    public string $name = '';

    public ?string $repository_url = null;

    public ?string $description = null;

    /**
     * @return Collection<int, Project>
     */
    #[Computed]
    public function projects(): Collection
    {
        return Project::query()->withCount(['docs', 'workItems'])->orderBy('name')->get();
    }

    public function save(SaveProject $action): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'repository_url' => 'nullable|url|max:2048',
            'description' => 'nullable|string|max:5000',
        ]);

        $outcome = $action->handle(null, $validated);

        if (! $outcome->success) {
            Flux::toast(duration: 2500, heading: 'Ocorreu um erro', text: $outcome->message, variant: 'danger');

            return;
        }

        $this->reset('name', 'repository_url', 'description');
        $this->modal('add-project')->close();
        unset($this->projects);

        Flux::toast(duration: 2500, text: $outcome->message, variant: 'success');
    }
};
