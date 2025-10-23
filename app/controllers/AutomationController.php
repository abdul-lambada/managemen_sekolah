<?php

declare(strict_types=1);

class AutomationController extends Controller
{
    public function index(): array
    {
        $this->requireRole('admin');

        $logs = [
            'whatsapp' => $this->getLog('whatsapp_dispatch_last_run'),
            'fingerprint' => $this->getLog('fingerprint_sync_last_run'),
        ];

        return $this->view('automation/index', [
            'logs' => $logs,
            'csrfToken' => ensure_csrf_token(),
            'alert' => flash('automation_alert'),
        ], 'Automasi');
    }

    public function trigger(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('automation_alert', 'Token tidak valid.', 'danger');
            redirect(route('automation'));
        }

        $action = $_POST['action'] ?? '';
        $cmd = null;

        // Use configured PHP CLI path directly to avoid open_basedir checks
        $phpBinaryPath = (defined('PHP_CLI_BIN') && is_string(PHP_CLI_BIN) && PHP_CLI_BIN !== '') ? PHP_CLI_BIN : PHP_BINARY;
        $phpBinary = escapeshellarg($phpBinaryPath);

        if ($action === 'whatsapp') {
            $script = escapeshellarg(BASE_PATH . '/scripts/whatsapp_dispatch.php');
            $cmd = sprintf('%s %s --limit=50', $phpBinary, $script);
        } elseif ($action === 'fingerprint') {
            $script = escapeshellarg(BASE_PATH . '/scripts/fingerprint_sync.php');
            $cmd = sprintf('%s %s', $phpBinary, $script);
        } else {
            flash('automation_alert', 'Aksi tidak dikenal.', 'danger');
            redirect(route('automation'));
        }

        try {
            $output = $this->runCommand($cmd);
            flash('automation_alert', "Perintah berhasil dijalankan. Output: {$output}", 'success');
        } catch (RuntimeException $e) {
            flash('automation_alert', 'Gagal menjalankan perintah: ' . $e->getMessage(), 'danger');
        }

        redirect(route('automation'));
    }

    private function getLog(string $key): array
    {
        $stmt = db()->prepare('SELECT stat_value, updated_at FROM system_stats WHERE stat_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();

        if (!$row) {
            return [
                'label' => 'Belum pernah dijalankan',
                'badge' => 'secondary',
                'meta' => [],
            ];
        }

        $decoded = json_decode($row['stat_value'], true);

        if (!is_array($decoded)) {
            return [
                'label' => 'Data tidak valid',
                'badge' => 'danger',
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

        return [
            'label' => $this->statusLabel($status),
            'badge' => $badgeMap[$status] ?? 'secondary',
            'meta' => $decoded,
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'success' => 'Berhasil',
            'warning' => 'Perlu perhatian',
            'error' => 'Gagal',
            default => 'Tidak diketahui',
        };
    }

    private function runCommand(string $command): string
    {
        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $options = [];
        if (PHP_OS_FAMILY === 'Windows') {
            $options['bypass_shell'] = true;
        }

        $process = proc_open($command, $descriptorSpec, $pipes, BASE_PATH, null, $options);

        if (!is_resource($process)) {
            throw new RuntimeException('Tidak dapat membuat proses.');
        }

        $output = stream_get_contents($pipes[1]) ?: '';
        $error = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException(trim($error) ?: 'Perintah gagal dijalankan.');
        }

        return trim($output);
    }

    private function resolvePhpBinary(): string
    {
        // 1) If PHP_CLI_BIN is configured, always use it (avoid any filesystem checks to prevent open_basedir warnings)
        if (defined('PHP_CLI_BIN') && is_string(PHP_CLI_BIN) && PHP_CLI_BIN !== '') {
            return PHP_CLI_BIN;
        }

        // 2) Windows: prefer PHP_BINDIR/php.exe, else typical XAMPP path. Avoid is_file checks to prevent warnings.
        if (PHP_OS_FAMILY === 'Windows') {
            $candidate = rtrim(PHP_BINDIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'php.exe';
            return $candidate ?: 'C:\\xampp\\php\\php.exe';
        }

        // 3) Default: current binary (no external path probing to avoid open_basedir issues)
        return PHP_BINARY;
    }

    private function assertPost(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan');
        }
    }
}
