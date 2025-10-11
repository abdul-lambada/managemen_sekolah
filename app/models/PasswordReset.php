<?php

declare(strict_types=1);

final class PasswordReset extends Model
{
    protected string $table = 'password_resets';
    protected string $primaryKey = 'id';

    public function createToken(int $userId, string $selector, string $tokenHash, DateTimeImmutable $expiresAt): void
    {
        $this->deleteByUser($userId);

        $stmt = $this->db->prepare('INSERT INTO password_resets (user_id, selector, token_hash, expires_at) VALUES (:user_id, :selector, :token_hash, :expires_at)');
        $stmt->execute([
            'user_id' => $userId,
            'selector' => $selector,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function findValidBySelector(string $selector): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM password_resets WHERE selector = :selector LIMIT 1');
        $stmt->execute(['selector' => $selector]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        if (new DateTimeImmutable($row['expires_at']) < new DateTimeImmutable()) {
            $this->deleteById((int) $row['id']);
            return null;
        }

        return $row;
    }

    public function deleteById(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM password_resets WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function deleteByUser(int $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }
}
