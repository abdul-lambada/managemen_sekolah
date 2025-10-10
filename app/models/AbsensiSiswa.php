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
}
