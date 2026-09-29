# **Especificação Técnica e Funcional: River Worker (v1.4)**

> * **Projeto:** River Worker  
> * **Origem / Base Arquitetural:** River Note Taker (Laravel Cloud)  
> * **Domínio:** Cockpit de Engenharia de Software, Orquestração de Fluxo com IA, Base de Documentação e Estudo Técnico Aprofundado  
> * **Status:** Especificação Consolidada e Simplificada (v1.4)  
> * Tecnologias: Livewire v4, FluxUI, EasyMDE

## **1\. Visão Geral & Premissas de Infraestrutura**

O **River Worker** é um ambiente de trabalho pessoal (*developer cockpit*) projetado para organizar o ciclo diário de engenharia de software moderno.

### **Premissa de Infraestrutura: Laravel Cloud (Serverless / Desconectado)**

> * **Ambiente em Produção:** A aplicação roda no **Laravel Cloud**, utilizando instâncias serverless que dormem quando inativas.  
> * **Operação Desconectada:** A aplicação **não** se comunica em tempo real com a máquina local, não possui daemons rodando em background, não consome webhooks locais e não faz inspeção direta da árvore de arquivos ou histórico do Git.  
> * **Entrada Manual e Ágil:** Toda a alimentação de dados é feita de forma assíncrona através de formulários simples e diretos na interface web, funcionando como o roteiro mental e a bancada de anotações do desenvolvedor enquanto ele opera o terminal local (Claude Code, Cursor, Neovim, etc.).

### **O Papel do River Worker no Fluxo com IA**

Ferramentas de IA geram código em ritmo acelerado, mas trazem dois desafios:

> 1. **Atritos e Regressões Pontuais:** Mudanças indesejadas em métodos existentes ou furos de lógica que precisam ser pontuados no próximo prompt.  
> 2. **Débito Arquitetural:** Códigos gerados pela IA sem critério degradam a arquitetura. Determinadas classes e serviços demandam intervenção manual (*Code Craftsmanship*).

O River Worker atua como a bancada de governança:.

> * Mapeia manualmente quais arquivos específicos exigem intervenção manual de design.  
> * Lista os atritos/glitches verificados e deixados pela IA para alimentar o próximo prompt \- cadastrados manualmente   
> * Disponibiliza a documentação em Markdown dos sistemas em desenvolvimento e a base de princípios técnicos para rápida consulta.

## **2\. Mapa Arquitetural de Módulos**

`River Worker`  
`├── 1. Gestão de Trabalho (Core / Tela Principal)`  
`│   ├── Dashboard de Foco Ativo (Current Task)`  
`│   ├── Checklist Fixo Padronizado (Pipeline Obrigatório)`  
`│   ├── Lista Manual de Arquivos de Foco (Path + O que Modificar)`  
`│   └── Ciclo de Vida & Registro de Deploy`  
`│`  
`├── 2. Documentações de Projetos`  
`│   ├── Cadastro de Projetos / Aplicações`  
`│   ├── Documentação em Markdown por Módulo/Categoria`  
`│`  
`├── 3. Mecanismo de Busca Global (Laravel Scout)`  
`│   ├── Command Palette Unificada (Atalho Cmd+K / Ctrl+K)`  
`│   └── Indexação Full-Text (Docs, Tarefas, Princípios, Conceitos, Recursos)`  
`│`  
`├── 4. Módulo de Princípios (Portado do River NoteTaker)`  
`│   ├── Princípios de Engenharia (SOLID, Clean Architecture, Padrões)`  
`│   └── Consulta Conceitual Integrada`  
`│`  
`├── 5. Base de Conhecimento & Estudo Técnico`  
`│   ├── Editor Split-View (Trecho de Código x Notas em Markdown)`  
`│   ├── Conceitos & Glossário Técnico`  
`└── 6. Módulo de Recursos (Herdado do River NoteTaker)`  
    `├── Livros, Artigos, Papers e Vídeos`  
    `└── Fichamentos e Anotações de Estudo`

## **3\. Especificação Detalhada das Funcionalidades**

### **3.1. Módulo de Gestão de Trabalho (Core / Tela Principal)**

A tela principal atua como cockpit único de operação, concentrando a demanda ativa e minimizando a troca de contexto.

#### **A. Dashboard Principal**

> * **Foco Ativo (*Current Focus*):** Destaque visual da demanda em andamento no momento.  
> * **Métricas da Demanda:** Barra de progresso do checklist fixo do projeto que tem o último registro (atualizado para quem tem o último registro no banco), contador de arquivos pendentes de revisão manual  
> * **Fila de Trabalho:** Listagem rápida das demandas em backlog do projeto atualizado por último, em desenvolvimento e histórico recente de deploys desse projeto. 

#### **B. Detalhamento do Item de Trabalho (*Work Item*)**

Ao abrir uma demanda, o desenvolvedor acessa o painel de governança estruturado:

> 1. **Especificação Técnica & Intenção:**  
   * Campo de texto em Markdown detalhando regras de negócio, escopo da tarefa e prompts base que orientarão as sessões de IA.  
> 2. **Checklist Padronizado e Fixo:**  
   * Pipeline fixo aplicado a todas as entregas técnicas para garantir qualidade (ajustar conforme o padrão que vemos fazendo \- o que for automático e já feito por você, não precisa estar aqui: tipo testes e linting)  
     * \[ \] Especificação e Contrato de Dados Definidos  
     * \[ \] Execução da Demanda via IA (Claude Code / Agente)  
     * \[ \] Refatoração / Revisão Manual dos Arquivos de Design  
     * \[ \] Ajuste das Fricções / Inconsistências de IA Identificadas  
     * \[ \] Testes Automatizados (Unitários / Feature)  
     * \[ \] Linting & Formatação de Código (Pint / PHP-CS-Fixer)  
     * \[ \] Auto Code Review & Validação de Escopo  
     * \[ \] Deploy em Produção  
     * \[ \] Validação Pós-Deploy (*Sanity Check*)  
> 3. **Lista Manual de Arquivos para Revisão de Design (Simples e Direta):**  
   * **Propósito:** Registro pontual dos arquivos que o desenvolvedor escolheu abrir e lapidar manualmente no seu editor local (ex.: desacoplar dependências, criar contratos/interfaces, aplicar Strategy Pattern).  
   * **Sem dependência de tempo real:** Não há leitura de diffs de Git nem leitura de diretórios locais.  
   * **Campos da Linha:**  
     * file\_path: Nome ou caminho do arquivo (ex.: app/Services/CheckoutService.php).  
     * reason\_notes: O que precisa ser visualizado ou desenhado na mão (ex.: *"Extrair regra de cálculo de frete para um Strategy desacoplado"*).  
     * is\_reviewed: Checkbox simples para marcar quando a revisão manual daquele arquivo na IDE local foi finalizada.  
> 4. **Aba de Ajustes de Iteração / Glitches de IA (Controle Prático de Bugs):**  
   * **Propósito:** Lista ágil de pequenos desvios, métodos deletados indevidamente ou regressões geradas pelo Claude Code durante as respostas de prompt.  
   * **Dinâmica:** Serve como bloco de notas tático para estruturar o próximo prompt de correção no terminal ou para ajuste manual.  
> 5. **Ciclo de Vida & Deploy:**  
   * **Estados:** Backlog \-\> Em Desenvolvimento com IA \-\> Revisão de Design \-\> Testes \-\> Pronto para Deploy \-\> Em Produção.  
   * **Registro de Deploy:** Armazenamento manual ou por botão de fechamento do timestamp de deploy (deployed\_at), versão e notas de lançamento.

### **3.2. Módulo de Documentações de Projetos**

Repositório central de especificações técnicas das aplicações sob gestão do desenvolvedor.

> * **Cadastro de Aplicações/Projetos:**  
  * Registro dos sistemas trabalhados (ex.: *River Note Taker*, *River Worker*, microsserviços, clientes).  
  * Campos: Nome do projeto, slug, repositório (URL) e visão geral.  
> * **Documentos Técnicos em Markdown:**  
  * Criação de páginas de documentação categorizadas por módulo (ex.: *Módulo de Autenticação*, *Arquitetura de Filas*, *Regras de Cobrança*).  
  * Conteúdo rico em Markdown para consulta direta.  
> * **Finalidade no Fluxo:**  
  * Fornecer consulta rápida das especificações existentes

### **3.3. Mecanismo de Busca Global (Laravel Scout)**

Indexação full-text unificada com busca instantânea através de Command Palette.

> * **Command Palette Global (Cmd \+ K / Ctrl \+ K):**  
  * Modal de busca acessível em qualquer tela via atalho de teclado ou clique na barra superior.  
> * **Escopo de Indexação:**  
  * **Documentações de Projetos:** Título, categoria e corpo Markdown dos documentos.  
  * **Work Items:** Título, especificação e regras de negócio.  
  * **Princípios:** Nome, sigla (ex.: SRP, OCP, DRY) e resumo conceitual.  
  * **Glossário / Conceitos:** Termos técnicos e definições.  
  * **Recursos:** Livros, títulos de artigos e anotações.  
> * **Navegação Ágil:**  
  * Resultados agrupados por categoria com navegação via teclado (↑ e ↓) e redirecionamento imediato via Enter.

### **3.4. Módulo de Princípios (Portado do River Note Taker)**

**Nota de Portabilidade:** Mantém exatamente a mesma arquitetura de dados e experiência do River Note Taker, aplicado agora a **Princípios de Engenharia de Software**.

> * **Objetivo:** Ter disponível o catálogo conceitual para balizar decisões técnicas contra código puramente procedural ou acoplado gerado por IA.  
> * **Exemplos de Conteúdo:**  
  * **SOLID:** Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion.  
  * **Padrões & Diretrizes:** DRY, KISS, YAGNI, Law of Demeter, Separation of Concerns.  
  * **Arquitetura Moderna:** Clean Architecture, Twelve-Factor App, Idempotência, Imutabilidade.  
> * **Conexões:** Consulta transversal durante a escrita da especificação ou durante os estudos no Split-View.

### **3.5. Base de Conhecimento & Estudo Técnico**

#### **A. Editor Split-View (Estudo Passo a Passo de Código)**

Interface voltada para dissectar refatorações, analisar patterns e internalizar conceitos técnicos:

> * **Disposição em Duas Colunas (Side-by-Side):**  
  * **Coluna da Esquerda (Código Fonte):**  
    * Bloco de código colado manualmente pelo usuário com realce de sintaxe (*syntax highlighting* para PHP, Laravel, TypeScript, SQL, etc.).  
    * Organização em etapas ou passos de evolução (ex.: *Passo 1: Código gerado inicial* \-\> *Passo 2: Extração de Interface* \-\> *Passo 3: Aplicação de Pattern*). Esse passo precisa ser sortable (livewire) pois eu posso trocar um passo de ordem. E tem que ser editável a posteriori.  
  * **Coluna da Direita (Anotações em Markdown):**  
    * Editor Markdown nativo (easyMDE) com renderização das anotações.  
    * Espaço para registrar o raciocínio arquitetural, trade-offs, prós e contras e princípios aplicados, espelhados ao lado do código.  
  * Text box no centro pra resumo do código e da implementação (com suporte a Marmaid)  
> * **Objetivo:** Estudo deliberado e fichamento didático sem poluir o repositório de produção com anotações de estudo pessoal.

#### **B. Conceitos & Glossário Técnico (igual ao River NoteTaker)**

> * Cadastro autônomo ou extração de termos técnicos durante a leitura de recursos e estudos de código.  
> * Termo, definição concisa, exemplos curtos e vínculo com princípios.

### **3.6. Módulo de Recursos (Herdado do River NoteTaker)**

> * **Entidades:** Livros técnicos, artigos, papers, documentações de referência e vídeos técnicos.  
> * **Fichamento:** Anotações em Markdown atreladas a cada recurso.  
> * **Status:** Controle de leitura (Quero Ler, Estudando, Concluído).

## **4\. Modelo de Dados Conceitual (Simplificado)**

`+-----------------------------------------------------------------+`  
`|                            Project                              |`  
`+-----------------------------------------------------------------+`  
`| id            : bigint, PK                                      |`  
`| name          : string (ex: River Note Taker, River Worker)     |`  
`| slug          : string, unique                                  |`  
`| repository_url: string, nullable                                |`  
`| description   : text, nullable                                  |`  
`| created_at    : timestamp                                       |`  
`+-----------------------------------------------------------------+`  
       `│ 1`  
       `│ n`  
`+-----------------------------------------------------------------+`  
`|                           ProjectDoc                            |`  
`|                     (Scout Searchable)                          |`  
`+-----------------------------------------------------------------+`  
`| id            : bigint, PK                                      |`  
`| project_id    : bigint, FK                                      |`  
`| title         : string                                          |`  
`| slug          : string                                          |`  
`| category      : string (ex: Módulo Auth, Filas, Regras de Cobr.)|`  
`| content       : longText (Markdown da documentação)             |`  
`| order         : integer                                         |`  
`| created_at    : timestamp                                       |`  
`+-----------------------------------------------------------------+`

`+-----------------------------------------------------------------+`  
`|                            WorkItem                             |`  
`|                     (Scout Searchable)                          |`  
`+-----------------------------------------------------------------+`  
`| id            : bigint, PK                                      |`  
`| project_id    : bigint, FK, nullable                            |`  
`| title         : string                                          |`  
`| description   : text (Markdown com escopo / prompts de IA)      |`  
`| status        : enum (backlog, in_dev_ai, design_review,       |`  
`|                       testing, ready_deploy, deployed)          |`  
`| deployed_at   : timestamp, nullable                             |`  
`| created_at    : timestamp                                       |`  
`+-----------------------------------------------------------------+`  
       `│ 1                   │ 1                  │ 1`  
       `│                     │                    │`  
       `│ n                   │ n                  │ n`  
`+──────────────────+  +───────────────────+  +────────────────────+`  
`|     WorkStep     |  |     WorkIssue     |  |      WorkFile      |`  
`|  (Checklist)     |  | (Glitches da IA)  |  |  (Arquivos Foco)   |`  
`+──────────────────+  +───────────────────+  +────────────────────+`  
`| id               |  | id                |  | id                 |`  
`| work_item_id     |  | work_item_id      |  | work_item_id       |`  
`| title            |  | description       |  | file_path          |`  
`| is_completed     |  | origin (ai_glitch,|  | reason_notes (o que|`  
`| order            |  |         logic)    |  |  precisa mudar)    |`  
`|                  |  | is_resolved       |  | is_reviewed (bool) |`  
`+──────────────────+  +───────────────────+  +────────────────────+`

`+-----------------------------------------------------------------+`  
`|                            Principle                            |`  
`|                     (Scout Searchable)                          |`  
`+-----------------------------------------------------------------+`  
`| id            : bigint, PK                                      |`  
`| name          : string                                          |`  
`| acronym       : string, nullable (ex: SRP, DIP, DRY)            |`  
`| summary       : string                                          |`  
`| description   : text (Markdown)                                 |`  
`+-----------------------------------------------------------------+`

`+-----------------------------------------------------------------+`  
`|                  Estudos Split-View & Recursos                  |`  
`|                     (Scout Searchable)                          |`  
`+-----------------------------------------------------------------+`  
`| CodeStudy      : id, title, topic, principle_id, description    |`  
`| CodeStudyStep  : id, code_study_id, language, snippet, markdown |`  
`| Concept        : id, title, definition, code_snippet            |`  
`| Resource       : id, title, type, source_url, status            |`  
`| ResourceNote   : id, resource_id, content (Markdown)            |`  
`| Lesson         : id, course_name, topic, notes, date            |`  
`+-----------------------------------------------------------------+`

## **5\. O Fluxo de Trabalho Prático (Desconectado e Sem Fricção \- revisar isso)** 

                 `[Consulta Rápida a ProjectDocs / Cmd+K]`  
                                   `│`  
                                   `▼`  
          `[Planejar Tarefa no River Worker: Escopo & Prompts]`  
                                   `│`  
                                   `▼`  
             `[Trabalho Local com Claude Code / Cursor / IDE]`  
                                   `│`  
            `┌──────────────────────┴──────────────────────┐`  
            `│                                             │`  
            `▼                                             ▼`  
 `[Identificou Inconsistência da IA?]        [Arquivo precisa de Design Manual?]`  
            `│                                             │`  
            `▼                                             ▼`  
 `[Anota na aba Glitches de IA]            [Cadastra no River Worker:           ]`  
 `[Usa no próximo prompt local]            [  - file_path                       ]`  
            `│                             [  - o que precisa ser visualizado   ]`  
            `│                                             │`  
            `│                                             ▼`  
            `│                             [Dev abre o arquivo na IDE local     ]`  
            `│                             [e faz o refactoring manual          ]`  
            `│                             [Marca 'is_reviewed' no River Worker ]`  
            `│                                             │`  
            `└──────────────────────┬──────────────────────┘`  
                                   `│`  
                                   `▼`  
                  `[Cumprir Checklist Fixo no Painel]`  
                                   `│`  
                                   `▼`  
                         `[Deploy em Produção]`  
                      `(Carimbo em deployed_at)`  
                                   `│`  
                                   `▼`  
             `[Atualiza ProjectDoc caso a arquitetura mude]`

## **6\. Diretrizes de Interface (UI/UX)**

> 1. **Entrada de Dados Rápida e Descomplicada:**  
   * Formulário inline com dois campos para adicionar arquivos: Caminho do arquivo e O que precisa verificar/modificar.  
   * Atalho global Cmd \+ K / Ctrl \+ K para busca unificada via Laravel Scout.  
> 2. **Editor Split-View:**  
   * Layout de duas colunas lado a lado em desktop (código estático colado à esquerda, editor/visualizador Markdown à direita).  
> 3. **Consistência de Identidade:**  
   * Reutilização dos componentes visuais, padrões de cores e tipografia já consolidados no *River Note Taker*, mantendo a familiaridade de uso.