<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);
define('BASE_PATH', $basePath);

date_default_timezone_set('Asia/Jakarta');

$config = [
    'APP_NAME' => 'Manajemen Sekolah',
    'APP_URL' => 'http://localhost/managemen_sekolah'
];

$envFile = $basePath . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    $parsed = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if (is_array($parsed)) {
        $upper = array_change_key_case($parsed, CASE_UPPER);
        $config = array_merge($config, $upper);
    }
}

if (!defined('APP_NAME')) {
    define('APP_NAME', $config['APP_NAME']);
}

if (!defined('APP_URL')) {
    define('APP_URL', rtrim($config['APP_URL'], '/'));
}
