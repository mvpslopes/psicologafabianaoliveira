<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

fo_logout();

if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
    fo_json_response(['ok' => true]);
}

header('Location: ../index.php');
exit;
