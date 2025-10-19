<?php

declare(strict_types=1);

// Note: RecursiveDirectoryIterator and RecursiveIteratorIterator are used in getDirectorySize() method
// If linter complains about unused imports, these are the full class names:
// use RecursiveDirectoryIterator;
// use RecursiveIteratorIterator;

final class HealthController extends Controller
{
    public function index(): array
    {
        $this->requireRole('admin');

        $checks = [
            'cron' => $this->cronStatus(),
            'storage' => $this->storageStatus(),
            'errors' => $this->errorLogStatus(),
            'queue' => $this->whatsappQueueStatus(),
        ];

        $response = $this->view('health/index', [
            'checks' => $checks,
            'breadcrumbs' => [
                'Dashboard' => route('dashboard'),
                'Health Check'
            ],
            'alert' => flash('health_alert'),
        ], 'Monitoring Sistem');

        return $response;
    }

    private function cronStatus(): array
    {
        $pdo = db();
        $stats = [
            'fingerprint_sync_last_run',
            'whatsapp_dispatch_last_run',
        ];

        $results = [];
        foreach ($stats as $key) {
            $results[$key] = $this->parseStat($pdo, $key);
        }

        return $results;
    }

    private function storageStatus(): array
    {
        $uploadPath = BASE_PATH . '/public/uploads';
        $diskFree = @disk_free_space($uploadPath);
        $diskTotal = @disk_total_space($uploadPath);
        $usage = null;

        if ($diskFree !== false && $diskTotal !== false && $diskTotal > 0) {
            $usage = 100 - (($diskFree / $diskTotal) * 100);
        }

        $folderSize = $this->getDirectorySize($uploadPath);

        return [
            'path' => $uploadPath,
            'disk_free' => $diskFree,
            'disk_total' => $diskTotal,
            'disk_usage_percent' => $usage,
            'folder_size' => $folderSize,
        ];
    }

    private function errorLogStatus(): array
    {
        $logPath = BASE_PATH . '/storage/logs/php-error.log';
        $recentLines = $this->tailFile($logPath, 20);

        return [
            'path' => $logPath,
            'exists' => file_exists($logPath),
            'recent' => $recentLines,
        ];
    }

    private function whatsappQueueStatus(): array
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT COUNT(*) FROM whatsapp_logs WHERE status = "pending"');
        $pending = (int) $stmt->fetchColumn();

        $stmt = $pdo->query('SELECT COUNT(*) FROM whatsapp_logs WHERE status = "failed"');
        $failed = (int) $stmt->fetchColumn();

        return [
            'pending' => $pending,
            'failed' => $failed,
        ];
    }

    private function parseStat(PDO $pdo, string $key): array
    {
        $stmt = $pdo->prepare('SELECT stat_value, updated_at FROM system_stats WHERE stat_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();

        if (!$row) {
            return [
                'status' => 'unknown',
                'label' => 'Belum ada data',
                'meta' => [],
            ];
        }

        $decoded = json_decode($row['stat_value'], true);
        if (!is_array($decoded)) {
            return [
                'status' => 'error',
                'label' => 'Data tidak valid',
                'meta' => [],
            ];
        }

        return [
            'status' => $decoded['status'] ?? 'unknown',
            'label' => $this->statusLabel($decoded['status'] ?? 'unknown'),
            'meta' => $decoded,
            'updated_at' => $row['updated_at'],
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'success' => 'Normal',
            'warning' => 'Perlu Perhatian',
            'error' => 'Gagal',
            default => 'Tidak Diketahui',
        };
    }

    private function getDirectorySize(string $path): ?int
    {
        if (!is_dir($path)) {
            return null;
        }

        $size = 0;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    private function tailFile(string $path, int $lines = 20): array
    {
        if (!is_file($path)) {
            return [];
        }

        $buffer = '';
        $fp = fopen($path, 'rb');
        if ($fp === false) {
            return [];
        }

        $position = -1;
        $lineCount = 0;
        $output = [];

        while ($lineCount < $lines && fseek($fp, $position, SEEK_END) === 0) {
            $char = fgetc($fp);
            if ($char === "\n") {
                $line = strrev($buffer);
                $output[] = $line;
                $buffer = '';
                $lineCount++;
            } elseif ($char !== false) {
                $buffer .= $char;
            }
            $position--;
        }

        if ($buffer !== '') {
            $output[] = strrev($buffer);
        }

        fclose($fp);

        return array_reverse($output);
    }
}
