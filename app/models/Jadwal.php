<?php

declare(strict_types=1);

final class Jadwal extends Model
{
    protected string $table = 'jadwal_pelajaran';
    protected string $primaryKey = 'id_jadwal';

    public function allWithRelations(): array
    {
        $sql = <<<'SQL'
SELECT j.*, k.nama_kelas, mp.nama_mapel, mp.kode_mapel, g.nama_guru
FROM jadwal_pelajaran j
JOIN kelas k ON j.id_kelas = k.id_kelas
JOIN mata_pelajaran mp ON j.id_mata_pelajaran = mp.id_mata_pelajaran
JOIN guru g ON j.id_guru = g.id_guru
ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), j.jam_mulai
SQL;

        return $this->db->query($sql)->fetchAll();
    }

    public function findWithRelations(int $id): ?array
    {
        $sql = <<<'SQL'
SELECT j.*, k.nama_kelas, mp.nama_mapel, mp.kode_mapel, g.nama_guru
FROM jadwal_pelajaran j
JOIN kelas k ON j.id_kelas = k.id_kelas
JOIN mata_pelajaran mp ON j.id_mata_pelajaran = mp.id_mata_pelajaran
JOIN guru g ON j.id_guru = g.id_guru
WHERE j.id_jadwal = :id
LIMIT 1
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function filter(array $filters = []): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['kelas'])) {
            $conditions[] = 'j.id_kelas = :kelas';
            $params['kelas'] = (int) $filters['kelas'];
        }

        if (!empty($filters['guru'])) {
            $conditions[] = 'j.id_guru = :guru';
            $params['guru'] = (int) $filters['guru'];
        }

        if (!empty($filters['mapel'])) {
            $conditions[] = 'j.id_mata_pelajaran = :mapel';
            $params['mapel'] = (int) $filters['mapel'];
        }

        if (!empty($filters['hari'])) {
            $conditions[] = 'j.hari = :hari';
            $params['hari'] = $filters['hari'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = <<<'SQL'
SELECT j.*, k.nama_kelas, mp.nama_mapel, mp.kode_mapel, g.nama_guru
FROM jadwal_pelajaran j
JOIN kelas k ON j.id_kelas = k.id_kelas
JOIN mata_pelajaran mp ON j.id_mata_pelajaran = mp.id_mata_pelajaran
JOIN guru g ON j.id_guru = g.id_guru
%s
ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), j.jam_mulai
SQL;

        $sql = sprintf($sql, $where);

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
