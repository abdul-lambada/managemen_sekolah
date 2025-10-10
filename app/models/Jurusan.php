<?php

declare(strict_types=1);

class Jurusan extends Model
{
    protected string $table = 'jurusan';
    protected string $primaryKey = 'id_jurusan';

    public function options(): array
    {
        return $this->db->query("SELECT id_jurusan, nama_jurusan FROM jurusan ORDER BY nama_jurusan")->fetchAll();
    }
}
