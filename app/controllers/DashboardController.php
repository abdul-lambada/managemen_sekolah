<?php

declare(strict_types=1);

class DashboardController extends Controller
{
    public function index(): array
    {
        $pdo = db();

        $stats = [
            'guru' => (int) $pdo->query('SELECT COUNT(*) FROM guru')->fetchColumn(),
            'siswa' => (int) $pdo->query('SELECT COUNT(*) FROM siswa')->fetchColumn(),
            'kelas' => (int) $pdo->query('SELECT COUNT(*) FROM kelas')->fetchColumn(),
            'jurusan' => (int) $pdo->query('SELECT COUNT(*) FROM jurusan')->fetchColumn(),
            'absensi_guru' => (int) $pdo->query('SELECT COUNT(*) FROM absensi_guru')->fetchColumn(),
            'absensi_siswa' => (int) $pdo->query('SELECT COUNT(*) FROM absensi_siswa')->fetchColumn(),
        ];

        $recentAttendance = $pdo->query('SELECT user_name, `timestamp`, status FROM tbl_kehadiran ORDER BY created_at DESC LIMIT 5')->fetchAll();
        $recentWhatsapp = $pdo->query('SELECT phone_number, status, created_at, message_type FROM whatsapp_logs ORDER BY created_at DESC LIMIT 5')->fetchAll();
        $systemStats = $pdo->query('SELECT stat_key, stat_value, updated_at FROM system_stats ORDER BY updated_at DESC LIMIT 6')->fetchAll();

        $automationStatus = [
            'whatsapp' => $this->fetchAutomationStatus($pdo, 'whatsapp_dispatch_last_run'),
            'fingerprint' => $this->fetchAutomationStatus($pdo, 'fingerprint_sync_last_run'),
        ];

        $attendancePerStatus = $pdo->query("SELECT status_kehadiran, COUNT(*) AS total FROM absensi_siswa GROUP BY status_kehadiran")->fetchAll();
        $attendanceChart = [
            'labels' => array_column($attendancePerStatus, 'status_kehadiran'),
            'data' => array_map('intval', array_column($attendancePerStatus, 'total')),
        ];

        $whatsappSummary = $pdo->query("SELECT status, COUNT(*) AS total FROM whatsapp_logs GROUP BY status")->fetchAll();
        $whatsappChart = [
            'labels' => array_column($whatsappSummary, 'status'),
            'data' => array_map('intval', array_column($whatsappSummary, 'total')),
        ];

        return $this->view('dashboard/index', [
            'stats' => $stats,
            'recentAttendance' => $recentAttendance,
            'recentWhatsapp' => $recentWhatsapp,
            'systemStats' => $systemStats,
            'attendanceChart' => $attendanceChart,
            'whatsappChart' => $whatsappChart,
            'automationStatus' => $automationStatus,
        ], 'Dashboard');
    }

    private function fetchAutomationStatus(PDO $pdo, string $key): array
    {
        $stmt = $pdo->prepare('SELECT stat_value, updated_at FROM system_stats WHERE stat_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();

        if (!$row) {
            return [
                'status' => 'unknown',
                'badge' => 'secondary',
                'label' => 'Belum ada data',
                'updated_at' => null,
                'meta' => [],
            ];
        }

        $decoded = json_decode($row['stat_value'], true);

        if (!is_array($decoded)) {
            return [
                'status' => 'unknown',
                'badge' => 'secondary',
                'label' => 'Data tidak valid',
                'updated_at' => $row['updated_at'],
                'meta' => [],
            ];
        }

        $status = $decoded['status'] ?? 'unknown';
        $badgeMap = [
            'success' => 'success',
            'warning' => 'warning',
            'error' => 'danger',
            'unknown' => 'secondary',
        ];

        $labelMap = [
            'success' => 'Berhasil',
            'warning' => 'Perlu perhatian',
            'error' => 'Gagal',
            'unknown' => 'Tidak diketahui',
        ];

        return [
            'status' => $status,
            'badge' => $badgeMap[$status] ?? 'secondary',
            'label' => $labelMap[$status] ?? 'Tidak diketahui',
            'updated_at' => $decoded['timestamp'] ?? $row['updated_at'],
            'meta' => $decoded,
        ];
    }
}
