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
            'show' => $this->show(),
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

    private function show(): array
    {
        $this->requireRole('admin');
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            flash('guru_alert', 'Data guru tidak ditemukan.', 'danger');
            redirect(route('guru'));
        }

        $guruModel = new Guru();
        $guru = $guruModel->findWithUser($id);

        if (!$guru) {
            flash('guru_alert', 'Data guru tidak ditemukan.', 'danger');
            redirect(route('guru'));
        }

        $response = $this->view('guru/show', [
            'guru' => $guru,
        ], 'Detail Guru');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Data Guru' => route('guru'),
            'Detail Guru'
        ];
        $response['breadcrumb_actions'] = [
            [
                'href' => route('guru', ['action' => 'edit', 'id' => $id]),
                'label' => 'Edit Guru',
                'icon' => 'fas fa-edit',
                'variant' => 'info',
            ],
        ];

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
            'userOptions' => $this->userOptions(null),
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
            'userOptions' => $this->userOptions(isset($guru['user_id']) ? (int) $guru['user_id'] : null),
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

            // Log sebelum create
            error_log("Creating new guru: {$data['nama_guru']} (NIP: {$data['nip']})");

            $guruId = $model->create($this->mapToDb($data));

            if ($guruId) {
                error_log("Guru created successfully with ID: $guruId");
                flash('guru_alert', "Data guru <strong>{$data['nama_guru']}</strong> berhasil ditambahkan.", 'success');
            } else {
                throw new Exception('Gagal mendapatkan ID guru setelah create');
            }

        } catch (PDOException $e) {
            error_log("Database error creating guru: " . $e->getMessage());
            $_SESSION['guru_form_data'] = $data;
            $_SESSION['guru_errors'] = ['Gagal menyimpan data: ' . $e->getMessage()];
            redirect(route('guru', ['action' => 'create']));
        } catch (Exception $e) {
            error_log("Error creating guru: " . $e->getMessage());
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
        $data['id'] = $id; // Pastikan ID disertakan untuk validasi
        $errors = $this->validate($data, true);

        if (!empty($errors)) {
            $_SESSION['guru_form_data'] = $data;
            $_SESSION['guru_errors'] = $errors;
            redirect(route('guru', ['action' => 'edit', 'id' => $id]));
        }

        try {
            $model = new Guru();

            // Log sebelum update
            error_log("Updating guru ID: $id - {$data['nama_guru']} (NIP: {$data['nip']})");

            $updated = $model->update($id, $this->mapToDb($data));

            if ($updated) {
                error_log("Guru updated successfully: $id");
                flash('guru_alert', "Data guru <strong>{$data['nama_guru']}</strong> berhasil diperbarui.", 'success');
            } else {
                throw new Exception('Tidak ada perubahan data atau guru tidak ditemukan');
            }

        } catch (PDOException $e) {
            error_log("Database error updating guru $id: " . $e->getMessage());
            $_SESSION['guru_form_data'] = $data;
            $_SESSION['guru_errors'] = ['Gagal memperbarui data: ' . $e->getMessage()];
            redirect(route('guru', ['action' => 'edit', 'id' => $id]));
        } catch (Exception $e) {
            error_log("Error updating guru $id: " . $e->getMessage());
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

            // Ambil data guru sebelum hapus untuk logging
            $guru = $model->find($id);
            if (!$guru) {
                throw new Exception('Data guru tidak ditemukan');
            }

            error_log("Deleting guru ID: $id - {$guru['nama_guru']} (NIP: {$guru['nip']})");

            $deleted = $model->delete($id, 'id_guru');

            if ($deleted) {
                error_log("Guru deleted successfully: $id");
                flash('guru_alert', "Data guru <strong>{$guru['nama_guru']}</strong> berhasil dihapus.", 'success');
            } else {
                throw new Exception('Gagal menghapus data guru');
            }

        } catch (PDOException $e) {
            error_log("Database error deleting guru $id: " . $e->getMessage());
            flash('guru_alert', 'Gagal menghapus data: ' . $e->getMessage(), 'danger');
        } catch (Exception $e) {
            error_log("Error deleting guru $id: " . $e->getMessage());
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
            'user_id' => isset($input['user_id']) && $input['user_id'] !== '' ? (int) $input['user_id'] : null,
        ];
    }

    private function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        // Validasi nama guru
        if (empty(trim($data['nama_guru']))) {
            $errors[] = 'Nama guru wajib diisi.';
        } elseif (strlen($data['nama_guru']) < 2) {
            $errors[] = 'Nama guru minimal 2 karakter.';
        } elseif (strlen($data['nama_guru']) > 100) {
            $errors[] = 'Nama guru maksimal 100 karakter.';
        }

        // Validasi NIP
        if (empty(trim($data['nip']))) {
            $errors[] = 'NIP wajib diisi.';
        } elseif (!preg_match('/^[0-9]+$/', $data['nip'])) {
            $errors[] = 'NIP harus berupa angka.';
        } elseif (strlen($data['nip']) < 5) {
            $errors[] = 'NIP minimal 5 digit.';
        } elseif (strlen($data['nip']) > 20) {
            $errors[] = 'NIP maksimal 20 digit.';
        } elseif (!$isUpdate) {
            // Cek duplikasi NIP hanya saat create
            $pdo = db();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM guru WHERE nip = ?");
            $stmt->execute([$data['nip']]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'NIP sudah digunakan.';
            }
        }

        // Validasi jenis kelamin
        if (!in_array($data['jenis_kelamin'], ['Laki-laki', 'Perempuan'], true)) {
            $errors[] = 'Jenis kelamin tidak valid.';
        }

        // Validasi alamat
        if (empty(trim($data['alamat']))) {
            $errors[] = 'Alamat wajib diisi.';
        } elseif (strlen($data['alamat']) < 10) {
            $errors[] = 'Alamat minimal 10 karakter.';
        }

        // Validasi nomor telepon (opsional tapi harus valid jika diisi)
        if (!empty($data['phone']) && !preg_match('/^[0-9+\- ]+$/', $data['phone'])) {
            $errors[] = 'Nomor telepon tidak valid (hanya angka, +, -, spasi).';
        } elseif (!empty($data['phone']) && strlen($data['phone']) < 10) {
            $errors[] = 'Nomor telepon minimal 10 digit.';
        }

        // Validasi tanggal lahir (opsional tapi harus valid jika diisi)
        if (!empty($data['tanggal_lahir'])) {
            $d = DateTime::createFromFormat('Y-m-d', $data['tanggal_lahir']);
            if (!$d || $d->format('Y-m-d') !== $data['tanggal_lahir']) {
                $errors[] = 'Tanggal lahir tidak valid.';
            } elseif ($d > new DateTime()) {
                $errors[] = 'Tanggal lahir tidak boleh lebih dari hari ini.';
            }
        }

        // Validasi user_id jika diisi
        if (!empty($data['user_id'])) {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ? AND role = 'guru'");
            $stmt->execute([$data['user_id']]);
            if ($stmt->fetchColumn() === 0) {
                $errors[] = 'User yang dipilih tidak valid atau bukan role guru.';
            }
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
            'user_id' => $data['user_id'] ?? null,
        ];
    }

    private function userOptions(?int $currentUserId = null): array
    {
        $pdo = db();

        // Hanya ambil user yang sudah digunakan oleh guru (untuk menghindari konflik)
        $usedStmt = $pdo->query("SELECT user_id FROM guru WHERE user_id IS NOT NULL");
        $usedIds = array_map('intval', $usedStmt->fetchAll(PDO::FETCH_COLUMN));
        $usedIds = array_values(array_filter($usedIds, static fn (int $id): bool => $id > 0));

        // Jika sedang edit, pastikan current user tetap tersedia
        if ($currentUserId !== null && $currentUserId > 0) {
            $usedIds = array_values(array_filter($usedIds, static fn (int $id): bool => $id !== $currentUserId));
        }

        // Ambil semua user dengan role 'guru' yang belum digunakan
        $params = [];
        $sql = "SELECT id, name, role FROM users WHERE role = 'guru'";
        if (!empty($usedIds)) {
            $placeholders = implode(',', array_fill(0, count($usedIds), '?'));
            $sql .= " AND id NOT IN ($placeholders)";
            $params = array_map('intval', $usedIds);
        }
        $sql .= " ORDER BY name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Jika sedang edit dan current user tidak dalam daftar, tambahkan
        if ($currentUserId !== null && $currentUserId > 0) {
            $hasCurrent = false;
            foreach ($options as $option) {
                if ((int) $option['id'] === $currentUserId) {
                    $hasCurrent = true;
                    break;
                }
            }

            if (!$hasCurrent) {
                $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE id = ? AND role = 'guru' LIMIT 1");
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
