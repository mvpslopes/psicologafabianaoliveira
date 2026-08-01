<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

fo_require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fo_json_response(['ok' => false, 'error' => 'Método não permitido'], 405);
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$current = (string) ($payload['current_password'] ?? '');
$new = (string) ($payload['new_password'] ?? '');
$confirm = (string) ($payload['confirm_password'] ?? '');

if ($new !== $confirm) {
    fo_json_response(['ok' => false, 'error' => 'A confirmação da senha não confere.'], 422);
}

$result = fo_change_password($current, $new);
if (!$result['ok']) {
    fo_json_response($result, 400);
}

fo_json_response(['ok' => true, 'message' => 'Senha atualizada com sucesso.']);
