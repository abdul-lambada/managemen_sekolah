<?php

declare(strict_types=1);

class FingerprintLog extends Model
{
    protected string $table = 'fingerprint_logs';
    protected string $primaryKey = 'id';

    public function recent(int $limit = 50): array
    {
        $stmt = $this->db->prepare("SELECT * FROM fingerprint_logs ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
