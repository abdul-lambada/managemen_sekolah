<?php

declare(strict_types=1);

class AbsensiSiswa extends Model
{
    protected string $table = 'absensi_siswa';
    protected string $primaryKey = 'id_absensi_siswa';

    public function allWithSiswa(?string $startDate = null, ?string $endDate = null, ?int $kelasId = null): array
    {
        $conditions = [];
        $params = [];

        if ($startDate) {
            $conditions[] = 'asw.tanggal >= :start';
            $params['start'] = $startDate;
        }

        if ($endDate) {
            $conditions[] = 'asw.tanggal <= :end';
            $params['end'] = $endDate;
        }

        if ($kelasId) {
            $conditions[] = 's.id_kelas = :kelas';
            $params['kelas'] = $kelasId;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT asw.*, s.nama_siswa, s.nisn, s.nis, k.nama_kelas, j.nama_jurusan
                FROM absensi_siswa asw
                JOIN siswa s ON asw.id_siswa = s.id_siswa
                JOIN kelas k ON s.id_kelas = k.id_kelas
                JOIN jurusan j ON k.id_jurusan = j.id_jurusan
                {$where}
                ORDER BY asw.tanggal DESC, asw.jam_masuk";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function recentForStudent(int $siswaId, int $days = 14, int $limit = 30): array
    {
        $startDate = date('Y-m-d', strtotime(sprintf('-%d days', $days)));

        $stmt = $this->db->prepare(
            "SELECT asw.*, s.nama_siswa, s.nisn, s.nis, k.nama_kelas
             FROM absensi_siswa asw
             JOIN siswa s ON asw.id_siswa = s.id_siswa
             JOIN kelas k ON s.id_kelas = k.id_kelas
             WHERE asw.id_siswa = :siswa AND asw.tanggal >= :start
             ORDER BY asw.tanggal DESC, asw.jam_masuk DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':siswa', $siswaId);
        $stmt->bindValue(':start', $startDate);
        $stmt->bindValue(':limit', $limit);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function summaryForStudent(int $siswaId, ?string $startDate = null, ?string $endDate = null): array
    {
        $conditions = ['id_siswa = :siswa'];
        $params = ['siswa' => $siswaId];

        if ($startDate) {
            $conditions[] = 'tanggal >= :start';
            $params['start'] = $startDate;
        }

        if ($endDate) {
            $conditions[] = 'tanggal <= :end';
            $params['end'] = $endDate;
        }

        $where = implode(' AND ', $conditions);

        $stmt = $this->db->prepare(
            "SELECT status_kehadiran, COUNT(*) AS total
             FROM absensi_siswa
             WHERE {$where}
             GROUP BY status_kehadiran"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        $result = [
            'Hadir' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'Alpa' => 0,
            'Terlambat' => 0,
        ];

        foreach ($stmt->fetchAll() as $row) {
            $status = $row['status_kehadiran'];
            $result[$status] = (int) $row['total'];
        }

        return $result;
    }

    public function byStudent(int $siswaId, ?string $startDate = null, ?string $endDate = null): array
    {
        $conditions = ['asw.id_siswa = :siswa'];
        $params = ['siswa' => $siswaId];

        if ($startDate) {
            $conditions[] = 'asw.tanggal >= :start';
            $params['start'] = $startDate;
        }

        if ($endDate) {
            $conditions[] = 'asw.tanggal <= :end';
            $params['end'] = $endDate;
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        $sql = "SELECT asw.*, s.nama_siswa, s.nisn, s.nis, k.nama_kelas
                FROM absensi_siswa asw
                JOIN siswa s ON asw.id_siswa = s.id_siswa
                JOIN kelas k ON s.id_kelas = k.id_kelas
                {$where}
                ORDER BY asw.tanggal DESC, asw.jam_masuk DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
