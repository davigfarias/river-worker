<?php

return [

    /*
    | Checklist fixo aplicado a toda demanda nova. Testes e linting ficam de
    | fora: o agente já executa esses passos sozinho.
    */
    'checklist' => [
        'Especificação e contrato de dados definidos',
        'Execução da demanda via IA (Claude Code / agente)',
        'Refatoração / revisão manual dos arquivos de design',
        'Auto code review e validação de escopo',
        'Deploy em produção',
        'Validação pós-deploy (sanity check)',
    ],

];
