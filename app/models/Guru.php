<?php

declare(strict_types=1);

class Guru extends Model
{
    protected string $table = 'guru';
    protected string $primaryKey = 'id_guru';

    public function allWithUser(): array
    {
        $sql = "SELECT g.*, u.name AS user_name FROM guru g LEFT JOIN users u ON g.user_id = u.id ORDER BY g.nama_guru";
        return $this->db->query($sql)->fetchAll();
    }

    public function findWithUser(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT g.*, u.name AS user_name FROM guru g LEFT JOIN users u ON g.user_id = u.id WHERE g.id_guru = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
