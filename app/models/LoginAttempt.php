<?php

declare(strict_types=1);

use PDO;

final class LoginAttempt extends Model
{
    protected string $table = 'login_attempts';
    protected string $primaryKey = 'id';

    public function record(string $username, ?string $ip, bool $success): void
    {
        $stmt = $this->db->prepare('INSERT INTO login_attempts (username, ip_address, attempted_at, success) VALUES (:username, :ip, NOW(), :success)');
        $stmt->execute([
            'username' => $username,
            'ip' => $ip,
            'success' => $success ? 1 : 0,
        ]);
    }

    public function countRecentFailures(string $username, ?string $ip, int $minutes = 10): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM login_attempts WHERE username = :username AND ip_address = :ip AND success = 0 AND attempted_at >= (NOW() - INTERVAL :minutes MINUTE)');
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function clearFailures(string $username, ?string $ip): void
    {
        $stmt = $this->db->prepare('DELETE FROM login_attempts WHERE username = :username AND ip_address = :ip AND success = 0');
        $stmt->execute([
            'username' => $username,
            'ip' => $ip,
        ]);
    }
}
