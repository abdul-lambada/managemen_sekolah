<?php

declare(strict_types=1);

class SiswaController extends Controller
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
            'show' => $this->show(),
            default => $this->listing(),
        };
    }

    private function listing(): array
    {
        $this->requireRole('admin');

        $model = new Siswa();
        $data = $model->allWithRelations();
        $csrfToken = ensure_csrf_token();

        $response = $this->view('siswa/index', [
            'students' => $data,
            'csrfToken' => $csrfToken,
            'alert' => flash('siswa_alert'),
        ], 'Data Siswa');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Siswa'
        ];
        $response['breadcrumb_actions'] = [
            [
                'href' => route('siswa', ['action' => 'create']),
                'label' => 'Tambah Siswa',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
            ],
        ];

        return $response;
    }

    private function show(): array
    {
        $this->requireRole('admin');
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            flash('siswa_alert', 'Data siswa tidak ditemukan.', 'danger');
            redirect(route('siswa'));
        }

        $model = new Siswa();
        $student = $model->findWithRelations($id);

        if (!$student) {
            flash('siswa_alert', 'Data siswa tidak ditemukan.', 'danger');
            redirect(route('siswa'));
        }

        $response = $this->view('siswa/show', [
            'student' => $student,
        ], 'Detail Siswa');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Siswa' => route('siswa'),
            'Detail Siswa'
        ];
        $response['breadcrumb_actions'] = [
            [
                'href' => route('siswa', ['action' => 'edit', 'id' => $id]),
                'label' => 'Edit Siswa',
                'icon' => 'fas fa-edit',
                'variant' => 'info',
            ],
        ];

        return $response;
    }

    private function create(): array
    {
        $this->requireRole('admin');

        $formData = $_SESSION['siswa_form_data'] ?? [];
        $errors = $_SESSION['siswa_errors'] ?? [];
        unset($_SESSION['siswa_form_data'], $_SESSION['siswa_errors']);

        $response = $this->view('siswa/form', [
            'isEdit' => false,
            'student' => $formData,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
            'kelasOptions' => $this->kelasOptions(),
            'userOptions' => $this->userOptions(null),
        ], 'Tambah Siswa');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Siswa' => route('siswa'),
            'Tambah Siswa'
        ];

        return $response;
    }

    private function edit(): array
    {
        $this->requireRole('admin');
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            flash('siswa_alert', 'Data siswa tidak ditemukan.', 'danger');
            redirect(route('siswa'));
        }

        $model = new Siswa();
        $student = $_SESSION['siswa_form_data'] ?? $model->findWithRelations($id);
        $errors = $_SESSION['siswa_errors'] ?? [];
        unset($_SESSION['siswa_form_data'], $_SESSION['siswa_errors']);

        if (!$student) {
            flash('siswa_alert', 'Data siswa tidak ditemukan.', 'danger');
            redirect(route('siswa'));
        }

        $response = $this->view('siswa/form', [
            'isEdit' => true,
            'student' => $student,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
            'kelasOptions' => $this->kelasOptions(),
            'userOptions' => $this->userOptions(isset($student['user_id']) ? (int) $student['user_id'] : null),
        ], 'Edit Siswa');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Siswa' => route('siswa'),
            'Edit Siswa'
        ];

        return $response;
    }

    private function store(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('siswa_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('siswa', ['action' => 'create']));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['siswa_form_data'] = $data;
            $_SESSION['siswa_errors'] = $errors;
            redirect(route('siswa', ['action' => 'create']));
        }

        try {
            $model = new Siswa();
            $model->create($this->mapToDb($data));
            flash('siswa_alert', 'Data siswa berhasil ditambahkan.', 'success');
        } catch (PDOException $e) {
            $_SESSION['siswa_form_data'] = $data;
            $_SESSION['siswa_errors'] = ['Gagal menyimpan data: ' . $e->getMessage()];
            redirect(route('siswa', ['action' => 'create']));
        }

        redirect(route('siswa'));
    }

    private function update(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('siswa_alert', 'Data siswa tidak ditemukan.', 'danger');
            redirect(route('siswa'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('siswa_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('siswa', ['action' => 'edit', 'id' => $id]));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data, true);

        if (!empty($errors)) {
            $_SESSION['siswa_form_data'] = $data;
            $_SESSION['siswa_errors'] = $errors;
            redirect(route('siswa', ['action' => 'edit', 'id' => $id]));
        }

        try {
            $model = new Siswa();
            $model->update($id, $this->mapToDb($data));
            flash('siswa_alert', 'Data siswa berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            $_SESSION['siswa_form_data'] = $data;
            $_SESSION['siswa_errors'] = ['Gagal memperbarui data: ' . $e->getMessage()];
            redirect(route('siswa', ['action' => 'edit', 'id' => $id]));
        }

        redirect(route('siswa'));
    }

    private function delete(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('siswa_alert', 'Data siswa tidak ditemukan.', 'danger');
            redirect(route('siswa'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('siswa_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('siswa'));
        }

        try {
            $model = new Siswa();
            $model->delete($id, 'id_siswa');
            flash('siswa_alert', 'Data siswa berhasil dihapus.', 'success');
        } catch (PDOException $e) {
            flash('siswa_alert', 'Gagal menghapus data: ' . $e->getMessage(), 'danger');
        }

        redirect(route('siswa'));
    }

    private function sanitizeInput(array $input): array
    {
        return [
            'id' => (int) ($input['id'] ?? 0),
            'nama_siswa' => trim($input['nama_siswa'] ?? ''),
            'nisn' => trim($input['nisn'] ?? ''),
            'nis' => trim($input['nis'] ?? ''),
            'jenis_kelamin' => $input['jenis_kelamin'] ?? '',
            'tanggal_lahir' => $input['tanggal_lahir'] ?? null,
            'alamat' => trim($input['alamat'] ?? ''),
            'id_kelas' => (int) ($input['id_kelas'] ?? 0),
            'phone' => trim($input['phone'] ?? ''),
            'user_id' => isset($input['user_id']) && $input['user_id'] !== '' ? (int) $input['user_id'] : null,
        ];
    }

    private function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if ($data['nama_siswa'] === '') {
            $errors[] = 'Nama siswa wajib diisi.';
        }

        if ($data['nisn'] === '') {
            $errors[] = 'NISN wajib diisi.';
        }

        if ($data['nis'] === '') {
            $errors[] = 'NIS wajib diisi.';
        }

        if (!in_array($data['jenis_kelamin'], ['Laki-laki', 'Perempuan'], true)) {
            $errors[] = 'Jenis kelamin tidak valid.';
        }

        if ($data['alamat'] === '') {
            $errors[] = 'Alamat wajib diisi.';
        }

        if ($data['id_kelas'] <= 0) {
            $errors[] = 'Kelas wajib dipilih.';
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

        return $errors;
    }

    private function mapToDb(array $data): array
    {
        return [
            'nama_siswa' => $data['nama_siswa'],
            'nisn' => $data['nisn'],
            'nis' => $data['nis'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tanggal_lahir' => $data['tanggal_lahir'] ?: null,
            'alamat' => $data['alamat'],
            'id_kelas' => $data['id_kelas'],
            'phone' => $data['phone'] ?: null,
            'user_id' => $data['user_id'] ?? null,
        ];
    }

    private function kelasOptions(): array
    {
        $kelas = new Kelas();
        return $kelas->options();
    }

    private function userOptions(?int $currentUserId = null): array
    {
        $pdo = db();

        $usedStmt = $pdo->query(
            "SELECT user_id FROM guru WHERE user_id IS NOT NULL
             UNION
             SELECT user_id FROM siswa WHERE user_id IS NOT NULL"
        );
        $usedIds = array_map('intval', $usedStmt->fetchAll(PDO::FETCH_COLUMN));
        $usedIds = array_values(array_filter($usedIds, static fn (int $id): bool => $id > 0));

        if ($currentUserId !== null && $currentUserId > 0) {
            $usedIds = array_values(array_filter($usedIds, static fn (int $id): bool => $id !== $currentUserId));
        }

        $params = [];
        $sql = "SELECT id, name, role FROM users";
        if (!empty($usedIds)) {
            $placeholders = implode(',', array_fill(0, count($usedIds), '?'));
            $sql .= " WHERE id NOT IN ($placeholders)";
            $params = array_map('intval', $usedIds);
        }
        $sql .= " ORDER BY name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($currentUserId !== null && $currentUserId > 0) {
            $hasCurrent = false;
            foreach ($options as $option) {
                if ((int) $option['id'] === $currentUserId) {
                    $hasCurrent = true;
                    break;
                }
            }

            if (!$hasCurrent) {
                $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$currentUserId]);
                $current = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($current) {
                    array_unshift($options, $current);
                }
            }
        }

        return $options;
    }

    private function assertPost(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan');
        }
    }
}
