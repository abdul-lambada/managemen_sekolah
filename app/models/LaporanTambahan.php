<?php

declare(strict_types=1);

final class LaporanTambahan extends Model
{
    public function guruTerlambat(?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT * FROM v_absensi_guru_terlambat WHERE 1=1";
        $params = [];

        if ($startDate) {
            $sql .= " AND tanggal >= :start";
            $params['start'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND tanggal <= :end";
            $params['end'] = $endDate;
        }

        $sql .= " ORDER BY tanggal DESC, jam_masuk DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function rekapSiswaPerKelas(?int $kelasId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT 
                    k.id_kelas,
                    k.nama_kelas,
                    j.nama_jurusan,
                    SUM(CASE WHEN asw.status_kehadiran = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
                    SUM(CASE WHEN asw.status_kehadiran = 'Izin' THEN 1 ELSE 0 END) AS izin,
                    SUM(CASE WHEN asw.status_kehadiran = 'Sakit' THEN 1 ELSE 0 END) AS sakit,
                    SUM(CASE WHEN asw.status_kehadiran = 'Alpa' THEN 1 ELSE 0 END) AS alpa,
                    COUNT(*) AS total
                FROM absensi_siswa asw
                JOIN siswa s ON asw.id_siswa = s.id_siswa
                JOIN kelas k ON s.id_kelas = k.id_kelas
                JOIN jurusan j ON k.id_jurusan = j.id_jurusan
                WHERE 1=1";

        $params = [];

        if ($kelasId) {
            $sql .= " AND k.id_kelas = :kelas";
            $params['kelas'] = $kelasId;
        }

        if ($startDate) {
            $sql .= " AND asw.tanggal >= :start";
            $params['start'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND asw.tanggal <= :end";
            $params['end'] = $endDate;
        }

        $sql .= " GROUP BY k.id_kelas, k.nama_kelas, j.nama_jurusan
                  ORDER BY j.nama_jurusan, k.nama_kelas";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
