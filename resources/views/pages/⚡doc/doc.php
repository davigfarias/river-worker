<?php

use App\Actions\DeleteProjectDoc;
use App\Actions\SaveProjectDoc;
use App\Models\ProjectDoc;
use App\Support\MarkdownDocument;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $projectId;

    #[Locked]
    public int $docId;

    public string $title = '';

    public string $category = '';

    public string $content = '';

    #[Url(as: 'editar')]
    public bool $editing = false;

    public function mount(int $project, int $doc): void
    {
        $model = ProjectDoc::where('project_id', $project)->findOrFail($doc);

        $this->projectId = $model->project_id;
        $this->docId = $model->id;
        $this->fill(['title' => $model->title, 'category' => $model->category, 'content' => (string) $model->content]);
    }

    #[Computed]
    public function doc(): ProjectDoc
    {
        return ProjectDoc::with('project')->findOrFail($this->docId);
    }

    /**
     * @return array{html: string, headings: list<array{level: int, title: string, id: string}>}
     */
    #[Computed]
    public function rendered(): array
    {
        return MarkdownDocument::render((string) $this->doc->content);
    }

    public function save(SaveProjectDoc $action): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $outcome = $action->handle($this->projectId, $this->docId, $validated);

        if ($outcome->success) {
            $this->editing = false;
            unset($this->doc, $this->rendered);
        }

        Flux::toast(duration: 2500, text: $outcome->message, variant: $outcome->success ? 'success' : 'danger');
    }

    public function delete(DeleteProjectDoc $action): void
    {
        $outcome = $action->handle($this->docId);

        if (! $outcome->success) {
            Flux::toast(duration: 2500, text: $outcome->message, variant: 'danger');

            return;
        }

        $this->redirectRoute('projetos.show', $this->doc->project->slug, navigate: true);
    }
};
