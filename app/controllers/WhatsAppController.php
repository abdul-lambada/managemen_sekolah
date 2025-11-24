<?php

class WhatsAppController extends Controller
{
    public function index()
    {
        $this->requireRole('admin');
        $action = isset($_GET['action']) ? $_GET['action'] : 'config';

        switch ($action) {
            case 'save_config':
                return $this->saveConfig();
            case 'logs':
                return $this->logs();
            case 'templates':
                return $this->templates();
            case 'save_template':
                return $this->saveTemplate();
            case 'edit_template':
                return $this->editTemplate();
            case 'update_template':
                return $this->updateTemplate();
            case 'delete_template':
                return $this->deleteTemplate();
            default:
                return $this->config();
        }
    }

    private function config()
    {
        $configModel = new WhatsAppConfig();
        $config = isset($_SESSION['wa_form_data']) ? $_SESSION['wa_form_data'] : $configModel->firstConfig();
        $errors = isset($_SESSION['wa_errors']) ? $_SESSION['wa_errors'] : [];
        unset($_SESSION['wa_form_data'], $_SESSION['wa_errors']);

        $response = $this->view('whatsapp/config', [
            'config' => $config,
            'csrfToken' => ensure_csrf_token(),
            'errors' => $errors,
            'alert' => flash('whatsapp_alert'),
        ], 'Konfigurasi WhatsApp');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Integrasi WhatsApp'
        ];

        return $response;
    }

    private function saveConfig()
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('whatsapp_alert', 'Token tidak valid.', 'danger');
            redirect(route('whatsapp_config'));
        }

        $data = $this->sanitizeConfig($_POST);
        $errors = $this->validateConfig($data);

        if (!empty($errors)) {
            $_SESSION['wa_form_data'] = $data;
            $_SESSION['wa_errors'] = $errors;
            redirect(route('whatsapp_config'));
        }

        $model = new WhatsAppConfig();
        $config = $model->firstConfig();

        try {
            if ($config) {
                $model->update($config['id'], $data);
            } else {
                $model->create($data);
            }
            flash('whatsapp_alert', 'Konfigurasi berhasil disimpan.', 'success');
        } catch (PDOException $e) {
            flash('whatsapp_alert', 'Gagal menyimpan konfigurasi: ' . $e->getMessage(), 'danger');
        }

        redirect(route('whatsapp_config'));
    }

    public function logs()
    {
        $this->requireRole('admin');
        $limit = isset($_GET['limit']) ? max(10, (int) $_GET['limit']) : 100;
        $logModel = new WhatsAppLog();
        $logs = $logModel->recent($limit);

        $response = $this->view('whatsapp/logs', [
            'logs' => $logs,
            'limit' => $limit,
        ], 'Log WhatsApp');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Integrasi WhatsApp' => route('whatsapp_config'),
            'Log Pesan'
        ];

        return $response;
    }

    public function templates()
    {
        $this->requireRole('admin');

        $templateModel = new WhatsAppMessageTemplate();
        $templates = $templateModel->all();

        $response = $this->view('whatsapp/templates', [
            'templates' => $templates,
            'csrfToken' => ensure_csrf_token(),
            'alert' => flash('whatsapp_template_alert'),
        ], 'Template WhatsApp');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Integrasi WhatsApp' => route('whatsapp_config'),
            'Template Pesan'
        ];

        $response['breadcrumb_actions'] = [
            [
                'href' => route('whatsapp_config', ['action' => 'edit_template']),
                'label' => 'Tambah Template',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
            ]
        ];

        return $response;
    }

    public function editTemplate()
    {
        $this->requireRole('admin');

        $templateModel = new WhatsAppMessageTemplate();
        $id = (int) ($_GET['id'] ?? 0);
        $template = $_SESSION['wa_template_data'] ?? ($id ? $templateModel->find($id) : null);
        $errors = $_SESSION['wa_template_errors'] ?? [];
        unset($_SESSION['wa_template_data'], $_SESSION['wa_template_errors']);

        $response = $this->view('whatsapp/template_form', [
            'template' => $template,
            'isEdit' => (bool) $id,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
        ], $id ? 'Edit Template WhatsApp' : 'Tambah Template WhatsApp');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Integrasi WhatsApp' => route('whatsapp_config'),
            'Template Pesan' => route('whatsapp_config', ['action' => 'templates']),
            $id ? 'Edit' : 'Tambah',
        ];

        return $response;
    }

    public function saveTemplate()
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('whatsapp_template_alert', 'Token tidak valid.', 'danger');
            redirect(route('whatsapp_config', ['action' => 'templates']));
        }

        $data = $this->sanitizeTemplate($_POST);
        $errors = $this->validateTemplate($data);

        if (!empty($errors)) {
            $_SESSION['wa_template_data'] = $data;
            $_SESSION['wa_template_errors'] = $errors;
            $redirectAction = empty($data['id']) ? ['action' => 'edit_template'] : ['action' => 'edit_template', 'id' => $data['id']];
            redirect(route('whatsapp_config', $redirectAction));
        }

        $templateModel = new WhatsAppMessageTemplate();

        try {
            if (!empty($data['id'])) {
                $id = (int) $data['id'];
                unset($data['id']);
                $templateModel->update($id, $data);
                flash('whatsapp_template_alert', 'Template berhasil diperbarui.', 'success');
            } else {
                unset($data['id']);
                $templateModel->create($data);
                flash('whatsapp_template_alert', 'Template berhasil ditambahkan.', 'success');
            }
        } catch (PDOException $e) {
            flash('whatsapp_template_alert', 'Gagal menyimpan template: ' . $e->getMessage(), 'danger');
        }

        redirect(route('whatsapp_config', ['action' => 'templates']));
    }

    public function updateTemplate()
    {
        return $this->saveTemplate();
    }

    public function deleteTemplate()
    {
        $this->requireRole('admin');
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('whatsapp_template_alert', 'Template tidak ditemukan.', 'danger');
            redirect(route('whatsapp_config', ['action' => 'templates']));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('whatsapp_template_alert', 'Token tidak valid.', 'danger');
            redirect(route('whatsapp_config', ['action' => 'templates']));
        }

        $templateModel = new WhatsAppMessageTemplate();

        try {
            $templateModel->delete($id);
            flash('whatsapp_template_alert', 'Template berhasil dihapus.', 'success');
        } catch (PDOException $e) {
            flash('whatsapp_template_alert', 'Gagal menghapus template: ' . $e->getMessage(), 'danger');
        }

        redirect(route('whatsapp_config', ['action' => 'templates']));
    }

    private function sanitizeConfig($input)
    {
        return [
            'api_key' => trim($input['api_key'] ?? ''),
            'api_url' => trim($input['api_url'] ?? 'https://api.fonnte.com'),
            'country_code' => trim($input['country_code'] ?? '62'),
            'device_id' => trim($input['device_id'] ?? ''),
            'delay' => (int) ($input['delay'] ?? 2),
            'retry' => (int) ($input['retry'] ?? 0),
            'callback_url' => trim($input['callback_url'] ?? ''),
            'template_language' => trim($input['template_language'] ?? 'id'),
            'webhook_secret' => trim($input['webhook_secret'] ?? ''),
        ];
    }

    private function validateConfig($data)
    {
        $errors = [];

        if ($data['api_key'] === '') {
            $errors[] = 'API Key wajib diisi.';
        }

        if (!filter_var($data['api_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'API URL tidak valid.';
        }

        if (!preg_match('/^\d{1,4}$/', $data['country_code'])) {
            $errors[] = 'Kode negara tidak valid.';
        }

        if ($data['delay'] < 0) {
            $errors[] = 'Delay tidak boleh negatif.';
        }

        if ($data['retry'] < 0) {
            $errors[] = 'Retry tidak boleh negatif.';
        }

        return $errors;
    }

    private function sanitizeTemplate($input)
    {
        return [
            'id' => $input['id'] ?? null,
            'name' => trim($input['name'] ?? ''),
            'display_name' => trim($input['display_name'] ?? ''),
            'category' => trim($input['category'] ?? 'UTILITY'),
            'language' => trim($input['language'] ?? 'id'),
            'status' => trim($input['status'] ?? 'PENDING'),
            'body' => trim($input['body'] ?? ''),
            'header' => trim($input['header'] ?? ''),
            'footer' => trim($input['footer'] ?? ''),
            'variables' => trim($input['variables'] ?? ''),
            'is_active' => isset($input['is_active']) ? 1 : 0,
        ];
    }

    private function validateTemplate($data)
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'Nama template wajib diisi.';
        }

        if ($data['display_name'] === '') {
            $errors[] = 'Nama tampilan wajib diisi.';
        }

        if ($data['body'] === '') {
            $errors[] = 'Isi pesan wajib diisi.';
        }

        return $errors;
    }

    private function assertPost()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan');
        }
    }
}
