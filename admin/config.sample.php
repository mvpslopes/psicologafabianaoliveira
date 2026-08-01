<?php
/**
 * FO Psicologia — Configuração da área interna
 * Copie este arquivo para config.php e ajuste os valores.
 */
return [
    'username' => 'Fabiana',
    // Senha padrão: FoPsico2026!  — gere um novo hash com: php -r "echo password_hash('SUA_SENHA', PASSWORD_DEFAULT);"
    'password_hash' => '$2y$12$9ZaNq/qpJ3naeh7ifBiXneWtc8ONo1VfwUAYQMMCJpyKrYyDAy7Va',
    'display_name' => 'Fabiana Oliveira',
    'ga4_property_id' => '548074031',
    'ga4_measurement_id' => 'G-5T8DCCK042',
    'service_account_json' => __DIR__ . '/private/ga4-service-account.json',
    'cache_ttl' => 300, // 5 minutos
    'cache_dir' => __DIR__ . '/data/cache',
    'site_url' => 'https://psicologafabianaoliveira.com.br/',
];
