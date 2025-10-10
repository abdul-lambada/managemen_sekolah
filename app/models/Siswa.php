<?php

declare(strict_types=1);

class Siswa extends Model
{
    protected string $table = 'siswa';
    protected string $primaryKey = 'id_siswa';

    public function allWithRelations(): array
    {
        $sql = "SELECT s.*, k.nama_kelas, j.nama_jurusan, u.name AS user_name
                FROM siswa s
                JOIN kelas k ON s.id_kelas = k.id_kelas
                JOIN jurusan j ON k.id_jurusan = j.id_jurusan
                LEFT JOIN users u ON s.user_id = u.id
                ORDER BY s.nama_siswa";
        return $this->db->query($sql)->fetchAll();
    }

    public function findWithRelations(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, k.nama_kelas, j.nama_jurusan
             FROM siswa s
             JOIN kelas k ON s.id_kelas = k.id_kelas
             JOIN jurusan j ON k.id_jurusan = j.id_jurusan
             WHERE s.id_siswa = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
