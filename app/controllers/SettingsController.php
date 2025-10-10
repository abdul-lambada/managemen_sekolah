<?php

declare(strict_types=1);

final class SettingsController extends Controller
{
    public function index(): array
    {
        if (!has_role('admin')) {
            http_response_code(403);

            return [
                'view' => 'errors/forbidden',
                'data' => [
                    'message' => 'Anda tidak memiliki izin untuk mengakses halaman pengaturan.',
                    'backUrl' => route('dashboard'),
                ],
                'title' => 'Akses Ditolak',
            ];
        }

        $response = $this->view('settings/index', [
            'sections' => $this->settingsSections(),
            'currentSettings' => $this->generalSettings(),
            'csrfToken' => ensure_csrf_token(),
            'alert' => flash('settings_alert'),
        ], 'Pengaturan');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Pengaturan'
        ];

        return $response;
    }

    public function update(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('settings_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('settings'));
        }

        $data = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            flash('settings_alert', implode('\n', $errors), 'danger');
            redirect(route('settings'));
        }

        try {
            $this->saveSettings($data);
            flash('settings_alert', 'Pengaturan umum berhasil diperbarui.', 'success');
        } catch (\Throwable $e) {
            flash('settings_alert', 'Pengaturan gagal diperbarui: ' . $e->getMessage(), 'danger');
        }

        redirect(route('settings'));
    }

    private function settingsSections(): array
    {
        return [
            [
                'title' => 'Profil',
                'description' => 'Kelola informasi profil dan keamanan akun.',
                'url' => route('profile'),
                'icon' => 'fas fa-user-cog',
            ],
            [
                'title' => 'WhatsApp',
                'description' => 'Perbarui konfigurasi integrasi WhatsApp.',
                'url' => route('whatsapp_config'),
                'icon' => 'fab fa-whatsapp',
            ],
            [
                'title' => 'Automasi',
                'description' => 'Atur automasi dan jadwal sinkronisasi.',
                'url' => route('automation'),
                'icon' => 'fas fa-robot',
            ],
        ];
    }

    private function generalSettings(): array
    {
        return app_settings();
    }

    private function sanitize(array $input): array
    {
        return [
            'app_name' => trim($input['app_name'] ?? ''),
            'app_tagline' => trim($input['app_tagline'] ?? ''),
            'favicon' => $_FILES['favicon'] ?? null,
            'favicon_existing' => trim($input['favicon_existing'] ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['app_name'] === '') {
            $errors[] = 'Nama aplikasi wajib diisi.';
        }

        if (!empty($data['favicon']['name'])) {
            $allowed = ['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml'];
            $mime = $data['favicon']['type'] ?? '';
            if (!in_array($mime, $allowed, true)) {
                $errors[] = 'Favicon harus dalam format PNG, ICO, atau SVG.';
            }

            if (($data['favicon']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                $errors[] = 'Gagal mengunggah favicon. Silakan coba lagi.';
            }
        }

        return $errors;
    }

    private function saveSettings(array $data): void
    {
        ensure_settings_table();

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $this->upsert('app_name', $data['app_name']);
            $this->upsert('app_tagline', $data['app_tagline']);

            $faviconPath = $this->handleFaviconUpload($data['favicon'], $data['favicon_existing']);
            $this->upsert('favicon', $faviconPath);

            $pdo->commit();
            app_settings(true);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function upsert(string $key, ?string $value): void
    {
        $stmt = db()->prepare("INSERT INTO settings (option_key, option_value) VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
        $stmt->execute([
            'key' => $key,
            'value' => $value,
        ]);
    }

    private function handleFaviconUpload(?array $file, string $existing): ?string
    {
        if (empty($file) || empty($file['name'])) {
            if ($existing === '') {
                return null;
            }

            return preg_replace('/[^a-zA-Z0-9_\-\/\.]/', '', $existing);
        }

        $uploadsDir = BASE_PATH . '/public/uploads/app';
        if (!is_dir($uploadsDir) && !mkdir($uploadsDir, 0755, true) && !is_dir($uploadsDir)) {
            throw new \RuntimeException('Tidak dapat membuat direktori upload.');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'ico');
        if ($extension === '') {
            $extension = 'ico';
        }
        $safeExtension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'ico';
        $filename = 'favicon_' . time() . '.' . $safeExtension;
        $targetPath = $uploadsDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \RuntimeException('Gagal menyimpan favicon.');
        }

        if ($existing) {
            $existingPath = $uploadsDir . '/' . basename($existing);
            if (file_exists($existingPath)) {
                @unlink($existingPath);
            }
        }

        return 'app/' . $filename;
    }

    private function assertPost(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan');
        }
    }
}
