<?php

declare(strict_types=1);

class GuruController extends Controller
{
    public function index(): array|string
    {
        $action = $_GET['action'] ?? 'list';

        return match ($action) {
            'create' => $this->create(),
            'store' => $this->store(),
            'edit' => $this->edit(),
            'update' => $this->update(),
            'delete' => $this->delete(),
            default => $this->list(),
        };
    }

    private function list(): array
    {
        $this->requireRole('admin');

        $guruModel = new Guru();
        $guruList = $guruModel->allWithUser();
        $csrfToken = ensure_csrf_token();

        $response = $this->view('guru/index', [
            'guruList' => $guruList,
            'alert' => flash('guru_alert'),
            'csrfToken' => $csrfToken,
        ], 'Data Guru');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Guru'
        ];
        $response['breadcrumb_actions'] = [
            [
                'href' => route('guru', ['action' => 'create']),
                'label' => 'Tambah Guru',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
            ],
        ];
        $response['scripts'] = [];

        return $response;
    }

    private function create(): array
    {
        $this->requireRole('admin');

        $formData = $_SESSION['guru_form_data'] ?? [];
        $errors = $_SESSION['guru_errors'] ?? [];
        unset($_SESSION['guru_form_data'], $_SESSION['guru_errors']);

        $response = $this->view('guru/form', [
            'isEdit' => false,
            'guru' => $formData,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
            'userOptions' => $this->userOptions(),
        ], 'Tambah Guru');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Guru' => route('guru'),
            'Tambah Guru'
        ];

        return $response;
    }

    private function edit(): array
    {
        $this->requireRole('admin');
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            flash('guru_alert', 'Data guru tidak ditemukan.', 'danger');
            redirect(route('guru'));
        }

        $guruModel = new Guru();
        $guru = $_SESSION['guru_form_data'] ?? $guruModel->findWithUser($id);
        $errors = $_SESSION['guru_errors'] ?? [];
        unset($_SESSION['guru_form_data'], $_SESSION['guru_errors']);

        if (!$guru) {
            flash('guru_alert', 'Data guru tidak ditemukan.', 'danger');
            redirect(route('guru'));
        }

        $response = $this->view('guru/form', [
            'isEdit' => true,
            'guru' => $guru,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
            'userOptions' => $this->userOptions(),
        ], 'Edit Guru');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Guru' => route('guru'),
            'Edit Guru'
        ];

        return $response;
    }

    private function store(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('guru_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('guru', ['action' => 'create']));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['guru_form_data'] = $data;
            $_SESSION['guru_errors'] = $errors;
            redirect(route('guru', ['action' => 'create']));
        }

        try {
            $model = new Guru();
            $model->create($this->mapToDb($data));
            flash('guru_alert', 'Data guru berhasil ditambahkan.', 'success');
        } catch (PDOException $e) {
            $_SESSION['guru_form_data'] = $data;
            $_SESSION['guru_errors'] = ['Gagal menyimpan data: ' . $e->getMessage()];
            redirect(route('guru', ['action' => 'create']));
        }

        redirect(route('guru'));
    }

    private function update(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('guru_alert', 'Data guru tidak ditemukan.', 'danger');
            redirect(route('guru'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('guru_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('guru', ['action' => 'edit', 'id' => $id]));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data, true);

        if (!empty($errors)) {
            $_SESSION['guru_form_data'] = $data;
            $_SESSION['guru_errors'] = $errors;
            redirect(route('guru', ['action' => 'edit', 'id' => $id]));
        }

        try {
            $model = new Guru();
            $model->update($id, $this->mapToDb($data));
            flash('guru_alert', 'Data guru berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            $_SESSION['guru_form_data'] = $data;
            $_SESSION['guru_errors'] = ['Gagal memperbarui data: ' . $e->getMessage()];
            redirect(route('guru', ['action' => 'edit', 'id' => $id]));
        }

        redirect(route('guru'));
    }

    private function delete(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('guru_alert', 'Data guru tidak ditemukan.', 'danger');
            redirect(route('guru'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('guru_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('guru'));
        }

        try {
            $model = new Guru();
            $model->delete($id, 'id_guru');
            flash('guru_alert', 'Data guru berhasil dihapus.', 'success');
        } catch (PDOException $e) {
            flash('guru_alert', 'Gagal menghapus data: ' . $e->getMessage(), 'danger');
        }

        redirect(route('guru'));
    }

    private function sanitizeInput(array $input): array
    {
        return [
            'id' => (int) ($input['id'] ?? 0),
            'nama_guru' => trim($input['nama_guru'] ?? ''),
            'nip' => trim($input['nip'] ?? ''),
            'jenis_kelamin' => $input['jenis_kelamin'] ?? '',
            'tanggal_lahir' => $input['tanggal_lahir'] ?? null,
            'alamat' => trim($input['alamat'] ?? ''),
            'phone' => trim($input['phone'] ?? ''),
            'user_id' => $input['user_id'] ?? null,
        ];
    }

    private function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if ($data['nama_guru'] === '') {
            $errors[] = 'Nama guru wajib diisi.';
        }

        if ($data['nip'] === '') {
            $errors[] = 'NIP wajib diisi.';
        }

        if (!in_array($data['jenis_kelamin'], ['Laki-laki', 'Perempuan'], true)) {
            $errors[] = 'Jenis kelamin tidak valid.';
        }

        if ($data['alamat'] === '') {
            $errors[] = 'Alamat wajib diisi.';
        }

        if ($data['phone'] !== '' && !preg_match('/^[0-9+\- ]+$/', $data['phone'])) {
            $errors[] = 'Nomor telepon tidak valid.';
        }

        if ($data['tanggal_lahir']) {
            $d = DateTime::createFromFormat('Y-m-d', $data['tanggal_lahir']);
            if (!$d || $d->format('Y-m-d') !== $data['tanggal_lahir']) {
                $errors[] = 'Tanggal lahir tidak valid.';
            }
        }

        if ($data['user_id'] === '') {
            $data['user_id'] = null;
        }

        return $errors;
    }

    private function mapToDb(array $data): array
    {
        return [
            'nama_guru' => $data['nama_guru'],
            'nip' => $data['nip'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tanggal_lahir' => $data['tanggal_lahir'] ?: null,
            'alamat' => $data['alamat'],
            'phone' => $data['phone'] ?: null,
            'user_id' => $data['user_id'] ?: null,
        ];
    }

    private function userOptions(): array
    {
        $stmt = db()->query("SELECT id, name, role FROM users ORDER BY name");
        return $stmt->fetchAll();
    }

    private function assertPost(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan');
        }
    }
}
