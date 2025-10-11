<?php

declare(strict_types=1);

final class AbsensiGuruMapel extends Model
{
    protected string $table = 'absensi_guru_mapel';
    protected string $primaryKey = 'id_absensi_mapel';

    public function findByJadwalAndDate(int $jadwalId, string $tanggal): ?array
    {
        $sql = "SELECT * FROM absensi_guru_mapel WHERE id_jadwal = :jadwal AND tanggal = :tanggal LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'jadwal' => $jadwalId,
            'tanggal' => $tanggal,
        ]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function upsert(array $data): int
    {
        $sql = "INSERT INTO absensi_guru_mapel (id_jadwal, tanggal, status_kehadiran, jam_masuk, jam_keluar, sumber, catatan)
                VALUES (:id_jadwal, :tanggal, :status_kehadiran, :jam_masuk, :jam_keluar, :sumber, :catatan)
                ON DUPLICATE KEY UPDATE status_kehadiran = VALUES(status_kehadiran), jam_masuk = VALUES(jam_masuk), jam_keluar = VALUES(jam_keluar), sumber = VALUES(sumber), catatan = VALUES(catatan), updated_at = NOW()";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_jadwal' => $data['id_jadwal'],
            'tanggal' => $data['tanggal'],
            'status_kehadiran' => $data['status_kehadiran'],
            'jam_masuk' => $data['jam_masuk'],
            'jam_keluar' => $data['jam_keluar'],
            'sumber' => $data['sumber'],
            'catatan' => $data['catatan'],
        ]);

        if (!empty($data['id_absensi_mapel'])) {
            return (int) $data['id_absensi_mapel'];
        }

        return (int) $this->db->lastInsertId();
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

        if (!empty($filters['start'])) {
            $conditions[] = 'agm.tanggal >= :start';
            $params['start'] = $filters['start'];
        }

        if (!empty($filters['end'])) {
            $conditions[] = 'agm.tanggal <= :end';
            $params['end'] = $filters['end'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'agm.status_kehadiran = :status';
            $params['status'] = $filters['status'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT agm.*, j.hari, j.jam_mulai, j.jam_selesai, k.nama_kelas, mp.nama_mapel, mp.kode_mapel, g.nama_guru
                FROM absensi_guru_mapel agm
                JOIN jadwal_pelajaran j ON agm.id_jadwal = j.id_jadwal
                JOIN kelas k ON j.id_kelas = k.id_kelas
                JOIN mata_pelajaran mp ON j.id_mata_pelajaran = mp.id_mata_pelajaran
                JOIN guru g ON j.id_guru = g.id_guru
                {$where}
                ORDER BY agm.tanggal DESC, j.jam_mulai";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
