<?php

declare(strict_types=1);

final class ProfileController extends Controller
{
    public function index(): array
    {
        $this->requireRole('admin', 'guru', 'siswa');

        $user = current_user();
        if (!$user) {
            redirect(route('login'));
        }

        $response = $this->view('profile/index', [
            'user' => $user,
            'alert' => flash('profile_alert'),
        ], 'Profil');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Profil'
        ];

        return $response;
    }

    public function edit(): array
    {
        $this->requireRole('admin', 'guru', 'siswa');

        $user = current_user();
        if (!$user) {
            redirect(route('login'));
        }

        $formData = $_SESSION['profile_form'] ?? $user;
        $errors = $_SESSION['profile_errors'] ?? [];
        unset($_SESSION['profile_form'], $_SESSION['profile_errors']);

        $response = $this->view('profile/edit', [
            'user' => $user,
            'formData' => $formData,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
            'alert' => flash('profile_alert'),
        ], 'Edit Profil');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Profil' => route('profile'),
            'Edit'
        ];

        return $response;
    }

    public function update(): string
    {
        $this->requireRole('admin', 'guru', 'siswa');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('profile_alert', 'Token tidak valid.', 'danger');
            redirect(route('profile_edit'));
        }

        $user = current_user();
        if (!$user) {
            redirect(route('login'));
        }

        $data = $this->sanitizeProfile($_POST);
        $errors = $this->validateProfile($data, $_FILES['avatar'] ?? null);

        if (!empty($errors)) {
            $_SESSION['profile_form'] = $data;
            $_SESSION['profile_errors'] = $errors;
            redirect(route('profile_edit'));
        }

        $updateData = [
            'name' => $data['name'],
            'phone' => $data['phone'] ?: null,
        ];

        if ($data['password'] !== '') {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (!empty($_FILES['avatar']['name'])) {
            try {
                $relative = $this->handleAvatarUpload($_FILES['avatar'], $user['id']);
                $updateData['avatar'] = $relative;
            } catch (RuntimeException $e) {
                flash('profile_alert', $e->getMessage(), 'danger');
                $_SESSION['profile_form'] = $data;
                redirect(route('profile_edit'));
            }
        }

        $userModel = new User();

        try {
            $userModel->update($user['id'], $updateData);
            $_SESSION['user']['name'] = $updateData['name'];
            if ($updateData['phone'] !== null) {
                $_SESSION['user']['phone'] = $updateData['phone'];
            } elseif (isset($_SESSION['user']['phone'])) {
                unset($_SESSION['user']['phone']);
            }
            if (isset($updateData['avatar'])) {
                $_SESSION['user']['avatar'] = $updateData['avatar'];
            }
            flash('profile_alert', 'Profil berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            flash('profile_alert', 'Gagal memperbarui profil: ' . $e->getMessage(), 'danger');
            redirect(route('profile_edit'));
        }

        redirect(route('profile'));
    }

    private function sanitizeProfile(array $input): array
    {
        return [
            'name' => trim($input['name'] ?? ''),
            'phone' => trim($input['phone'] ?? ''),
            'password' => $input['password'] ?? '',
            'password_confirmation' => $input['password_confirmation'] ?? '',
        ];
    }

    private function validateProfile(array $data, ?array $avatar = null): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'Nama wajib diisi.';
        }

        if ($data['phone'] !== '' && !preg_match('/^[0-9+\-\s]{6,20}$/', $data['phone'])) {
            $errors[] = 'Nomor telepon tidak valid.';
        }

        if ($data['password'] !== '') {
            if (strlen($data['password']) < 6) {
                $errors[] = 'Kata sandi minimal 6 karakter.';
            }

            if ($data['password'] !== $data['password_confirmation']) {
                $errors[] = 'Konfirmasi kata sandi tidak sesuai.';
            }
        }

        if ($avatar && !empty($avatar['name'])) {
            if ($avatar['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Gagal mengunggah avatar.';
            } else {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $mime = mime_content_type($avatar['tmp_name']);
                if (!in_array($mime, $allowedTypes, true)) {
                    $errors[] = 'Avatar harus berupa gambar JPG, PNG, atau WEBP.';
                }

                if ($avatar['size'] > 2 * 1024 * 1024) {
                    $errors[] = 'Ukuran avatar maksimal 2MB.';
                }
            }
        }

        return $errors;
    }

    private function handleAvatarUpload(array $file, int $userId): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Gagal mengunggah avatar.');
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $fileName = sprintf('avatar_%d_%s.%s', $userId, time(), strtolower($extension));
        $relativePath = 'uploads/avatar/' . $fileName;
        $targetDir = BASE_PATH . '/public/uploads/avatar';
        $targetPath = BASE_PATH . '/public/' . $relativePath;

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Tidak dapat membuat direktori upload.');
        }

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Gagal menyimpan avatar.');
        }

        return $relativePath;
    }

    private function assertPost(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan');
        }
    }
}
