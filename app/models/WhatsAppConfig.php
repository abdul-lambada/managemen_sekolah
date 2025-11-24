<?php

class WhatsAppConfig extends Model
{
    protected $table = 'whatsapp_config';
    protected $primaryKey = 'id';

    public function firstConfig()
    {
        $stmt = $this->db->query('SELECT * FROM whatsapp_config ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
