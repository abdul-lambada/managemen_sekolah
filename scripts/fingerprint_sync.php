<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);
require_once $basePath . '/config/app.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/autoload.php';
require_once $basePath . '/helpers/app.php';

default_timezone_set('Asia/Jakarta');

$pdo = db();

$deviceStmt = $pdo->prepare('SELECT * FROM fingerprint_devices WHERE is_active = 1 ORDER BY nama_lokasi');
$deviceStmt->execute();
$devices = $deviceStmt->fetchAll();

if (!$devices) {
    fwrite(STDOUT, "Tidak ada perangkat aktif.\n");
    exit(0);
}

$summary = [
    'timestamp' => date('Y-m-d H:i:s'),
    'devices' => [],
    'total_success' => 0,
    'total_failure' => 0,
    'total_warning' => 0,
];

foreach ($devices as $device) {
    $deviceInfo = sprintf('%s (%s:%d)', $device['nama_lokasi'], $device['ip'], (int) $device['port']);
    fwrite(STDOUT, "Sinkronisasi perangkat: {$deviceInfo}\n");

    try {
        $entries = pullAttendanceFromDevice($device);

        if (empty($entries)) {
            logFingerprint($pdo, 'sync', 'Tidak ada data baru', 'warning', $deviceInfo);
            $summary['devices'][] = [
                'device' => $deviceInfo,
                'status' => 'warning',
                'inserted' => 0,
                'message' => 'Tidak ada data baru',
            ];
            $summary['total_warning']++;
            continue;
        }

        $inserted = persistAttendance($pdo, $entries, $device);
        logFingerprint($pdo, 'sync', "Berhasil menyimpan {$inserted} entri", 'success', $deviceInfo);
        $summary['devices'][] = [
            'device' => $deviceInfo,
            'status' => 'success',
            'inserted' => $inserted,
        ];
        $summary['total_success']++;
    } catch (Throwable $e) {
        logFingerprint($pdo, 'sync', $e->getMessage(), 'error', $deviceInfo);
        fwrite(STDERR, "[ERROR] {$deviceInfo}: {$e->getMessage()}\n");
        $summary['devices'][] = [
            'device' => $deviceInfo,
            'status' => 'error',
            'inserted' => 0,
            'message' => $e->getMessage(),
        ];
        $summary['total_failure']++;
    }
}

$summary['status'] = $summary['total_failure'] > 0 ? 'error' : ($summary['total_warning'] > 0 ? 'warning' : 'success');

updateSystemStat($pdo, 'fingerprint_sync_last_run', json_encode($summary, JSON_UNESCAPED_UNICODE));

exit($summary['total_failure'] > 0 ? 2 : 0);

/**
 * Pull attendance records from fingerprint device.
 *
 * NOTE: Implementasikan menggunakan SDK resmi ZKTeco (PHP extension atau bridge Python).
 * Fungsi ini mengembalikan array catatan dengan struktur:
 * [
 *   [
 *     'user_id' => '1',
 *     'user_name' => 'ABDULKHOLIK',
 *     'timestamp' => '2025-07-22 18:13:48',
 *     'verification_mode' => 'Fingerprint',
 *     'status' => 'Masuk',
 *   ],
 *   ...
 * ]
 */
function pullAttendanceFromDevice(array $device): array
{
    $autoload = BASE_PATH . '/vendor/autoload.php';

    if (!class_exists('ZKLib')) {
        if (file_exists($autoload)) {
            require_once $autoload;
        }
    }

    if (!class_exists('ZKLib')) {
        throw new RuntimeException('Library ZKLib belum terpasang. Jalankan composer require totemo/zklib atau tambahkan secara manual.');
    }

    $zk = new ZKLib($device['ip'], (int) $device['port']);

    if (!$zk->connect()) {
        throw new RuntimeException('Gagal terhubung ke perangkat.');
    }

    $zk->disableDevice();

    $attendance = $zk->getAttendance();

    $zk->enableDevice();
    $zk->disconnect();

    if (!is_array($attendance) || empty($attendance)) {
        return [];
    }

    return array_map(static function ($record): array {
        return [
            'user_id' => $record['uid'] ?? $record['id'] ?? null,
            'user_name' => $record['id'] ?? null,
            'timestamp' => $record['timestamp'] ?? null,
            'verification_mode' => $record['state'] ?? 'Unknown',
            'status' => ($record['type'] ?? 0) === 1 ? 'Keluar' : 'Masuk',
        ];
    }, $attendance);
}

/**
 * Persist attendance entries into tbl_kehadiran.
 */
function persistAttendance(PDO $pdo, array $entries, array $device): int
{
    if (empty($entries)) {
        return 0;
    }

    $insertStmt = $pdo->prepare('INSERT INTO tbl_kehadiran (user_id, user_name, timestamp, verification_mode, status, is_processed, created_at) VALUES (:user_id, :user_name, :timestamp, :verification_mode, :status, 0, NOW()) ON DUPLICATE KEY UPDATE timestamp = VALUES(timestamp), verification_mode = VALUES(verification_mode), status = VALUES(status)');

    $count = 0;

    foreach ($entries as $entry) {
        $insertStmt->execute([
            'user_id' => $entry['user_id'] ?? null,
            'user_name' => $entry['user_name'] ?? null,
            'timestamp' => $entry['timestamp'] ?? null,
            'verification_mode' => $entry['verification_mode'] ?? 'Unknown',
            'status' => $entry['status'] ?? 'Masuk',
        ]);
        $count++;
    }

    return $count;
}

/**
 * Write log into fingerprint_logs table.
 */
function logFingerprint(PDO $pdo, string $action, string $message, string $status, string $context): void
{
    $stmt = $pdo->prepare('INSERT INTO fingerprint_logs (action, message, status, created_at) VALUES (:action, :message, :status, NOW())');
    $stmt->execute([
        'action' => $action . ' - ' . $context,
        'message' => $message,
        'status' => $status,
    ]);
}

function updateSystemStat(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare('INSERT INTO system_stats (stat_key, stat_value, updated_at) VALUES (:key, :value, NOW()) ON DUPLICATE KEY UPDATE stat_value = :value, updated_at = NOW()');
    $stmt->execute([
        'key' => $key,
        'value' => $value,
    ]);
}
