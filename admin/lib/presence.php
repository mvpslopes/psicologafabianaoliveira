<?php

declare(strict_types=1);

/**
 * Contagem de visitantes online via heartbeat do site.
 * Considera online quem pingou nos últimos N segundos.
 */

function fo_presence_file(): string
{
    $dir = dirname(__DIR__) . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir . '/presence.json';
}

function fo_presence_ttl(): int
{
    return 90; // segundos — visitante some se não pingar
}

function fo_presence_load(): array
{
    $file = fo_presence_file();
    if (!is_file($file)) {
        return [];
    }
    $raw = file_get_contents($file);
    $data = json_decode((string) $raw, true);
    return is_array($data) ? $data : [];
}

function fo_presence_save(array $visitors): void
{
    file_put_contents(
        fo_presence_file(),
        json_encode($visitors, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    );
}

function fo_presence_prune(array $visitors, ?int $now = null): array
{
    $now = $now ?? time();
    $ttl = fo_presence_ttl();
    $clean = [];
    foreach ($visitors as $id => $meta) {
        $seen = is_array($meta) ? (int) ($meta['t'] ?? 0) : (int) $meta;
        if ($now - $seen <= $ttl) {
            $clean[$id] = is_array($meta) ? $meta : ['t' => $seen];
        }
    }
    return $clean;
}

function fo_presence_ping(string $visitorId, string $path = '/'): array
{
    $visitorId = preg_replace('/[^a-zA-Z0-9_-]/', '', $visitorId) ?? '';
    if ($visitorId === '' || strlen($visitorId) > 64) {
        return ['ok' => false, 'error' => 'visitor inválido'];
    }

    $now = time();
    $visitors = fo_presence_prune(fo_presence_load(), $now);
    $visitors[$visitorId] = [
        't' => $now,
        'path' => mb_substr($path, 0, 120),
    ];
    fo_presence_save($visitors);

    return [
        'ok' => true,
        'online' => count($visitors),
        'ttl' => fo_presence_ttl(),
    ];
}

function fo_presence_count(): int
{
    $visitors = fo_presence_prune(fo_presence_load());
    fo_presence_save($visitors);
    return count($visitors);
}

function fo_presence_snapshot(): array
{
    $visitors = fo_presence_prune(fo_presence_load());
    fo_presence_save($visitors);
    $list = [];
    foreach ($visitors as $id => $meta) {
        $list[] = [
            'id' => substr((string) $id, 0, 8),
            'path' => is_array($meta) ? ($meta['path'] ?? '/') : '/',
            'seen' => is_array($meta) ? (int) ($meta['t'] ?? 0) : (int) $meta,
        ];
    }
    return [
        'online' => count($visitors),
        'visitors' => $list,
        'ttl' => fo_presence_ttl(),
    ];
}
