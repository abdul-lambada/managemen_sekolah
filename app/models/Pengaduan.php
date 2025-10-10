<?php

declare(strict_types=1);

final class Pengaduan extends Model
{
    protected string $table = 'pengaduan';
    protected string $primaryKey = 'id_pengaduan';

    public function allOrdered(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY tanggal_pengaduan DESC");
        return $stmt->fetchAll();
    }

    public function createPengaduan(array $data): int
    {
        return $this->create($data);
    }
}
