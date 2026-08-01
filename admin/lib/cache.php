<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function fo_cache_path(string $key): string
{
    $dir = (string) fo_config('cache_dir');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . preg_replace('/[^a-zA-Z0-9_-]/', '_', $key) . '.json';
}

function fo_cache_get(string $key): ?array
{
    $path = fo_cache_path($key);
    if (!is_file($path)) {
        return null;
    }

    $ttl = (int) fo_config('cache_ttl', 300);
    if (time() - filemtime($path) > $ttl) {
        return null;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        return null;
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function fo_cache_set(string $key, array $data): void
{
    $path = fo_cache_path($key);
    file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
}
