<?php

final class Pengaduan extends Model
{
    protected $table = 'pengaduan';
    protected $primaryKey = 'id_pengaduan';

    public function allOrdered()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY tanggal_pengaduan DESC");
        return $stmt->fetchAll();
    }

    public function createPengaduan($data)
    {
        return $this->create($data);
    }
}
