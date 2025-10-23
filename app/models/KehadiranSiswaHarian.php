<?php

declare(strict_types=1);

class KehadiranSiswaHarian extends Model
{
    protected string $table = 'kehadiran_siswa_harian';
    protected string $primaryKey = 'id';

    public function allWithSiswa(?string $startDate = null, ?string $endDate = null, ?int $kelasId = null): array
    {
        $this->ensureTableExists();
        $conditions = [];
        $params = [];

        if ($startDate) { $conditions[] = 'ksh.tanggal >= :start'; $params['start'] = $startDate; }
        if ($endDate) { $conditions[] = 'ksh.tanggal <= :end'; $params['end'] = $endDate; }
        if ($kelasId && $kelasId > 0) { $conditions[] = 's.id_kelas = :kelas'; $params['kelas'] = $kelasId; }

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

        $sql = "SELECT ksh.*, s.nama_siswa, s.nis, s.nisn, k.nama_kelas, j.nama_jurusan
                FROM kehadiran_siswa_harian ksh
                JOIN siswa s ON ksh.siswa_id = s.id_siswa
                JOIN kelas k ON s.id_kelas = k.id_kelas
                JOIN jurusan j ON k.id_jurusan = j.id_jurusan
                $where
                ORDER BY ksh.tanggal DESC, s.nama_siswa";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue(':' . $k, $v); }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function kelasOptions(): array
    {
        // Ensure dependencies exist
        $this->ensureTableExists();
        $stmt = $this->db->query("SELECT k.id_kelas, k.nama_kelas, j.nama_jurusan FROM kelas k JOIN jurusan j ON k.id_jurusan = j.id_jurusan ORDER BY j.nama_jurusan, k.nama_kelas");
        return $stmt->fetchAll();
    }

    private function ensureTableExists(): void
    {
        $check = $this->db->prepare("SELECT COUNT(1) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :t");
        $check->execute(['t' => $this->table]);
        $exists = (int)$check->fetchColumn() > 0;
        if ($exists) return;

        $sql = "CREATE TABLE IF NOT EXISTS `kehadiran_siswa_harian` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `siswa_id` INT UNSIGNED NOT NULL,
            `tanggal` DATE NOT NULL,
            `check_in_pagi` TIME NULL,
            `check_out_sore` TIME NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_siswa_tanggal` (`siswa_id`, `tanggal`),
            KEY `idx_tanggal` (`tanggal`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->exec($sql);
    }
}
