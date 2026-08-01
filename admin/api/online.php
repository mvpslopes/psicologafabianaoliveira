<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/presence.php';

fo_require_auth();

fo_json_response([
    'ok' => true,
    'summary' => fo_presence_snapshot(),
]);
