<?php

declare(strict_types=1);

class WhatsAppMessageTemplate extends Model
{
    protected string $table = 'whatsapp_message_templates';
    protected string $primaryKey = 'id';

    public function activeTemplates(): array
    {
        $stmt = $this->db->query("SELECT * FROM whatsapp_message_templates WHERE is_active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }
}
