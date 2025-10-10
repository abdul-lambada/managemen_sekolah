<?php

declare(strict_types=1);

final class SystemStatsController extends Controller
{
    public function index(): array
    {
        $this->requireRole('admin');

        $stmt = db()->query('SELECT stat_key, stat_value, updated_at FROM system_stats ORDER BY stat_key');
        $stats = [];
        foreach ($stmt->fetchAll() as $row) {
            $decoded = json_decode((string) $row['stat_value'], true);
            $stats[] = [
                'key' => $row['stat_key'],
                'value' => $decoded ?? $row['stat_value'],
                'updated_at' => $row['updated_at'],
            ];
        }

        $response = $this->view('system/stats', [
            'stats' => $stats,
        ], 'Statistik Sistem');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Statistik Sistem'
        ];

        return $response;
    }
}
