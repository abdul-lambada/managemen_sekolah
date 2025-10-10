<?php

declare(strict_types=1);

class FingerprintController extends Controller
{
    public function index(): array|string
    {
        $this->requireRole('admin');

        $action = $_GET['action'] ?? 'list';

        return match ($action) {
            'create' => $this->create(),
            'store' => $this->store(),
            'edit' => $this->edit(),
            'update' => $this->update(),
            'delete' => $this->delete(),
            'logs' => $this->buildLogs(),
            default => $this->listing(),
        };
    }

    private function listing(): array
    {
        $deviceModel = new FingerprintDevice();
        $devices = $deviceModel->all();
        $activeCount = $deviceModel->count(['is_active' => 1]);

        $response = $this->view('fingerprint/devices', [
            'devices' => $devices,
            'activeCount' => $activeCount,
            'csrfToken' => ensure_csrf_token(),
            'alert' => flash('fingerprint_alert'),
        ], 'Perangkat Fingerprint');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Fingerprint',
            'Perangkat',
        ];
        $response['breadcrumb_actions'] = [
            [
                'href' => route('fingerprint_devices', ['action' => 'create']),
                'label' => 'Tambah Perangkat',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
            ],
        ];

        return $response;
    }

    private function create(): array
    {
        $formData = $_SESSION['fingerprint_form_data'] ?? [];
        $errors = $_SESSION['fingerprint_errors'] ?? [];
        unset($_SESSION['fingerprint_form_data'], $_SESSION['fingerprint_errors']);

        $response = $this->view('fingerprint/device_form', [
            'isEdit' => false,
            'device' => $formData,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
        ], 'Tambah Perangkat Fingerprint');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Fingerprint' => route('fingerprint_devices'),
            'Tambah Perangkat',
        ];

        return $response;
    }

    private function edit(): array
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            flash('fingerprint_alert', 'Perangkat tidak ditemukan.', 'danger');
            redirect(route('fingerprint_devices'));
        }

        $model = new FingerprintDevice();
        $device = $_SESSION['fingerprint_form_data'] ?? $model->find($id);
        $errors = $_SESSION['fingerprint_errors'] ?? [];
        unset($_SESSION['fingerprint_form_data'], $_SESSION['fingerprint_errors']);

        if (!$device) {
            flash('fingerprint_alert', 'Perangkat tidak ditemukan.', 'danger');
            redirect(route('fingerprint_devices'));
        }

        $response = $this->view('fingerprint/device_form', [
            'isEdit' => true,
            'device' => $device,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
        ], 'Edit Perangkat Fingerprint');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Fingerprint' => route('fingerprint_devices'),
            'Edit Perangkat',
        ];

        return $response;
    }

    private function store(): string
    {
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('fingerprint_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('fingerprint_devices', ['action' => 'create']));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['fingerprint_form_data'] = $data;
            $_SESSION['fingerprint_errors'] = $errors;
            redirect(route('fingerprint_devices', ['action' => 'create']));
        }

        try {
            $model = new FingerprintDevice();
            $model->create($this->mapToDb($data));
            flash('fingerprint_alert', 'Perangkat berhasil ditambahkan.', 'success');
        } catch (PDOException $e) {
            $_SESSION['fingerprint_form_data'] = $data;
            $_SESSION['fingerprint_errors'] = ['Gagal menyimpan data: ' . $e->getMessage()];
            redirect(route('fingerprint_devices', ['action' => 'create']));
        }

        redirect(route('fingerprint_devices'));
    }

    private function update(): string
    {
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('fingerprint_alert', 'Perangkat tidak ditemukan.', 'danger');
            redirect(route('fingerprint_devices'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('fingerprint_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('fingerprint_devices', ['action' => 'edit', 'id' => $id]));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data, true);

        if (!empty($errors)) {
            $_SESSION['fingerprint_form_data'] = $data;
            $_SESSION['fingerprint_errors'] = $errors;
            redirect(route('fingerprint_devices', ['action' => 'edit', 'id' => $id]));
        }

        try {
            $model = new FingerprintDevice();
            $model->update($id, $this->mapToDb($data));
            flash('fingerprint_alert', 'Perangkat berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            $_SESSION['fingerprint_form_data'] = $data;
            $_SESSION['fingerprint_errors'] = ['Gagal memperbarui data: ' . $e->getMessage()];
            redirect(route('fingerprint_devices', ['action' => 'edit', 'id' => $id]));
        }

        redirect(route('fingerprint_devices'));
    }

    private function delete(): string
    {
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('fingerprint_alert', 'Perangkat tidak ditemukan.', 'danger');
            redirect(route('fingerprint_devices'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('fingerprint_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('fingerprint_devices'));
        }

        try {
            $model = new FingerprintDevice();
            $model->delete($id);
            flash('fingerprint_alert', 'Perangkat berhasil dihapus.', 'success');
        } catch (PDOException $e) {
            flash('fingerprint_alert', 'Gagal menghapus data: ' . $e->getMessage(), 'danger');
        }

        redirect(route('fingerprint_devices'));
    }

    public function logs(): array
    {
        $this->requireRole('admin');

        return $this->buildLogs();
    }

    private function buildLogs(): array
    {
        $logModel = new FingerprintLog();
        $limit = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 100;
        $logs = $logModel->recent($limit);

        $response = $this->view('fingerprint/logs', [
            'logs' => $logs,
            'limit' => $limit,
        ], 'Log Fingerprint');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Fingerprint' => route('fingerprint_devices'),
            'Log Perangkat',
        ];

        return $response;
    }

    private function sanitizeInput(array $input): array
    {
        $port = isset($input['port']) && $input['port'] !== '' ? (int) $input['port'] : 4370;

        return [
            'id' => (int) ($input['id'] ?? 0),
            'ip' => trim($input['ip'] ?? ''),
            'port' => $port,
            'nama_lokasi' => trim($input['nama_lokasi'] ?? ''),
            'keterangan' => trim($input['keterangan'] ?? ''),
            'is_active' => isset($input['is_active']) ? 1 : 0,
        ];
    }

    private function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if ($data['ip'] === '') {
            $errors[] = 'IP address wajib diisi.';
        } elseif (!filter_var($data['ip'], FILTER_VALIDATE_IP)) {
            $errors[] = 'IP address tidak valid.';
        }

        if ($data['port'] <= 0 || $data['port'] > 65535) {
            $errors[] = 'Port harus antara 1-65535.';
        }

        if ($data['nama_lokasi'] === '') {
            $errors[] = 'Nama lokasi wajib diisi.';
        }

        return $errors;
    }

    private function mapToDb(array $data): array
    {
        return [
            'ip' => $data['ip'],
            'port' => $data['port'] ?: 4370,
            'nama_lokasi' => $data['nama_lokasi'],
            'keterangan' => $data['keterangan'] ?: null,
            'is_active' => $data['is_active'] ? 1 : 0,
        ];
    }

    private function assertPost(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan');
        }
    }
}
