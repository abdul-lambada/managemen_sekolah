<?php

declare(strict_types=1);

/**
 * Returns a shared PDO connection using credentials from .env or defaults.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    $config = [
        'DB_HOST' => '127.0.0.1',
        'DB_PORT' => '3306',
        'DB_NAME' => 'dpgwgcvf_salassika',
        'DB_USER' => 'root',
        'DB_PASS' => ''
    ];

    if (file_exists($envPath)) {
        $parsed = parse_ini_file($envPath, false, INI_SCANNER_TYPED);
        if (is_array($parsed)) {
            $config = array_merge($config, array_change_key_case($parsed, CASE_UPPER));
        }
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $config['DB_HOST'],
        $config['DB_PORT'],
        $config['DB_NAME']
    );

    try {
        $pdo = new PDO($dsn, $config['DB_USER'], (string) $config['DB_PASS'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }

    return $pdo;
}
