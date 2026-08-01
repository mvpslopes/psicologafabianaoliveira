<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/cache.php';

/**
 * Cliente leve da Google Analytics Data API (REST + JWT RS256).
 * Sem Composer — adequado para Hostinger shared hosting.
 */
class FoGa4Client
{
    private string $propertyId;
    private array $credentials;
    private ?string $accessToken = null;
    private int $tokenExpires = 0;

    public function __construct()
    {
        $this->propertyId = (string) fo_config('ga4_property_id');
        $jsonPath = (string) fo_config('service_account_json');

        if (!is_file($jsonPath)) {
            throw new RuntimeException('Arquivo da service account não encontrado.');
        }

        $raw = file_get_contents($jsonPath);
        $creds = json_decode((string) $raw, true);
        if (!is_array($creds) || empty($creds['private_key']) || empty($creds['client_email'])) {
            throw new RuntimeException('Service account JSON inválido.');
        }

        $this->credentials = $creds;
    }

    public function fetchDashboard(string $range): array
    {
        $dates = $this->resolveDateRange($range);
        $cacheKey = 'stats_' . $range;

        $cached = fo_cache_get($cacheKey);
        if ($cached !== null) {
            $cached['cached'] = true;
            return $cached;
        }

        $start = $dates['start'];
        $end = $dates['end'];

        $overview = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'metrics' => [
                ['name' => 'totalUsers'],
                ['name' => 'sessions'],
                ['name' => 'screenPageViews'],
                ['name' => 'averageSessionDuration'],
                ['name' => 'bounceRate'],
                ['name' => 'sessionsPerUser'],
                ['name' => 'eventCount'],
                ['name' => 'screenPageViewsPerSession'],
            ],
        ]);

        $whatsapp = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'eventName']],
            'metrics' => [['name' => 'eventCount']],
            'dimensionFilter' => [
                'filter' => [
                    'fieldName' => 'eventName',
                    'stringFilter' => [
                        'matchType' => 'EXACT',
                        'value' => 'whatsapp_click',
                    ],
                ],
            ],
        ]);

        $byHour = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'hour']],
            'metrics' => [['name' => 'sessions']],
            'orderBys' => [['dimension' => ['dimensionName' => 'hour']]],
            'limit' => 24,
        ]);

        $byWeekday = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'dayOfWeek']],
            'metrics' => [['name' => 'sessions']],
            'orderBys' => [['dimension' => ['dimensionName' => 'dayOfWeek']]],
            'limit' => 7,
        ]);

        $timeline = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'date']],
            'metrics' => [
                ['name' => 'totalUsers'],
                ['name' => 'sessions'],
                ['name' => 'screenPageViews'],
            ],
            'orderBys' => [['dimension' => ['dimensionName' => 'date']]],
            'limit' => 120,
        ]);

        $topPages = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'pagePath']],
            'metrics' => [
                ['name' => 'screenPageViews'],
                ['name' => 'averageSessionDuration'],
                ['name' => 'bounceRate'],
            ],
            'orderBys' => [['metric' => ['metricName' => 'screenPageViews'], 'desc' => true]],
            'limit' => 12,
        ]);

        $devices = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'deviceCategory']],
            'metrics' => [['name' => 'sessions']],
            'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
            'limit' => 10,
        ]);

        $browsers = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'browser']],
            'metrics' => [['name' => 'sessions']],
            'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
            'limit' => 8,
        ]);

        $os = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'operatingSystem']],
            'metrics' => [['name' => 'sessions']],
            'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
            'limit' => 8,
        ]);

        $sources = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'sessionDefaultChannelGroup']],
            'metrics' => [['name' => 'sessions']],
            'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
            'limit' => 10,
        ]);

        $landings = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'landingPage']],
            'metrics' => [['name' => 'sessions']],
            'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
            'limit' => 10,
        ]);

        $exits = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'pagePath']],
            'metrics' => [
                ['name' => 'sessions'],
                ['name' => 'bounceRate'],
            ],
            'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
            'limit' => 10,
        ]);

        $countries = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [['name' => 'country']],
            'metrics' => [
                ['name' => 'sessions'],
                ['name' => 'screenPageViews'],
            ],
            'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
            'limit' => 12,
        ]);

        $cities = $this->runReport([
            'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
            'dimensions' => [
                ['name' => 'city'],
                ['name' => 'country'],
            ],
            'metrics' => [
                ['name' => 'sessions'],
                ['name' => 'screenPageViews'],
            ],
            'orderBys' => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
            'limit' => 20,
        ]);

        $users = (int) $this->metricValue($overview, 0);
        $sessions = (int) $this->metricValue($overview, 1);
        $views = (int) $this->metricValue($overview, 2);
        $avgDuration = (float) $this->metricValue($overview, 3);
        $bounce = (float) $this->metricValue($overview, 4);
        $events = (int) $this->metricValue($overview, 6);
        $pagesPerSession = (float) $this->metricValue($overview, 7);
        $waClicks = (int) $this->firstMetric($whatsapp);
        $conversion = $sessions > 0 ? ($waClicks / $sessions) * 100 : 0.0;

        $payload = [
            'ok' => true,
            'cached' => false,
            'range' => $range,
            'startDate' => $start,
            'endDate' => $end,
            'propertyId' => $this->propertyId,
            'generatedAt' => date('c'),
            'summary' => [
                'users' => $users,
                'sessions' => $sessions,
                'views' => $views,
                'viewsPerVisit' => $sessions > 0 ? round($views / $sessions, 1) : 0,
                'whatsappClicks' => $waClicks,
                'avgSessionDuration' => round($avgDuration),
                'bounceRate' => round($bounce * 100, 1),
                'conversionRate' => round($conversion, 1),
                'pagesPerSession' => round($pagesPerSession, 1),
                'events' => $events,
            ],
            'hourly' => $this->mapDimMetric($byHour, 'hour', 'sessions'),
            'weekday' => $this->mapWeekday($byWeekday),
            'timeline' => $this->mapTimeline($timeline),
            'topPages' => $this->mapPages($topPages),
            'devices' => $this->mapLabeled($devices),
            'browsers' => $this->mapLabeled($browsers),
            'os' => $this->mapLabeled($os),
            'sources' => $this->mapLabeled($sources),
            'landings' => $this->mapLabeled($landings, 'entradas'),
            'exits' => $this->mapExits($exits),
            'countries' => $this->mapGeo($countries, false),
            'cities' => $this->mapGeo($cities, true),
            'note' => 'IP individual não está disponível na API do GA4 (privacidade do Google). Cliques de WhatsApp usam o evento whatsapp_click. Dados em cache (até 5 min). Propriedade GA4: ' . $this->propertyId . '.',
        ];

        fo_cache_set($cacheKey, $payload);
        return $payload;
    }

    private function resolveDateRange(string $range): array
    {
        return match ($range) {
            'today' => ['start' => 'today', 'end' => 'today'],
            '7d' => ['start' => '7daysAgo', 'end' => 'today'],
            '90d' => ['start' => '90daysAgo', 'end' => 'today'],
            'all' => ['start' => '2024-01-01', 'end' => 'today'],
            default => ['start' => '30daysAgo', 'end' => 'today'],
        };
    }

    private function runReport(array $body): array
    {
        $url = 'https://analyticsdata.googleapis.com/v1beta/properties/' . $this->propertyId . ':runReport';
        return $this->request($url, $body);
    }

    private function request(string $url, array $body): array
    {
        $token = $this->getAccessToken();
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_TIMEOUT => 45,
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Falha na requisição GA4: ' . $error);
        }

        $data = json_decode($response, true);
        if ($status >= 400) {
            $message = $data['error']['message'] ?? ('HTTP ' . $status);
            throw new RuntimeException('GA4 API: ' . $message);
        }

        return is_array($data) ? $data : [];
    }

    private function getAccessToken(): string
    {
        if ($this->accessToken && time() < $this->tokenExpires - 60) {
            return $this->accessToken;
        }

        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claim = $this->base64UrlEncode(json_encode([
            'iss' => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $unsigned = $header . '.' . $claim;
        $privateKey = openssl_pkey_get_private($this->credentials['private_key']);
        if ($privateKey === false) {
            throw new RuntimeException('Não foi possível ler a private key.');
        }

        $signature = '';
        $ok = openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$ok) {
            throw new RuntimeException('Falha ao assinar JWT.');
        }

        $jwt = $unsigned . '.' . $this->base64UrlEncode($signature);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode((string) $response, true);
        if ($status >= 400 || empty($data['access_token'])) {
            $message = $data['error_description'] ?? $data['error'] ?? 'token error';
            throw new RuntimeException('OAuth token: ' . $message);
        }

        $this->accessToken = $data['access_token'];
        $this->tokenExpires = $now + (int) ($data['expires_in'] ?? 3600);
        return $this->accessToken;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function metricValue(array $report, int $index): string
    {
        $rows = $report['rows'] ?? [];
        if (!$rows) {
            return '0';
        }
        return (string) ($rows[0]['metricValues'][$index]['value'] ?? '0');
    }

    private function firstMetric(array $report): string
    {
        return $this->metricValue($report, 0);
    }

    private function mapDimMetric(array $report, string $dimKey, string $metricKey): array
    {
        $out = [];
        foreach ($report['rows'] ?? [] as $row) {
            $out[] = [
                'label' => (string) ($row['dimensionValues'][0]['value'] ?? ''),
                'value' => (int) ($row['metricValues'][0]['value'] ?? 0),
            ];
        }
        return $out;
    }

    private function mapWeekday(array $report): array
    {
        $names = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        $map = array_fill(0, 7, 0);
        foreach ($report['rows'] ?? [] as $row) {
            $idx = (int) ($row['dimensionValues'][0]['value'] ?? 0);
            if ($idx >= 0 && $idx <= 6) {
                $map[$idx] = (int) ($row['metricValues'][0]['value'] ?? 0);
            }
        }
        $out = [];
        for ($i = 0; $i < 7; $i++) {
            $out[] = ['label' => $names[$i], 'value' => $map[$i]];
        }
        return $out;
    }

    private function mapTimeline(array $report): array
    {
        $out = [];
        foreach ($report['rows'] ?? [] as $row) {
            $raw = (string) ($row['dimensionValues'][0]['value'] ?? '');
            $label = strlen($raw) === 8
                ? substr($raw, 6, 2) . '/' . substr($raw, 4, 2)
                : $raw;
            $out[] = [
                'label' => $label,
                'users' => (int) ($row['metricValues'][0]['value'] ?? 0),
                'sessions' => (int) ($row['metricValues'][1]['value'] ?? 0),
                'views' => (int) ($row['metricValues'][2]['value'] ?? 0),
            ];
        }
        return $out;
    }

    private function mapPages(array $report): array
    {
        $out = [];
        foreach ($report['rows'] ?? [] as $row) {
            $out[] = [
                'path' => (string) ($row['dimensionValues'][0]['value'] ?? '/'),
                'views' => (int) ($row['metricValues'][0]['value'] ?? 0),
                'avgDuration' => (int) round((float) ($row['metricValues'][1]['value'] ?? 0)),
                'bounceRate' => round(((float) ($row['metricValues'][2]['value'] ?? 0)) * 100, 1),
            ];
        }
        return $out;
    }

    private function mapLabeled(array $report, string $suffix = ''): array
    {
        $out = [];
        $total = 0;
        foreach ($report['rows'] ?? [] as $row) {
            $value = (int) ($row['metricValues'][0]['value'] ?? 0);
            $total += $value;
            $out[] = [
                'label' => (string) ($row['dimensionValues'][0]['value'] ?? '(not set)'),
                'value' => $value,
            ];
        }
        foreach ($out as &$item) {
            $item['percent'] = $total > 0 ? round(($item['value'] / $total) * 100, 1) : 0;
            if ($suffix === 'entradas') {
                $item['suffix'] = 'entradas';
            }
        }
        unset($item);
        return $out;
    }

    private function mapExits(array $report): array
    {
        $out = [];
        foreach ($report['rows'] ?? [] as $row) {
            $out[] = [
                'path' => (string) ($row['dimensionValues'][0]['value'] ?? '/'),
                'sessions' => (int) ($row['metricValues'][0]['value'] ?? 0),
                'bounceRate' => round(((float) ($row['metricValues'][1]['value'] ?? 0)) * 100, 1),
            ];
        }
        return $out;
    }

    private function mapGeo(array $report, bool $withCountry): array
    {
        $out = [];
        foreach ($report['rows'] ?? [] as $row) {
            $item = [
                'label' => (string) ($row['dimensionValues'][0]['value'] ?? '(not set)'),
                'sessions' => (int) ($row['metricValues'][0]['value'] ?? 0),
                'views' => (int) ($row['metricValues'][1]['value'] ?? 0),
            ];
            if ($withCountry) {
                $item['country'] = (string) ($row['dimensionValues'][1]['value'] ?? '');
            }
            $out[] = $item;
        }
        return $out;
    }
}
