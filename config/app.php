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

if (!defined('PHP_CLI_BIN')) {
    $candidate = $config['PHP_CLI_BIN'] ?? null;
    if (is_string($candidate) && $candidate !== '' && @is_file($candidate)) {
        define('PHP_CLI_BIN', $candidate);
    } else {
        if (PHP_OS_FAMILY === 'Windows') {
            $win = rtrim(PHP_BINDIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'php.exe';
            define('PHP_CLI_BIN', @is_file($win) ? $win : 'C:\\xampp\\php\\php.exe');
        } else {
            $bins = [
                '/opt/plesk/php/8.3/bin/php',
                '/opt/plesk/php/8.2/bin/php',
                '/opt/plesk/php/8.1/bin/php',
                '/opt/cpanel/ea-php83/root/usr/bin/php',
                '/opt/cpanel/ea-php82/root/usr/bin/php',
                '/opt/cpanel/ea-php81/root/usr/bin/php',
                '/usr/bin/php8.3',
                '/usr/bin/php8.2',
                '/usr/bin/php8.1',
                '/usr/local/bin/php8.3',
                '/usr/local/bin/php8.2',
                '/usr/local/bin/php8.1',
                '/usr/bin/php',
                '/usr/local/bin/php',
            ];
            $found = null;
            foreach ($bins as $b) {
                if (@is_file($b) && @is_executable($b)) { $found = $b; break; }
            }
            define('PHP_CLI_BIN', $found ?: PHP_BINARY);
        }
    }
}
