<?php

class FingerprintDevice extends Model
{
    protected $table = 'fingerprint_devices';
    protected $primaryKey = 'id';

    public function active()
    {
        $stmt = $this->db->query("SELECT * FROM fingerprint_devices WHERE is_active = 1 ORDER BY nama_lokasi");
        return $stmt->fetchAll();
    }
}
