<?php

declare(strict_types=1);

class Kelas extends Model
{
    protected string $table = 'kelas';
    protected string $primaryKey = 'id_kelas';

    public function allWithJurusan(): array
    {
        $sql = "SELECT k.*, j.nama_jurusan FROM kelas k JOIN jurusan j ON k.id_jurusan = j.id_jurusan ORDER BY k.nama_kelas";
        return $this->db->query($sql)->fetchAll();
    }

    public function findWithJurusan(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT k.*, j.nama_jurusan FROM kelas k JOIN jurusan j ON k.id_jurusan = j.id_jurusan WHERE k.id_kelas = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function options(): array
    {
        $stmt = $this->db->query("SELECT k.id_kelas, k.nama_kelas, j.nama_jurusan FROM kelas k JOIN jurusan j ON k.id_jurusan = j.id_jurusan ORDER BY j.nama_jurusan, k.nama_kelas");
        return $stmt->fetchAll();
    }

    public function byJurusan(int $jurusanId): array
    {
        $stmt = $this->db->prepare(
            "SELECT k.*, j.nama_jurusan FROM kelas k JOIN jurusan j ON k.id_jurusan = j.id_jurusan WHERE k.id_jurusan = :id ORDER BY k.nama_kelas"
        );
        $stmt->execute(['id' => $jurusanId]);
        return $stmt->fetchAll();
    }
}
