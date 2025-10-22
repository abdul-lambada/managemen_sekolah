<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);
require_once $basePath . '/config/app.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/autoload.php';
require_once $basePath . '/helpers/app.php';

date_default_timezone_set('Asia/Jakarta');

/**
 * Output helpers that work in CLI and non-CLI contexts.
 */
function out(string $message): void
{
    if (PHP_SAPI === 'cli') {
        $h = @fopen('php://stdout', 'w');
        if (is_resource($h)) { fwrite($h, $message); fclose($h); return; }
    }
    echo $message;
}

function err(string $message): void
{
    if (PHP_SAPI === 'cli') {
        $h = @fopen('php://stderr', 'w');
        if (is_resource($h)) { fwrite($h, $message); fclose($h); return; }
    }
    error_log(trim($message));
}

$pdo = db();

/**
 * Ensure unique index on absensi_guru_mapel(id_jadwal, tanggal) to prevent duplicates.
 */
function ensureAbsensiMapelUniqueIndex(PDO $pdo): void
{
    try {
        $stmt = $pdo->prepare("SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'absensi_guru_mapel' AND index_name = 'uniq_jadwal_tanggal'");
        $stmt->execute();
        $exists = (int) $stmt->fetchColumn() > 0;
        if (!$exists) {
            $pdo->exec("ALTER TABLE absensi_guru_mapel ADD UNIQUE KEY uniq_jadwal_tanggal (id_jadwal, tanggal)");
        }
    } catch (Throwable $e) {
        // ignore if lacking privilege; system will still function without the index
    }
}

/**
 * Map fingerprint UID to siswa_id if mapping table exists.
 */
function mapFingerprintToSiswa(PDO $pdo, string $fingerprintUid): ?int
{
    try {
        $existsStmt = $pdo->prepare("SELECT COUNT(1) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'siswa_fingerprint'");
        $existsStmt->execute();
        if ((int) $existsStmt->fetchColumn() === 0) {
            return null; // mapping belum tersedia
        }

        $stmt = $pdo->prepare('SELECT id_siswa FROM siswa_fingerprint WHERE fingerprint_uid = :uid LIMIT 1');
        $stmt->execute(['uid' => $fingerprintUid]);
        $id = $stmt->fetchColumn();
        if ($id === false) {
            return null;
        }
        return (int) $id;
    } catch (Throwable $e) {
        return null;
    }
}

function ensureDailyAttendanceStudentTable(PDO $pdo): void
{
    static $ensured = false;
    if ($ensured) return;

    $sql = "CREATE TABLE IF NOT EXISTS kehadiran_siswa_harian (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        siswa_id INT UNSIGNED NOT NULL,
        tanggal DATE NOT NULL,
        check_in_pagi TIME NULL,
        check_out_sore TIME NULL,
        sumber VARCHAR(50) DEFAULT 'fingerprint',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_siswa_tanggal (siswa_id, tanggal),
        KEY siswa_id_idx (siswa_id),
        KEY tanggal_idx (tanggal)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    try {
        $pdo->exec($sql);
        $ensured = true;
    } catch (Throwable $e) {
        // ignore
    }
}

function upsertDailyAttendanceStudent(PDO $pdo, int $siswaId, string $date, array $fields): void
{
    ensureDailyAttendanceStudentTable($pdo);
    $stmt = $pdo->prepare('SELECT id, check_in_pagi, check_out_sore FROM kehadiran_siswa_harian WHERE siswa_id = :sid AND tanggal = :tanggal LIMIT 1');
    $stmt->execute(['sid' => $siswaId, 'tanggal' => $date]);
    $existing = $stmt->fetch();

    if ($existing) {
        $checkIn = $existing['check_in_pagi'];
        $checkOut = $existing['check_out_sore'];
        if (isset($fields['check_in_pagi'])) {
            if (empty($checkIn) || $fields['check_in_pagi'] < $checkIn) {
                $checkIn = $fields['check_in_pagi'];
            }
        }
        if (isset($fields['check_out_sore'])) {
            if (empty($checkOut) || $fields['check_out_sore'] > $checkOut) {
                $checkOut = $fields['check_out_sore'];
            }
        }
        $pdo->prepare('UPDATE kehadiran_siswa_harian SET check_in_pagi = :in, check_out_sore = :out, updated_at = NOW() WHERE id = :id')
            ->execute(['in' => $checkIn, 'out' => $checkOut, 'id' => $existing['id']]);
        return;
    }

    $pdo->prepare('INSERT INTO kehadiran_siswa_harian (siswa_id, tanggal, check_in_pagi, check_out_sore, sumber) VALUES (:sid, :tanggal, :in, :out, :sumber)')
        ->execute([
            'sid' => $siswaId,
            'tanggal' => $date,
            'in' => $fields['check_in_pagi'] ?? null,
            'out' => $fields['check_out_sore'] ?? null,
            'sumber' => 'fingerprint',
        ]);
}

function processDailyAttendanceStudents(PDO $pdo, array $entries): int
{
    if (empty($entries)) return 0;
    $win = getAttendanceWindows();
    $processed = 0;
    foreach ($entries as $entry) {
        $uid = $entry['user_id'] ?? null;
        $ts = $entry['timestamp'] ?? null;
        if (!$uid || !$ts) continue;

        $siswaId = mapFingerprintToSiswa($pdo, (string)$uid);
        if (!$siswaId) continue; // skip jika tidak terpetakan ke siswa

        $date = substr($ts, 0, 10);
        $time = substr($ts, 11, 8);

        if (withinMorningWindow($time, $win)) {
            upsertDailyAttendanceStudent($pdo, $siswaId, $date, ['check_in_pagi' => $time]);
            $processed++;
            continue;
        }

        if (withinEveningWindow($time, $win)) {
            upsertDailyAttendanceStudent($pdo, $siswaId, $date, ['check_out_sore' => $time]);
            $processed++;
            continue;
        }
    }
    return $processed;
}

ensureAbsensiMapelUniqueIndex($pdo);

$deviceStmt = $pdo->prepare('SELECT * FROM fingerprint_devices WHERE is_active = 1 ORDER BY nama_lokasi');
$deviceStmt->execute();
$devices = $deviceStmt->fetchAll();

if (!$devices) {
    out("Tidak ada perangkat aktif.\n");
    exit(0);
}

function processScheduleAttendance(PDO $pdo, array $entries, array $device): array
{
    $processed = 0;
    $lateNotifications = [];

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

        $result = upsertAbsensiMapel($pdo, $schedule, $date, $time, $status);
        insertAbsensiMapelLog($pdo, $result['id'], (int) $schedule['id_jadwal'], (string) $fingerprintUid, $timestamp, $status, $entry);

        if ($result['should_notify_late'] && $result['minutes_late'] > 0) {
            $lateNotifications[] = [
                'guru_id' => $guruId,
                'schedule' => $schedule,
                'timestamp' => $timestamp,
                'minutes_late' => $result['minutes_late'],
            ];
        }

        $processed++;
    }

    return [
        'processed' => $processed,
        'late_notifications' => $lateNotifications,
    ];
}

function queueLateNotification(PDO $pdo, array $data): void
{
    $attendanceDate = substr($data['timestamp'], 0, 10);

    if (hasLateNotification($pdo, $data['guru_id'], $attendanceDate)) {
        return;
    }

    $guru = fetchGuruContact($pdo, $data['guru_id']);
    if (!$guru || empty($guru['phone'])) {
        return;
    }

    $template = getWhatsAppTemplate($pdo, 'attendance_late');
    if (!$template) {
        return;
    }

    $timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $data['timestamp']);
    $formattedDate = $timestamp ? $timestamp->format('d-m-Y') : $attendanceDate;
    $formattedTime = $timestamp ? $timestamp->format('H:i') : substr($data['timestamp'], 11, 5);

    $message = renderTemplateBody($template['body'], [
        'nama' => $guru['nama_guru'],
        'tanggal' => $formattedDate,
        'waktu' => $formattedTime,
        'menit_terlambat' => (string) $data['minutes_late'],
    ]);

    $pdo->prepare(
        'INSERT INTO whatsapp_logs (phone_number, message_type, template_name, message, status, created_at)
         VALUES (:phone, :type, :template, :message, :status, NOW())'
    )->execute([
        'phone' => $guru['phone'],
        'type' => 'text',
        'template' => 'attendance_late',
        'message' => $message,
        'status' => 'pending',
    ]);

    $logId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'INSERT INTO whatsapp_automation_logs (user_id, user_type, attendance_status, notification_type, recipient_phone, recipient_type, template_used, message_sent, whatsapp_log_id, attendance_date, created_at)
         VALUES (:user_id, :user_type, :attendance_status, :notification_type, :recipient_phone, :recipient_type, :template_used, 0, :log_id, :attendance_date, NOW())'
    );
    $stmt->execute([
        'user_id' => $data['guru_id'],
        'user_type' => 'guru',
        'attendance_status' => 'Terlambat',
        'notification_type' => 'late_notice',
        'recipient_phone' => $guru['phone'],
        'recipient_type' => 'user',
        'template_used' => 'attendance_late',
        'log_id' => $logId,
        'attendance_date' => $attendanceDate,
    ]);
}

function hasLateNotification(PDO $pdo, int $guruId, string $date): bool
{
    $stmt = $pdo->prepare(
        'SELECT id FROM whatsapp_automation_logs WHERE user_type = :type AND user_id = :user AND notification_type = :notification AND attendance_date = :date LIMIT 1'
    );
    $stmt->execute([
        'type' => 'guru',
        'user' => $guruId,
        'notification' => 'late_notice',
        'date' => $date,
    ]);

    return (bool) $stmt->fetchColumn();
}

function fetchGuruContact(PDO $pdo, int $guruId): ?array
{
    $stmt = $pdo->prepare('SELECT nama_guru, phone FROM guru WHERE id_guru = :id LIMIT 1');
    $stmt->execute(['id' => $guruId]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    $row['phone'] = trim((string) ($row['phone'] ?? ''));

    return $row;
}

function getWhatsAppTemplate(PDO $pdo, string $name): ?array
{
    static $cache = [];

    if (isset($cache[$name])) {
        return $cache[$name];
    }

    $stmt = $pdo->prepare('SELECT * FROM whatsapp_message_templates WHERE name = :name AND is_active = 1 LIMIT 1');
    $stmt->execute(['name' => $name]);
    $template = $stmt->fetch();

    if ($template) {
        $cache[$name] = $template;
    }

    return $template ?: null;
}

function renderTemplateBody(string $body, array $variables): string
{
    $message = $body;

    foreach ($variables as $key => $value) {
        $message = str_replace('{{' . $key . '}}', (string) $value, $message);
    }

    return $message;
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

function upsertAbsensiMapel(PDO $pdo, array $jadwal, string $tanggal, string $time, string $status): array
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
    $minutesLate = 0;

    $scheduledStart = DateTime::createFromFormat('H:i:s', $jamMulai);
    $actualTime = DateTime::createFromFormat('H:i:s', $time);
    if ($scheduledStart && $actualTime) {
        $diff = $scheduledStart->diff($actualTime);
        $minutes = (int) $diff->format('%r%i');
        if ($minutes > $lateThreshold) {
            $statusKehadiran = 'Terlambat';
            $minutesLate = $minutes;
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

        $shouldNotify = $status === 'Masuk' && $update['status_kehadiran'] === 'Terlambat' && $minutesLate > 0;

        return [
            'id' => (int) $existing['id_absensi_mapel'],
            'should_notify_late' => $shouldNotify,
            'minutes_late' => $minutesLate,
        ];
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

    $newId = (int) $pdo->lastInsertId();

    $shouldNotify = $status === 'Masuk' && $statusKehadiran === 'Terlambat' && $minutesLate > 0;

    return [
        'id' => $newId,
        'should_notify_late' => $shouldNotify,
        'minutes_late' => $minutesLate,
    ];
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

/**
 * Daily attendance processing (pagi/pulang) for teachers.
 */
function getAttendanceWindows(): array
{
    // Default windows
    $defaults = [
        'morning_start' => '05:00:00',
        'morning_end' => '09:00:00',
        'evening_start' => '14:00:00',
        'evening_end' => '23:59:59',
    ];

    try {
        $settings = app_settings();
        $overrides = [
            'morning_start' => $settings['attendance_morning_start'] ?? null,
            'morning_end' => $settings['attendance_morning_end'] ?? null,
            'evening_start' => $settings['attendance_evening_start'] ?? null,
            'evening_end' => $settings['attendance_evening_end'] ?? null,
        ];

        foreach ($overrides as $k => $v) {
            if (is_string($v) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $v)) {
                $defaults[$k] = $v;
            }
        }
    } catch (Throwable $e) {
        // fallback to defaults
    }

    return $defaults;
}

function ensureDailyAttendanceTable(PDO $pdo): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    $sql = "CREATE TABLE IF NOT EXISTS kehadiran_guru_harian (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        guru_id INT UNSIGNED NOT NULL,
        tanggal DATE NOT NULL,
        check_in_pagi TIME NULL,
        check_out_sore TIME NULL,
        sumber VARCHAR(50) DEFAULT 'fingerprint',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_guru_tanggal (guru_id, tanggal),
        KEY guru_id_idx (guru_id),
        KEY tanggal_idx (tanggal)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    $pdo->exec($sql);
    $ensured = true;
}

function withinMorningWindow(string $time, array $win): bool
{
    return $time >= $win['morning_start'] && $time <= $win['morning_end'];
}

function withinEveningWindow(string $time, array $win): bool
{
    return $time >= $win['evening_start'] && $time <= $win['evening_end'];
}

function upsertDailyAttendance(PDO $pdo, int $guruId, string $date, array $fields): void
{
    ensureDailyAttendanceTable($pdo);

    $stmt = $pdo->prepare('SELECT id, check_in_pagi, check_out_sore FROM kehadiran_guru_harian WHERE guru_id = :guru AND tanggal = :tanggal LIMIT 1');
    $stmt->execute(['guru' => $guruId, 'tanggal' => $date]);
    $existing = $stmt->fetch();

    if ($existing) {
        $checkIn = $existing['check_in_pagi'];
        $checkOut = $existing['check_out_sore'];

        if (isset($fields['check_in_pagi'])) {
            if (empty($checkIn) || $fields['check_in_pagi'] < $checkIn) {
                $checkIn = $fields['check_in_pagi'];
            }
        }
        if (isset($fields['check_out_sore'])) {
            if (empty($checkOut) || $fields['check_out_sore'] > $checkOut) {
                $checkOut = $fields['check_out_sore'];
            }
        }

        $pdo->prepare('UPDATE kehadiran_guru_harian SET check_in_pagi = :in, check_out_sore = :out, updated_at = NOW() WHERE id = :id')
            ->execute([
                'in' => $checkIn,
                'out' => $checkOut,
                'id' => $existing['id'],
            ]);
        return;
    }

    $pdo->prepare('INSERT INTO kehadiran_guru_harian (guru_id, tanggal, check_in_pagi, check_out_sore, sumber) VALUES (:guru, :tanggal, :in, :out, :sumber)')
        ->execute([
            'guru' => $guruId,
            'tanggal' => $date,
            'in' => $fields['check_in_pagi'] ?? null,
            'out' => $fields['check_out_sore'] ?? null,
            'sumber' => 'fingerprint',
        ]);
}

function processDailyAttendance(PDO $pdo, array $entries): int
{
    if (empty($entries)) {
        return 0;
    }

    $win = getAttendanceWindows();
    $processed = 0;

    foreach ($entries as $entry) {
        $uid = $entry['user_id'] ?? null;
        $ts = $entry['timestamp'] ?? null;
        if (!$uid || !$ts) {
            continue;
        }

        $guruId = mapFingerprintToGuru($pdo, (string) $uid);
        if (!$guruId) {
            continue;
        }

        // Normalize to local date/time
        $date = substr($ts, 0, 10);
        $time = substr($ts, 11, 8);

        if (withinMorningWindow($time, $win)) {
            upsertDailyAttendance($pdo, $guruId, $date, ['check_in_pagi' => $time]);
            $processed++;
            continue;
        }

        if (withinEveningWindow($time, $win)) {
            upsertDailyAttendance($pdo, $guruId, $date, ['check_out_sore' => $time]);
            $processed++;
            continue;
        }
    }

    return $processed;
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
    'late_notifications' => 0,
];

foreach ($devices as $device) {
    $deviceInfo = sprintf('%s (%s:%d)', $device['nama_lokasi'], $device['ip'], (int) $device['port']);
    out("Sinkronisasi perangkat: {$deviceInfo}\n");

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
        // Process daily (pagi/pulang) recap for guru
        $dailyProcessed = processDailyAttendance($pdo, $entries);
        // Process daily (pagi/pulang) recap for siswa
        $studentDailyProcessed = processDailyAttendanceStudents($pdo, $entries);
        // Process schedule-based (per jam pelajaran)
        $scheduleResult = processScheduleAttendance($pdo, $entries, $device);

        if (!empty($scheduleResult['late_notifications'])) {
            foreach ($scheduleResult['late_notifications'] as $notification) {
                queueLateNotification($pdo, $notification);
            }
            $lateCount = count($scheduleResult['late_notifications']);
            $summary['late_notifications'] += $lateCount;
        } else {
            $lateCount = 0;
        }

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
            'daily_processed' => $dailyProcessed,
            'student_daily_processed' => $studentDailyProcessed,
            'schedule_processed' => $scheduleResult['processed'],
            'late_notices' => $lateCount,
        ];
        $summary['total_success']++;
    } catch (Throwable $e) {
        logFingerprint($pdo, 'sync', $e->getMessage(), 'error', $deviceInfo);
        err("[ERROR] {$deviceInfo}: {$e->getMessage()}\n");
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
    // Guard PHP version for composer packages requiring >=8.1
    if (PHP_VERSION_ID < 80100) {
        throw new RuntimeException('PHP 8.1+ diperlukan untuk modul ZKLib. Versi saat ini: ' . PHP_VERSION . '. Mohon jalankan script pada PHP 8.1+ atau sediakan bridge eksternal.');
    }

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
    $stmt = $pdo->prepare('INSERT INTO system_stats (stat_key, stat_value, updated_at) VALUES (:key1, :value1, NOW()) ON DUPLICATE KEY UPDATE stat_value = :value2, updated_at = NOW()');
    $stmt->execute([
        'key1' => $key,
        'value1' => $value,
        'value2' => $value,
    ]);
}
