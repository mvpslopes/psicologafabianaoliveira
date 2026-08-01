<?php
/**
 * Bootstrap da área interna FO Psicologia
 */

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$configPath = __DIR__ . '/../config.php';
if (!is_file($configPath)) {
    $configPath = __DIR__ . '/../config.sample.php';
}

/** @var array $config */
$config = require $configPath;

function fo_config(string $key, $default = null)
{
    global $config;
    return $config[$key] ?? $default;
}

function fo_json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function fo_require_auth(): void
{
    if (empty($_SESSION['fo_admin_auth'])) {
        if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            fo_json_response(['ok' => false, 'error' => 'Não autenticado'], 401);
        }
        header('Location: index.php');
        exit;
    }
}

function fo_is_authenticated(): bool
{
    return !empty($_SESSION['fo_admin_auth']);
}

function fo_e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
