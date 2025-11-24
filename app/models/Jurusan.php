<?php

class Jurusan extends Model
{
    protected $table = 'jurusan';
    protected $primaryKey = 'id_jurusan';

    public function options()
    {
        return $this->db->query("SELECT id_jurusan, nama_jurusan FROM jurusan ORDER BY nama_jurusan")->fetchAll();
    }
}
