<?php

class WhatsAppMessageTemplate extends Model
{
    protected $table = 'whatsapp_message_templates';
    protected $primaryKey = 'id';

    public function activeTemplates()
    {
        $stmt = $this->db->query("SELECT * FROM whatsapp_message_templates WHERE is_active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }
}
