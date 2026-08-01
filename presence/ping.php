<?php

declare(strict_types=1);

/**
 * Endpoint público: heartbeat de visitantes online no site.
 * POST/GET JSON: { "visitor": "uuid", "path": "/index.html" }
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../admin/lib/presence.php';

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_REQUEST;
}

$visitor = (string) ($payload['visitor'] ?? '');
$path = (string) ($payload['path'] ?? ($_SERVER['HTTP_REFERER'] ?? '/'));

if ($visitor === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'visitor obrigatório']);
    exit;
}

$result = fo_presence_ping($visitor, $path);
http_response_code(!empty($result['ok']) ? 200 : 400);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
