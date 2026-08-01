<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/ga4.php';
require_once __DIR__ . '/../lib/presence.php';

fo_require_auth();

$range = (string) ($_GET['range'] ?? '30d');
$allowed = ['today', '7d', '30d', '90d', 'all'];
if (!in_array($range, $allowed, true)) {
    $range = '30d';
}

$online = fo_presence_snapshot();

try {
    $client = new FoGa4Client();
    $data = $client->fetchDashboard($range);
    $data['summary']['onlineNow'] = $online['online'];
    $data['online'] = $online;
    $data['note'] = ($data['note'] ?? '')
        . ' Online agora usa heartbeat do site (últimos ' . $online['ttl'] . 's), independente do GA4.';
    fo_json_response($data);
} catch (Throwable $e) {
    // Mesmo sem GA4, devolve zeros + online ao vivo
    fo_json_response([
        'ok' => true,
        'partial' => true,
        'error' => $e->getMessage(),
        'range' => $range,
        'generatedAt' => date('c'),
        'summary' => [
            'onlineNow' => $online['online'],
            'users' => 0,
            'sessions' => 0,
            'views' => 0,
            'viewsPerVisit' => 0,
            'whatsappClicks' => 0,
            'avgSessionDuration' => 0,
            'bounceRate' => 0,
            'conversionRate' => 0,
            'pagesPerSession' => 0,
            'events' => 0,
        ],
        'online' => $online,
        'hourly' => [],
        'weekday' => [],
        'timeline' => [],
        'topPages' => [],
        'devices' => [],
        'browsers' => [],
        'os' => [],
        'sources' => [],
        'landings' => [],
        'exits' => [],
        'countries' => [],
        'cities' => [],
        'note' => 'GA4 indisponível no momento. Contador Online agora segue ativo via site.',
    ]);
}
