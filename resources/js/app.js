import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';
import hljs from 'highlight.js/lib/common';
import 'highlight.js/styles/github-dark.css';
import './session-modal';
import './mermaid';

// Insere um bloco de diagrama ```mermaid no cursor (renderizado na exibição).
function insertMermaid(editor) {
    const cm = editor.codemirror;
    const startsMidLine = cm.getCursor().ch > 0;

    cm.replaceSelection(`${startsMidLine ? '\n' : ''}\`\`\`mermaid\nflowchart TD\n  A[Início] --> B[Fim]\n\`\`\`\n`);
    cm.focus();
}

// Realce de sintaxe dos blocos <pre><code class="language-x"> renderizados do Markdown.
const highlightPending = (root) => {
    root.querySelectorAll('pre > code:not(.language-mermaid):not([data-highlighted])').forEach((block) => hljs.highlightElement(block));
};

document.addEventListener('alpine:init', () => {
    Alpine.directive('highlight', (el, _directive, { cleanup }) => {
        const observer = new MutationObserver(() => highlightPending(el));

        observer.observe(el, { childList: true, subtree: true });
        highlightPending(el);
        cleanup(() => observer.disconnect());
    });

    // `field` é a propriedade Livewire (ex.: 'form.content').
    Alpine.data('markdownEditor', (field, minHeight = '200px') => ({
        editor: null,
        unwatch: null,
        visibility: null,
        init() {
            const editor = new EasyMDE({
                element: this.$refs.textarea,
                toolbar: [
                    'bold', 'italic', 'heading', '|',
                    'unordered-list', 'ordered-list', '|',
                    'code', 'table', 'link',
                    { name: 'mermaid', action: insertMermaid, className: 'fa fa-sitemap', title: 'Diagrama Mermaid' },
                ],
                spellChecker: false,
                status: false,
                initialValue: this.$wire.$get(field) ?? '',
                placeholder: this.$refs.textarea.getAttribute('placeholder'),
                minHeight,
            });

            this.editor = editor;

            // O CodeMirror nasce em branco dentro de containers ocultos (modais); refresh ao ficar visível.
            this.visibility = new IntersectionObserver(([entry]) => entry.isIntersecting && editor.codemirror.refresh());
            this.visibility.observe(this.$el);

            editor.codemirror.on('change', () => this.$wire.$set(field, editor.value(), false));

            this.unwatch = this.$wire.$watch(field, (value) => {
                if ((value ?? '') !== editor.value()) {
                    editor.value(value ?? '');
                }
            });
        },
        destroy() {
            this.unwatch?.();
            this.visibility?.disconnect();
            this.editor?.toTextArea();
        },
    }));
});
