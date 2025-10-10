<?php

declare(strict_types=1);

class FingerprintDevice extends Model
{
    protected string $table = 'fingerprint_devices';
    protected string $primaryKey = 'id';

    public function active(): array
    {
        $stmt = $this->db->query("SELECT * FROM fingerprint_devices WHERE is_active = 1 ORDER BY nama_lokasi");
        return $stmt->fetchAll();
    }
}
