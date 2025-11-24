<?php

class KelasController extends Controller
{
    public function index()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';

        switch ($action) {
            case 'create':
                return $this->create();
            case 'store':
                return $this->store();
            case 'edit':
                return $this->edit();
            case 'update':
                return $this->update();
            case 'delete':
                return $this->delete();
            case 'show':
                return $this->show();
            default:
                return $this->listing();
        }
    }

    private function listing()
    {
        $this->requireRole('admin');

        $model = new Kelas();
        $kelas = $model->allWithJurusan();
        $csrfToken = ensure_csrf_token();

        $response = $this->view('kelas/index', [
            'kelasList' => $kelas,
            'csrfToken' => $csrfToken,
            'alert' => flash('kelas_alert'),
        ], 'Data Kelas');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Kelas'
        ];
        $response['breadcrumb_actions'] = [
            [
                'href' => route('kelas', ['action' => 'create']),
                'label' => 'Tambah Kelas',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
            ],
        ];

        return $response;
    }

    private function show()
    {
        $this->requireRole('admin');
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            flash('kelas_alert', 'Data kelas tidak ditemukan.', 'danger');
            redirect(route('kelas'));
        }

        $model = new Kelas();
        $kelas = $model->findWithJurusan($id);

        if (!$kelas) {
            flash('kelas_alert', 'Data kelas tidak ditemukan.', 'danger');
            redirect(route('kelas'));
        }

        $siswaModel = new Siswa();
        $students = $siswaModel->byKelas($id);

        $response = $this->view('kelas/show', [
            'kelas' => $kelas,
            'students' => $students,
        ], 'Detail Kelas');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Kelas' => route('kelas'),
            'Detail Kelas'
        ];
        $response['breadcrumb_actions'] = [
            [
                'href' => route('kelas', ['action' => 'edit', 'id' => $id]),
                'label' => 'Edit Kelas',
                'icon' => 'fas fa-edit',
                'variant' => 'info',
            ],
        ];

        return $response;
    }

    private function create()
    {
        $this->requireRole('admin');

        $formData = $_SESSION['kelas_form_data'] ?? [];
        $errors = $_SESSION['kelas_errors'] ?? [];
        unset($_SESSION['kelas_form_data'], $_SESSION['kelas_errors']);

        $response = $this->view('kelas/form', [
            'isEdit' => false,
            'kelas' => $formData,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
            'jurusanOptions' => $this->jurusanOptions(),
        ], 'Tambah Kelas');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Kelas' => route('kelas'),
            'Tambah Kelas'
        ];

        return $response;
    }

    private function edit()
    {
        $this->requireRole('admin');
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            flash('kelas_alert', 'Data kelas tidak ditemukan.', 'danger');
            redirect(route('kelas'));
        }

        $model = new Kelas();
        $kelas = $_SESSION['kelas_form_data'] ?? $model->find($id, 'id_kelas');
        $errors = $_SESSION['kelas_errors'] ?? [];
        unset($_SESSION['kelas_form_data'], $_SESSION['kelas_errors']);

        if (!$kelas) {
            flash('kelas_alert', 'Data kelas tidak ditemukan.', 'danger');
            redirect(route('kelas'));
        }

        $response = $this->view('kelas/form', [
            'isEdit' => true,
            'kelas' => $kelas,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
            'jurusanOptions' => $this->jurusanOptions(),
        ], 'Edit Kelas');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Kelas' => route('kelas'),
            'Edit Kelas'
        ];

        return $response;
    }

    private function store()
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('kelas_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('kelas', ['action' => 'create']));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['kelas_form_data'] = $data;
            $_SESSION['kelas_errors'] = $errors;
            redirect(route('kelas', ['action' => 'create']));
        }

        try {
            $model = new Kelas();
            $model->create($this->mapToDb($data));
            flash('kelas_alert', 'Data kelas berhasil ditambahkan.', 'success');
        } catch (PDOException $e) {
            $_SESSION['kelas_form_data'] = $data;
            $_SESSION['kelas_errors'] = ['Gagal menyimpan data: ' . $e->getMessage()];
            redirect(route('kelas', ['action' => 'create']));
        }

        redirect(route('kelas'));
    }

    private function update()
    {
        $this->requireRole('admin');
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('kelas_alert', 'Data kelas tidak ditemukan.', 'danger');
            redirect(route('kelas'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('kelas_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('kelas', ['action' => 'edit', 'id' => $id]));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data, true);

        if (!empty($errors)) {
            $_SESSION['kelas_form_data'] = $data;
            $_SESSION['kelas_errors'] = $errors;
            redirect(route('kelas', ['action' => 'edit', 'id' => $id]));
        }

        try {
            $model = new Kelas();
            $model->update($id, $this->mapToDb($data));
            flash('kelas_alert', 'Data kelas berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            $_SESSION['kelas_form_data'] = $data;
            $_SESSION['kelas_errors'] = ['Gagal memperbarui data: ' . $e->getMessage()];
            redirect(route('kelas', ['action' => 'edit', 'id' => $id]));
        }

        redirect(route('kelas'));
    }

    private function delete()
    {
        $this->requireRole('admin');
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('kelas_alert', 'Data kelas tidak ditemukan.', 'danger');
            redirect(route('kelas'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('kelas_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('kelas'));
        }

        try {
            $model = new Kelas();
            $model->delete($id, 'id_kelas');
            flash('kelas_alert', 'Data kelas berhasil dihapus.', 'success');
        } catch (PDOException $e) {
            flash('kelas_alert', 'Gagal menghapus data: ' . $e->getMessage(), 'danger');
        }

        redirect(route('kelas'));
    }

    private function sanitizeInput($input)
    {
        return [
            'id' => (int) ($input['id'] ?? 0),
            'nama_kelas' => trim($input['nama_kelas'] ?? ''),
            'id_jurusan' => (int) ($input['id_jurusan'] ?? 0),
        ];
    }

    private function validate($data, $isUpdate = false)
    {
        $errors = [];

        if ($data['nama_kelas'] === '') {
            $errors[] = 'Nama kelas wajib diisi.';
        }

        if ($data['id_jurusan'] <= 0) {
            $errors[] = 'Jurusan wajib dipilih.';
        }

        return $errors;
    }

    private function mapToDb($data)
    {
        return [
            'nama_kelas' => $data['nama_kelas'],
            'id_jurusan' => $data['id_jurusan'],
        ];
    }

    private function jurusanOptions()
    {
        $jurusan = new Jurusan();
        return $jurusan->options();
    }

    private function assertPost()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan');
        }
    }
}
