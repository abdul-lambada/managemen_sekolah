<?php

class WhatsAppLog extends Model
{
    protected $table = 'whatsapp_logs';
    protected $primaryKey = 'id';

    public function recent($limit = 50)
    {
        $stmt = $this->db->prepare("SELECT * FROM whatsapp_logs ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
