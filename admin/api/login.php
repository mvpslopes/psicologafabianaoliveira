<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fo_json_response(['ok' => false, 'error' => 'Método não permitido'], 405);
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$username = trim((string) ($payload['username'] ?? ''));
$password = (string) ($payload['password'] ?? '');

if ($username === '' || $password === '') {
    fo_json_response(['ok' => false, 'error' => 'Informe usuário e senha.'], 422);
}

if (!fo_login($username, $password)) {
    fo_json_response(['ok' => false, 'error' => 'Usuário ou senha inválidos.'], 401);
}

fo_json_response([
    'ok' => true,
    'name' => $_SESSION['fo_admin_name'] ?? 'Fabiana Oliveira',
]);
