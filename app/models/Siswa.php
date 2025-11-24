<?php

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'id_siswa';

    public function allWithRelations()
    {
        $sql = "SELECT s.*, k.nama_kelas, j.nama_jurusan, u.name AS user_name
                FROM siswa s
                JOIN kelas k ON s.id_kelas = k.id_kelas
                JOIN jurusan j ON k.id_jurusan = j.id_jurusan
                LEFT JOIN users u ON s.user_id = u.id
                ORDER BY s.nama_siswa";
        return $this->db->query($sql)->fetchAll();
    }

    public function findWithRelations($id)
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, k.nama_kelas, j.nama_jurusan, u.name AS user_name
             FROM siswa s
             JOIN kelas k ON s.id_kelas = k.id_kelas
             JOIN jurusan j ON k.id_jurusan = j.id_jurusan
             LEFT JOIN users u ON s.user_id = u.id
             WHERE s.id_siswa = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function byKelas($kelasId)
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, u.name AS user_name
             FROM siswa s
             LEFT JOIN users u ON s.user_id = u.id
             WHERE s.id_kelas = :id
             ORDER BY s.nama_siswa"
        );
        $stmt->execute(['id' => $kelasId]);
        return $stmt->fetchAll();
    }

    public function findByUserId($userId)
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, k.nama_kelas, j.nama_jurusan
             FROM siswa s
             JOIN kelas k ON s.id_kelas = k.id_kelas
             JOIN jurusan j ON k.id_jurusan = j.id_jurusan
             WHERE s.user_id = :user LIMIT 1"
        );
        $stmt->execute(['user' => $userId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
