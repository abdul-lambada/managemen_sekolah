<?php

declare(strict_types=1);

class AbsensiGuru extends Model
{
    protected string $table = 'absensi_guru';
    protected string $primaryKey = 'id_absensi_guru';

    public function allWithGuru(?string $startDate = null, ?string $endDate = null): array
    {
        $conditions = [];
        $params = [];

        if ($startDate) {
            $conditions[] = 'ag.tanggal >= :start';
            $params['start'] = $startDate;
        }

        if ($endDate) {
            $conditions[] = 'ag.tanggal <= :end';
            $params['end'] = $endDate;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT ag.*, g.nama_guru, g.nip,
                       kh.check_in_pagi AS daily_check_in_pagi,
                       kh.check_out_sore AS daily_check_out_sore
                FROM absensi_guru ag
                JOIN guru g ON ag.id_guru = g.id_guru
                LEFT JOIN kehadiran_guru_harian kh
                  ON kh.guru_id = ag.id_guru AND kh.tanggal = ag.tanggal
                {$where}
                ORDER BY ag.tanggal DESC, ag.jam_masuk";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
