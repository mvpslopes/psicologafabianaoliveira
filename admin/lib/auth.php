<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function fo_login(string $username, string $password): bool
{
    $expectedUser = (string) fo_config('username');
    $hash = (string) fo_config('password_hash');

    if (mb_strtolower($expectedUser) !== mb_strtolower(trim($username))) {
        return false;
    }

    if (!password_verify($password, $hash)) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['fo_admin_auth'] = true;
    $_SESSION['fo_admin_user'] = $expectedUser;
    $_SESSION['fo_admin_name'] = fo_config('display_name', $expectedUser);
    return true;
}

function fo_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function fo_change_password(string $current, string $newPassword): array
{
    $hash = (string) fo_config('password_hash');
    if (!password_verify($current, $hash)) {
        return ['ok' => false, 'error' => 'Senha atual incorreta.'];
    }

    if (strlen($newPassword) < 8) {
        return ['ok' => false, 'error' => 'A nova senha deve ter pelo menos 8 caracteres.'];
    }

    $configFile = __DIR__ . '/../config.php';
    if (!is_file($configFile) || !is_writable($configFile)) {
        return ['ok' => false, 'error' => 'Não foi possível gravar a nova senha (config.php).'];
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $config = [
        'username' => fo_config('username'),
        'password_hash' => $newHash,
        'display_name' => fo_config('display_name'),
        'ga4_property_id' => fo_config('ga4_property_id'),
        'ga4_measurement_id' => fo_config('ga4_measurement_id'),
        'service_account_json' => fo_config('service_account_json'),
        'cache_ttl' => fo_config('cache_ttl', 300),
        'cache_dir' => fo_config('cache_dir'),
        'site_url' => fo_config('site_url'),
    ];

    $export = "<?php\n/**\n * FO Psicologia — Configuração da área interna\n */\nreturn " . var_export($config, true) . ";\n";
    if (file_put_contents($configFile, $export) === false) {
        return ['ok' => false, 'error' => 'Falha ao salvar a nova senha.'];
    }

    return ['ok' => true];
}
