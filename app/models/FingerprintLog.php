<?php

class FingerprintLog extends Model
{
    protected $table = 'fingerprint_logs';
    protected $primaryKey = 'id';

    public function recent($limit = 50)
    {
        $stmt = $this->db->prepare("SELECT * FROM fingerprint_logs ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
