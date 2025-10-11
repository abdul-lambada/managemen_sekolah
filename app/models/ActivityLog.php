<?php

declare(strict_types=1);

use PDO;

final class ActivityLog extends Model
{
    protected string $table = 'activity_logs';
    protected string $primaryKey = 'id';

    public function record(?int $userId, string $action, ?string $description = null): void
    {
        $stmt = $this->db->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (:user_id, :action, :description, :ip_address, :user_agent)');
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    public function latest(int $limit = 20): array
    {
        $stmt = $this->db->prepare('SELECT al.*, u.name AS user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function filter(?string $action = null, ?int $userId = null, int $limit = 100): array
    {
        $sql = 'SELECT al.*, u.name AS user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id';
        $conditions = [];
        $params = [];

        if ($action !== null && $action !== '') {
            $conditions[] = 'al.action LIKE :action';
            $params['action'] = '%' . $action . '%';
        }

        if ($userId !== null) {
            $conditions[] = 'al.user_id = :user_id';
            $params['user_id'] = $userId;
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY al.created_at DESC LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
