<?php

declare(strict_types=1);

class User extends Model
{
    protected string $table = 'users';

    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updatePassword(int $id, string $hashedPassword): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password = :password, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            'password' => $hashedPassword,
            'id' => $id,
        ]);
    }

    public function createResetToken(int $userId, string $selector, string $tokenHash, DateTimeImmutable $expiresAt): void
    {
        $stmt = $this->db->prepare('INSERT INTO password_resets (user_id, selector, token_hash, expires_at) VALUES (:user_id, :selector, :token_hash, :expires_at)');
        $stmt->execute([
            'user_id' => $userId,
            'selector' => $selector,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }
}
