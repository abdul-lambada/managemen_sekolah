<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);
require_once $basePath . '/config/app.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/autoload.php';
require_once $basePath . '/helpers/app.php';

date_default_timezone_set('Asia/Jakarta');

$pdo = db();

$deviceStmt = $pdo->prepare('SELECT * FROM fingerprint_devices WHERE is_active = 1 ORDER BY nama_lokasi');
$deviceStmt->execute();
$devices = $deviceStmt->fetchAll();

if (!$devices) {
    fwrite(STDOUT, "Tidak ada perangkat aktif.\n");
    exit(0);
}

function processScheduleAttendance(PDO $pdo, array $entries, array $device): array
{
    $processed = 0;

    foreach ($entries as $entry) {
        $fingerprintUid = $entry['user_id'] ?? null;
        $timestamp = $entry['timestamp'] ?? null;
        if (!$fingerprintUid || !$timestamp) {
            continue;
        }

        $guruId = mapFingerprintToGuru($pdo, (string) $fingerprintUid);
        if (!$guruId) {
            continue;
        }

        $schedule = findJadwalForEntry($pdo, $guruId, $timestamp);
        if (!$schedule) {
            continue;
        }

        $date = substr($timestamp, 0, 10);
        $time = substr($timestamp, 11, 8);
        $status = $entry['status'] ?? 'Masuk';

        $absensiId = upsertAbsensiMapel($pdo, $schedule, $date, $time, $status);
        insertAbsensiMapelLog($pdo, $absensiId, (int) $schedule['id_jadwal'], (string) $fingerprintUid, $timestamp, $status, $entry);

        $processed++;
    }

    return ['processed' => $processed];
}

function mapFingerprintToGuru(PDO $pdo, string $fingerprintUid): ?int
{
    $stmt = $pdo->prepare('SELECT id_guru FROM guru_fingerprint WHERE fingerprint_uid = :uid LIMIT 1');
    $stmt->execute(['uid' => $fingerprintUid]);
    $id = $stmt->fetchColumn();

    if ($id === false) {
        return null;
    }

    return (int) $id;
}

function findJadwalForEntry(PDO $pdo, int $guruId, string $timestamp): ?array
{
    $dateTime = new DateTime($timestamp);
    $hari = indoDayName($dateTime);
    $time = $dateTime->format('H:i:s');

    $stmt = $pdo->prepare(
        "SELECT j.* FROM jadwal_pelajaran j
         WHERE j.id_guru = :guru AND j.hari = :hari"
    );
    $stmt->execute([
        'guru' => $guruId,
        'hari' => $hari,
    ]);

    $jadwals = $stmt->fetchAll();
    if (!$jadwals) {
        return null;
    }

    foreach ($jadwals as $jadwal) {
        $start = DateTime::createFromFormat('H:i:s', $jadwal['jam_mulai']);
        $end = DateTime::createFromFormat('H:i:s', $jadwal['jam_selesai']);
        if (!$start || !$end) {
            continue;
        }

        $marginMinutes = 30;
        $startMargin = (clone $start)->modify(sprintf('-%d minutes', $marginMinutes));
        $endMargin = (clone $end)->modify(sprintf('+%d minutes', $marginMinutes));

        $timeObj = DateTime::createFromFormat('H:i:s', $time);
        if ($timeObj >= $startMargin && $timeObj <= $endMargin) {
            return $jadwal;
        }
    }

    return null;
}

function upsertAbsensiMapel(PDO $pdo, array $jadwal, string $tanggal, string $time, string $status): int
{
    $stmt = $pdo->prepare('SELECT * FROM absensi_guru_mapel WHERE id_jadwal = :id AND tanggal = :tanggal LIMIT 1');
    $stmt->execute([
        'id' => $jadwal['id_jadwal'],
        'tanggal' => $tanggal,
    ]);
    $existing = $stmt->fetch();

    $jamMulai = $jadwal['jam_mulai'];
    $lateThreshold = 10; // minutes
    $statusKehadiran = 'Hadir';

    $scheduledStart = DateTime::createFromFormat('H:i:s', $jamMulai);
    $actualTime = DateTime::createFromFormat('H:i:s', $time);
    if ($scheduledStart && $actualTime) {
        $diff = $scheduledStart->diff($actualTime);
        $minutes = (int) $diff->format('%r%i');
        if ($minutes > $lateThreshold) {
            $statusKehadiran = 'Terlambat';
        }
    }

    if ($existing) {
        $update = [
            'status_kehadiran' => $status === 'Keluar' ? ($existing['status_kehadiran'] ?? 'Hadir') : $statusKehadiran,
            'jam_masuk' => $existing['jam_masuk'],
            'jam_keluar' => $existing['jam_keluar'],
            'catatan' => $existing['catatan'],
        ];

        if ($status === 'Masuk') {
            if (empty($existing['jam_masuk']) || $time < $existing['jam_masuk']) {
                $update['jam_masuk'] = $time;
            }
        } elseif ($status === 'Keluar') {
            if (empty($existing['jam_keluar']) || $time > $existing['jam_keluar']) {
                $update['jam_keluar'] = $time;
            }
        }

        $pdo->prepare(
            'UPDATE absensi_guru_mapel SET status_kehadiran = :status, jam_masuk = :jam_masuk, jam_keluar = :jam_keluar, sumber = :sumber, updated_at = NOW() WHERE id_absensi_mapel = :id'
        )->execute([
            'status' => $update['status_kehadiran'],
            'jam_masuk' => $update['jam_masuk'],
            'jam_keluar' => $update['jam_keluar'],
            'sumber' => 'fingerprint',
            'id' => $existing['id_absensi_mapel'],
        ]);

        return (int) $existing['id_absensi_mapel'];
    }

    $pdo->prepare(
        'INSERT INTO absensi_guru_mapel (id_jadwal, tanggal, status_kehadiran, jam_masuk, jam_keluar, sumber, catatan)
         VALUES (:id_jadwal, :tanggal, :status, :jam_masuk, :jam_keluar, :sumber, :catatan)'
    )->execute([
        'id_jadwal' => $jadwal['id_jadwal'],
        'tanggal' => $tanggal,
        'status' => $status === 'Keluar' ? 'Hadir' : $statusKehadiran,
        'jam_masuk' => $status === 'Masuk' ? $time : null,
        'jam_keluar' => $status === 'Keluar' ? $time : null,
        'sumber' => 'fingerprint',
        'catatan' => null,
    ]);

    return (int) $pdo->lastInsertId();
}

function insertAbsensiMapelLog(PDO $pdo, int $absensiId, int $jadwalId, string $fingerprintUid, string $timestamp, string $status, array $entry): void
{
    $pdo->prepare(
        'INSERT INTO absensi_guru_mapel_log (id_absensi_mapel, id_jadwal, fingerprint_user_id, timestamp, status, payload)
         VALUES (:absensi, :jadwal, :uid, :timestamp, :status, :payload)'
    )->execute([
        'absensi' => $absensiId ?: null,
        'jadwal' => $jadwalId,
        'uid' => $fingerprintUid,
        'timestamp' => $timestamp,
        'status' => $status,
        'payload' => json_encode($entry, JSON_UNESCAPED_UNICODE),
    ]);
}

function indoDayName(DateTime $dateTime): string
{
    $names = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu',
    ];

    return $names[$dateTime->format('l')] ?? 'Senin';
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
                'schedule_processed' => 0,
                'message' => 'Tidak ada data baru',
            ];
            $summary['total_warning']++;
            continue;
        }

        $inserted = persistAttendance($pdo, $entries, $device);
        $scheduleResult = processScheduleAttendance($pdo, $entries, $device);

        logFingerprint(
            $pdo,
            'sync',
            "Berhasil menyimpan {$inserted} entri, terproses jadwal {$scheduleResult['processed']} entri",
            'success',
            $deviceInfo
        );
        $summary['devices'][] = [
            'device' => $deviceInfo,
            'status' => 'success',
            'inserted' => $inserted,
            'schedule_processed' => $scheduleResult['processed'],
        ];
        $summary['total_success']++;
    } catch (Throwable $e) {
        logFingerprint($pdo, 'sync', $e->getMessage(), 'error', $deviceInfo);
        fwrite(STDERR, "[ERROR] {$deviceInfo}: {$e->getMessage()}\n");
        $summary['devices'][] = [
            'device' => $deviceInfo,
            'status' => 'error',
            'inserted' => 0,
            'schedule_processed' => 0,
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
