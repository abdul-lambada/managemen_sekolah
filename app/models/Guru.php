<?php

class Guru extends Model
{
    protected $table = 'guru';
    protected $primaryKey = 'id_guru';

    public function allWithUser()
    {
        $sql = "SELECT g.*, u.name AS user_name FROM guru g LEFT JOIN users u ON g.user_id = u.id ORDER BY g.nama_guru";
        return $this->db->query($sql)->fetchAll();
    }

    public function findWithUser($id)
    {
        $stmt = $this->db->prepare(
            "SELECT g.*, u.name AS user_name FROM guru g LEFT JOIN users u ON g.user_id = u.id WHERE g.id_guru = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function options()
    {
        $sql = "SELECT id_guru, nama_guru FROM guru ORDER BY nama_guru";
        return $this->db->query($sql)->fetchAll();
    }
}
