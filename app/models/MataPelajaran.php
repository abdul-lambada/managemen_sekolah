<?php

declare(strict_types=1);

final class MataPelajaran extends Model
{
    protected string $table = 'mata_pelajaran';
    protected string $primaryKey = 'id_mata_pelajaran';

    public function options(): array
    {
        $sql = "SELECT id_mata_pelajaran, kode_mapel, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel";
        return $this->db->query($sql)->fetchAll();
    }
}
