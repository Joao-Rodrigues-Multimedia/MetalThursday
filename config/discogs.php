<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Endereço base
    |--------------------------------------------------------------------------
    |
    | Endereço da API pública do Discogs.
    |
    */
    'base_url' => env(
        'DISCOGS_BASE_URL',
        'https://api.discogs.com',
    ),

    /*
    |--------------------------------------------------------------------------
    | User-Agent
    |--------------------------------------------------------------------------
    |
    | Identificação enviada pela aplicação nos pedidos efectuados ao Discogs.
    |
    */
    'user_agent' => env(
        'DISCOGS_USER_AGENT',
        'MetalThursday/2.0 (https://github.com/joaorodriguesmm/MetalThursday)',
    ),

    /*
    |--------------------------------------------------------------------------
    | Token de acesso
    |--------------------------------------------------------------------------
    |
    | Token pessoal utilizado nos pedidos da API que exigem autenticação.
    |
    */
    'token' => env(
        'DISCOGS_TOKEN',
    ),

    /*
    |--------------------------------------------------------------------------
    | Limites de comunicação
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env(
        'DISCOGS_TIMEOUT',
        10,
    ),

    'tentativas' => (int) env(
        'DISCOGS_TENTATIVAS',
        3,
    ),

    'intervalo_repeticao_ms' => (int) env(
        'DISCOGS_INTERVALO_REPETICAO_MS',
        1000,
    ),

    /*
    |--------------------------------------------------------------------------
    | Intervalo mínimo entre pedidos
    |--------------------------------------------------------------------------
    |
    | O limite é partilhado pela aplicação inteira e aplica-se também a
    | pedidos provenientes de processos ou utilizadores diferentes.
    |
    */
    'intervalo_minimo_pedidos_ms' => (int) env(
        'DISCOGS_INTERVALO_MINIMO_PEDIDOS_MS',
        1000,
    ),
];
