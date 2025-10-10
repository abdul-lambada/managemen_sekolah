<?php

declare(strict_types=1);

final class PublicPengaduanController extends Controller
{
    public function form(): array
    {
        ensure_csrf_token();

        $formData = $_SESSION['pengaduan_form_data'] ?? [];
        $errors = $_SESSION['pengaduan_errors'] ?? [];
        unset($_SESSION['pengaduan_form_data'], $_SESSION['pengaduan_errors']);

        return [
            'view' => 'pengaduan/public_form',
            'data' => [
                'csrfToken' => $_SESSION['csrf_token'],
                'formData' => $formData,
                'errors' => $errors,
                'flash' => flash('pengaduan_public'),
            ],
            'title' => 'Form Pengaduan',
            'layout' => 'auth',
        ];
    }

    public function submit(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect(route('pengaduan_form'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('pengaduan_public', 'Sesi berakhir. Silakan isi kembali formulir.', 'danger');
            redirect(route('pengaduan_form'));
        }

        $input = $this->sanitizeInput($_POST);
        $errors = $this->validate($input);

        $filePath = null;
        if (empty($errors) && isset($_FILES['file_pendukung']) && $_FILES['file_pendukung']['error'] !== UPLOAD_ERR_NO_FILE) {
            [$filePath, $fileError] = $this->handleUpload($_FILES['file_pendukung']);
            if ($fileError !== null) {
                $errors[] = $fileError;
            }
        }

        if (!empty($errors)) {
            $_SESSION['pengaduan_form_data'] = $input;
            $_SESSION['pengaduan_errors'] = $errors;
            redirect(route('pengaduan_form'));
        }

        $model = new Pengaduan();

        try {
            $model->createPengaduan([
                'nama_pelapor' => $input['nama_pelapor'],
                'no_wa' => $input['no_wa'] ?: null,
                'email_pelapor' => $input['email_pelapor'] ?: null,
                'role_pelapor' => $input['role_pelapor'],
                'kategori' => $input['kategori'],
                'judul_pengaduan' => $input['judul_pengaduan'],
                'isi_pengaduan' => $input['isi_pengaduan'],
                'keterangan' => $input['keterangan'] ?: null,
                'file_pendukung' => $filePath,
                'status' => 'pending',
            ]);
        } catch (PDOException $e) {
            $_SESSION['pengaduan_form_data'] = $input;
            flash('pengaduan_public', 'Pengaduan gagal dikirim: ' . $e->getMessage(), 'danger');
            redirect(route('pengaduan_form'));
        }

        unset($_SESSION['pengaduan_form_data'], $_SESSION['pengaduan_errors']);
        flash('pengaduan_public', 'Terima kasih, pengaduan Anda telah diterima.', 'success');
        redirect(route('pengaduan_form'));
    }

    private function sanitizeInput(array $input): array
    {
        return [
            'nama_pelapor' => trim($input['nama_pelapor'] ?? ''),
            'no_wa' => preg_replace('/[^0-9+]/', '', $input['no_wa'] ?? ''),
            'email_pelapor' => trim($input['email_pelapor'] ?? ''),
            'role_pelapor' => trim($input['role_pelapor'] ?? ''),
            'kategori' => trim($input['kategori'] ?? ''),
            'judul_pengaduan' => trim($input['judul_pengaduan'] ?? ''),
            'isi_pengaduan' => trim($input['isi_pengaduan'] ?? ''),
            'keterangan' => trim($input['keterangan'] ?? ''),
        ];
    }

    private function validate(array $input): array
    {
        $errors = [];

        if ($input['nama_pelapor'] === '') {
            $errors[] = 'Nama pelapor wajib diisi.';
        }

        if ($input['judul_pengaduan'] === '') {
            $errors[] = 'Judul pengaduan wajib diisi.';
        }

        if ($input['isi_pengaduan'] === '') {
            $errors[] = 'Isi pengaduan wajib diisi.';
        }

        $validRoles = ['siswa', 'guru', 'umum'];
        if (!in_array($input['role_pelapor'], $validRoles, true)) {
            $errors[] = 'Peran pelapor tidak valid.';
        }

        $validCategories = ['saran', 'kritik', 'pembelajaran', 'organisasi', 'administrasi', 'lainnya'];
        if (!in_array($input['kategori'], $validCategories, true)) {
            $errors[] = 'Kategori pengaduan tidak valid.';
        }

        if ($input['email_pelapor'] !== '' && !filter_var($input['email_pelapor'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Alamat email pelapor tidak valid.';
        }

        if ($input['no_wa'] !== '' && !preg_match('/^[0-9+]{8,15}$/', $input['no_wa'])) {
            $errors[] = 'Nomor WhatsApp tidak valid.';
        }

        return $errors;
    }

    private function handleUpload(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Gagal mengunggah file pendukung.'];
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if ($file['size'] > $maxSize) {
            return [null, 'Ukuran file pendukung maksimal 2MB.'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            return [null, 'Format file pendukung harus JPG, PNG, atau PDF.'];
        }

        $uploadDir = BASE_PATH . '/public/uploads/pengaduan';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return [null, 'Gagal membuat direktori penyimpanan file.'];
        }

        $filename = uniqid('pengaduan_', true) . '.' . $extension;
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return [null, 'Gagal menyimpan file pendukung.'];
        }

        return ['uploads/pengaduan/' . $filename, null];
    }
}
