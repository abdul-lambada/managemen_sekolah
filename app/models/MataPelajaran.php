<?php

final class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';
    protected $primaryKey = 'id_mata_pelajaran';

    public function options()
    {
        $sql = "SELECT id_mata_pelajaran, kode_mapel, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel";
        return $this->db->query($sql)->fetchAll();
    }
}
