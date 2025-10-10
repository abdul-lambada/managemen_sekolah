<?php

declare(strict_types=1);

class LaporanAbsensi extends Model
{
    protected string $table = 'laporan_absensi';
    protected string $primaryKey = 'id_laporan';

    public function summary(?string $startDate = null, ?string $endDate = null, string $periode = 'Bulanan'): array
    {
        $sql = "SELECT 
                    periode,
                    tanggal_mulai,
                    tanggal_akhir,
                    jumlah_hadir,
                    jumlah_tidak_hadir
                FROM laporan_absensi";
        $conditions = [];
        $params = [];

        if ($startDate) {
            $conditions[] = 'tanggal_mulai >= :startDate';
            $params['startDate'] = $startDate;
        }

        if ($endDate) {
            $conditions[] = 'tanggal_akhir <= :endDate';
            $params['endDate'] = $endDate;
        }

        if ($periode) {
            $conditions[] = 'periode = :periode';
            $params['periode'] = $periode;
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY tanggal_akhir DESC';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
