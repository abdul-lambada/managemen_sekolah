<?php

declare(strict_types=1);

class JurusanController extends Controller
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
            default => $this->listing(),
        };
    }

    private function listing(): array
    {
        $this->requireRole('admin');

        $model = new Jurusan();
        $jurusan = $model->all();
        $csrfToken = ensure_csrf_token();

        $response = $this->view('jurusan/index', [
            'jurusanList' => $jurusan,
            'csrfToken' => $csrfToken,
            'alert' => flash('jurusan_alert'),
        ], 'Data Jurusan');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Jurusan'
        ];
        $response['breadcrumb_actions'] = [
            [
                'href' => route('jurusan', ['action' => 'create']),
                'label' => 'Tambah Jurusan',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
            ],
        ];

        return $response;
    }

    private function create(): array
    {
        $this->requireRole('admin');

        $formData = $_SESSION['jurusan_form_data'] ?? [];
        $errors = $_SESSION['jurusan_errors'] ?? [];
        unset($_SESSION['jurusan_form_data'], $_SESSION['jurusan_errors']);

        $response = $this->view('jurusan/form', [
            'isEdit' => false,
            'jurusan' => $formData,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
        ], 'Tambah Jurusan');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Jurusan' => route('jurusan'),
            'Tambah Jurusan'
        ];

        return $response;
    }

    private function edit(): array
    {
        $this->requireRole('admin');
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            flash('jurusan_alert', 'Data jurusan tidak ditemukan.', 'danger');
            redirect(route('jurusan'));
        }

        $model = new Jurusan();
        $jurusan = $_SESSION['jurusan_form_data'] ?? $model->find($id, 'id_jurusan');
        $errors = $_SESSION['jurusan_errors'] ?? [];
        unset($_SESSION['jurusan_form_data'], $_SESSION['jurusan_errors']);

        if (!$jurusan) {
            flash('jurusan_alert', 'Data jurusan tidak ditemukan.', 'danger');
            redirect(route('jurusan'));
        }

        $response = $this->view('jurusan/form', [
            'isEdit' => true,
            'jurusan' => $jurusan,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
        ], 'Edit Jurusan');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Jurusan' => route('jurusan'),
            'Edit Jurusan'
        ];

        return $response;
    }

    private function store(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('jurusan_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('jurusan', ['action' => 'create']));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['jurusan_form_data'] = $data;
            $_SESSION['jurusan_errors'] = $errors;
            redirect(route('jurusan', ['action' => 'create']));
        }

        try {
            $model = new Jurusan();
            $model->create($this->mapToDb($data));
            flash('jurusan_alert', 'Data jurusan berhasil ditambahkan.', 'success');
        } catch (PDOException $e) {
            $_SESSION['jurusan_form_data'] = $data;
            $_SESSION['jurusan_errors'] = ['Gagal menyimpan data: ' . $e->getMessage()];
            redirect(route('jurusan', ['action' => 'create']));
        }

        redirect(route('jurusan'));
    }

    private function update(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('jurusan_alert', 'Data jurusan tidak ditemukan.', 'danger');
            redirect(route('jurusan'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('jurusan_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('jurusan', ['action' => 'edit', 'id' => $id]));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data, true);

        if (!empty($errors)) {
            $_SESSION['jurusan_form_data'] = $data;
            $_SESSION['jurusan_errors'] = $errors;
            redirect(route('jurusan', ['action' => 'edit', 'id' => $id]));
        }

        try {
            $model = new Jurusan();
            $model->update($id, $this->mapToDb($data));
            flash('jurusan_alert', 'Data jurusan berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            $_SESSION['jurusan_form_data'] = $data;
            $_SESSION['jurusan_errors'] = ['Gagal memperbarui data: ' . $e->getMessage()];
            redirect(route('jurusan', ['action' => 'edit', 'id' => $id]));
        }

        redirect(route('jurusan'));
    }

    private function delete(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('jurusan_alert', 'Data jurusan tidak ditemukan.', 'danger');
            redirect(route('jurusan'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('jurusan_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('jurusan'));
        }

        try {
            $model = new Jurusan();
            $model->delete($id, 'id_jurusan');
            flash('jurusan_alert', 'Data jurusan berhasil dihapus.', 'success');
        } catch (PDOException $e) {
            flash('jurusan_alert', 'Gagal menghapus data: ' . $e->getMessage(), 'danger');
        }

        redirect(route('jurusan'));
    }

    private function sanitizeInput(array $input): array
    {
        return [
            'id' => (int) ($input['id'] ?? 0),
            'nama_jurusan' => trim($input['nama_jurusan'] ?? ''),
        ];
    }

    private function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if ($data['nama_jurusan'] === '') {
            $errors[] = 'Nama jurusan wajib diisi.';
        }

        return $errors;
    }

    private function mapToDb(array $data): array
    {
        return [
            'nama_jurusan' => $data['nama_jurusan'],
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
