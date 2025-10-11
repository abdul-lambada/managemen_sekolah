<?php

declare(strict_types=1);

final class JadwalController extends Controller
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
            'attendance' => $this->attendance(),
            default => $this->listing(),
        };
    }

    private function listing(): array
    {
        $this->requireRole('admin', 'guru');

        $filters = $this->sanitizeFilters($_GET);
        $jadwalModel = new Jadwal();
        $jadwalList = $jadwalModel->filter($filters);

        $response = $this->view('jadwal/index', [
            'jadwalList' => $jadwalList,
            'filters' => $filters,
            'kelasOptions' => (new Kelas())->options(),
            'guruOptions' => (new Guru())->options(),
            'mapelOptions' => (new MataPelajaran())->options(),
            'hariOptions' => $this->dayOptions(),
            'csrfToken' => ensure_csrf_token(),
            'alert' => flash('jadwal_alert'),
        ], 'Jadwal Pelajaran');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Jadwal Pelajaran'
        ];
        $response['breadcrumb_actions'] = [
            [
                'href' => route('jadwal', ['action' => 'create']),
                'label' => 'Tambah Jadwal',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
            ],
            [
                'href' => route('jadwal', ['action' => 'attendance']),
                'label' => 'Absensi Mapel',
                'icon' => 'fas fa-fingerprint',
                'variant' => 'info',
            ],
        ];

        return $response;
    }

    private function attendance(): array
    {
        $this->requireRole('admin', 'guru');

        $filters = $this->sanitizeAttendanceFilters($_GET);

        $absensiModel = new AbsensiGuruMapel();
        $records = $absensiModel->filter($filters);

        $response = $this->view('jadwal/attendance', [
            'records' => $records,
            'filters' => $filters,
            'kelasOptions' => (new Kelas())->options(),
            'guruOptions' => (new Guru())->options(),
            'mapelOptions' => (new MataPelajaran())->options(),
            'statusOptions' => ['Hadir', 'Izin', 'Sakit', 'Alpa', 'Terlambat'],
        ], 'Absensi Guru Mapel');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Jadwal Pelajaran' => route('jadwal'),
            'Absensi Mapel'
        ];

        return $response;
    }

    private function create(): array
    {
        $this->requireRole('admin');

        $formData = $_SESSION['jadwal_form_data'] ?? [];
        $errors = $_SESSION['jadwal_errors'] ?? [];
        unset($_SESSION['jadwal_form_data'], $_SESSION['jadwal_errors']);

        $response = $this->view('jadwal/form', [
            'isEdit' => false,
            'jadwal' => $formData,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
            'kelasOptions' => (new Kelas())->options(),
            'guruOptions' => (new Guru())->options(),
            'mapelOptions' => (new MataPelajaran())->options(),
            'hariOptions' => $this->dayOptions(),
        ], 'Tambah Jadwal');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Jadwal Pelajaran' => route('jadwal'),
            'Tambah Jadwal'
        ];

        return $response;
    }

    private function edit(): array
    {
        $this->requireRole('admin');
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            flash('jadwal_alert', 'Data jadwal tidak ditemukan.', 'danger');
            redirect(route('jadwal'));
        }

        $jadwalModel = new Jadwal();
        $jadwal = $_SESSION['jadwal_form_data'] ?? $jadwalModel->find($id, 'id_jadwal');
        $errors = $_SESSION['jadwal_errors'] ?? [];
        unset($_SESSION['jadwal_form_data'], $_SESSION['jadwal_errors']);

        if (!$jadwal) {
            flash('jadwal_alert', 'Data jadwal tidak ditemukan.', 'danger');
            redirect(route('jadwal'));
        }

        $response = $this->view('jadwal/form', [
            'isEdit' => true,
            'jadwal' => $jadwal,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
            'kelasOptions' => (new Kelas())->options(),
            'guruOptions' => (new Guru())->options(),
            'mapelOptions' => (new MataPelajaran())->options(),
            'hariOptions' => $this->dayOptions(),
        ], 'Edit Jadwal');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Jadwal Pelajaran' => route('jadwal'),
            'Edit Jadwal'
        ];

        return $response;
    }

    private function store(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('jadwal_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('jadwal', ['action' => 'create']));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['jadwal_form_data'] = $data;
            $_SESSION['jadwal_errors'] = $errors;
            redirect(route('jadwal', ['action' => 'create']));
        }

        try {
            $model = new Jadwal();
            $model->create($this->mapToDb($data));
            flash('jadwal_alert', 'Jadwal berhasil ditambahkan.', 'success');
        } catch (PDOException $e) {
            $_SESSION['jadwal_form_data'] = $data;
            $_SESSION['jadwal_errors'] = ['Gagal menyimpan data: ' . $e->getMessage()];
            redirect(route('jadwal', ['action' => 'create']));
        }

        redirect(route('jadwal'));
    }

    private function update(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('jadwal_alert', 'Data jadwal tidak ditemukan.', 'danger');
            redirect(route('jadwal'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('jadwal_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('jadwal', ['action' => 'edit', 'id' => $id]));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data, true);

        if (!empty($errors)) {
            $_SESSION['jadwal_form_data'] = $data;
            $_SESSION['jadwal_errors'] = $errors;
            redirect(route('jadwal', ['action' => 'edit', 'id' => $id]));
        }

        try {
            $model = new Jadwal();
            $model->update($id, $this->mapToDb($data));
            flash('jadwal_alert', 'Jadwal berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            $_SESSION['jadwal_form_data'] = $data;
            $_SESSION['jadwal_errors'] = ['Gagal memperbarui data: ' . $e->getMessage()];
            redirect(route('jadwal', ['action' => 'edit', 'id' => $id]));
        }

        redirect(route('jadwal'));
    }

    private function delete(): string
    {
        $this->requireRole('admin');
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('jadwal_alert', 'Data jadwal tidak ditemukan.', 'danger');
            redirect(route('jadwal'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('jadwal_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('jadwal'));
        }

        try {
            $model = new Jadwal();
            $model->delete($id, 'id_jadwal');
            flash('jadwal_alert', 'Jadwal berhasil dihapus.', 'success');
        } catch (PDOException $e) {
            flash('jadwal_alert', 'Gagal menghapus data: ' . $e->getMessage(), 'danger');
        }

        redirect(route('jadwal'));
    }

    private function sanitizeFilters(array $input): array
    {
        return [
            'kelas' => isset($input['kelas']) && $input['kelas'] !== '' ? (int) $input['kelas'] : null,
            'guru' => isset($input['guru']) && $input['guru'] !== '' ? (int) $input['guru'] : null,
            'mapel' => isset($input['mapel']) && $input['mapel'] !== '' ? (int) $input['mapel'] : null,
            'hari' => isset($input['hari']) && in_array($input['hari'], $this->dayOptions(), true) ? $input['hari'] : null,
        ];
    }

    private function sanitizeAttendanceFilters(array $input): array
    {
        return [
            'kelas' => isset($input['kelas']) && $input['kelas'] !== '' ? (int) $input['kelas'] : null,
            'guru' => isset($input['guru']) && $input['guru'] !== '' ? (int) $input['guru'] : null,
            'mapel' => isset($input['mapel']) && $input['mapel'] !== '' ? (int) $input['mapel'] : null,
            'status' => isset($input['status']) && in_array($input['status'], ['Hadir', 'Izin', 'Sakit', 'Alpa', 'Terlambat'], true) ? $input['status'] : null,
            'start' => isset($input['start']) && $this->isValidDate($input['start']) ? $input['start'] : null,
            'end' => isset($input['end']) && $this->isValidDate($input['end']) ? $input['end'] : null,
        ];
    }

    private function sanitizeInput(array $input): array
    {
        return [
            'id' => (int) ($input['id'] ?? 0),
            'id_kelas' => (int) ($input['id_kelas'] ?? 0),
            'id_guru' => (int) ($input['id_guru'] ?? 0),
            'id_mata_pelajaran' => (int) ($input['id_mata_pelajaran'] ?? 0),
            'hari' => $input['hari'] ?? '',
            'jam_mulai' => $input['jam_mulai'] ?? '',
            'jam_selesai' => $input['jam_selesai'] ?? '',
            'ruang' => trim($input['ruang'] ?? ''),
            'catatan' => trim($input['catatan'] ?? ''),
        ];
    }

    private function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if ($data['id_kelas'] <= 0) {
            $errors[] = 'Kelas wajib dipilih.';
        }

        if ($data['id_guru'] <= 0) {
            $errors[] = 'Guru wajib dipilih.';
        }

        if ($data['id_mata_pelajaran'] <= 0) {
            $errors[] = 'Mata pelajaran wajib dipilih.';
        }

        if (!in_array($data['hari'], $this->dayOptions(), true)) {
            $errors[] = 'Hari tidak valid.';
        }

        if (!$this->isValidTime($data['jam_mulai'])) {
            $errors[] = 'Jam mulai tidak valid.';
        }

        if (!$this->isValidTime($data['jam_selesai'])) {
            $errors[] = 'Jam selesai tidak valid.';
        }

        if ($this->isValidTime($data['jam_mulai']) && $this->isValidTime($data['jam_selesai'])) {
            if (strtotime($data['jam_mulai']) >= strtotime($data['jam_selesai'])) {
                $errors[] = 'Jam selesai harus lebih besar dari jam mulai.';
            }
        }

        return $errors;
    }

    private function mapToDb(array $data): array
    {
        return [
            'id_kelas' => $data['id_kelas'],
            'id_mata_pelajaran' => $data['id_mata_pelajaran'],
            'id_guru' => $data['id_guru'],
            'hari' => $data['hari'],
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'ruang' => $data['ruang'] ?: null,
            'catatan' => $data['catatan'] ?: null,
        ];
    }

    private function dayOptions(): array
    {
        return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    }

    private function isValidTime(?string $time): bool
    {
        if (!$time) {
            return false;
        }

        $dt = DateTime::createFromFormat('H:i', $time);
        return $dt !== false && $dt->format('H:i') === $time;
    }

    private function isValidDate(?string $date): bool
    {
        if (!$date) {
            return false;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $date);
        return $dt !== false && $dt->format('Y-m-d') === $date;
    }

    private function assertPost(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan');
        }
    }
}
