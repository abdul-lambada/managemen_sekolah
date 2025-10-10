<?php

declare(strict_types=1);

class WhatsAppConfig extends Model
{
    protected string $table = 'whatsapp_config';
    protected string $primaryKey = 'id';

    public function firstConfig(): ?array
    {
        $stmt = $this->db->query('SELECT * FROM whatsapp_config ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
