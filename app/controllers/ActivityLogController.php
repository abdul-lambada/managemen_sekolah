<?php

declare(strict_types=1);

final class ActivityLogController extends Controller
{
    public function index(): array
    {
        $this->requireRole('admin');

        $action = $_GET['action_filter'] ?? null;
        $userId = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int) $_GET['user_id'] : null;
        $limit = isset($_GET['limit']) ? max(10, (int) $_GET['limit']) : 100;

        $logModel = new ActivityLog();
        $logs = $logModel->filter($action, $userId, $limit);

        $response = $this->view('activity_logs/index', [
            'logs' => $logs,
            'actionFilter' => $action,
            'userId' => $userId,
            'limit' => $limit,
        ], 'Log Aktivitas');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Log Aktivitas'
        ];

        return $response;
    }
}
