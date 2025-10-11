<?php

declare(strict_types=1);

use DateTimeImmutable;

class AuthController extends Controller
{
    public function showLogin(): array
    {
        if (current_user()) {
            redirect(route('dashboard'));
        }

        ensure_csrf_token();

        return [
            'view' => 'auth/login',
            'data' => [
                'csrfToken' => $_SESSION['csrf_token'],
                'flash' => flash('auth_error'),
                'flashSuccess' => flash('auth_success'),
            ],
            'title' => 'Masuk',
            'layout' => 'auth',
        ];
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(route('login'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('auth_error', 'Sesi berakhir. Silakan coba lagi.', 'danger');
            redirect(route('login'));
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            flash('auth_error', 'Nama pengguna dan kata sandi wajib diisi.', 'danger');
            redirect(route('login'));
        }

        $userModel = new User();
        $user = $userModel->findByName($username);

        if (!$user || !password_verify($password, $user['password'])) {
            activity_log('auth.login_failed', 'Login gagal untuk username ' . $username);
            flash('auth_error', 'Kredensial tidak valid.', 'danger');
            redirect(route('login'));
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
            'avatar' => $user['avatar'] ?? null,
        ];

        activity_log('auth.login', 'Pengguna #' . $user['id'] . ' masuk.');
        flash('auth_success', 'Selamat datang kembali, ' . $user['name'] . '!', 'success');
        if ($user['role'] === 'siswa') {
            redirect(route('portal_siswa'));
        }

        redirect(route('dashboard'));
    }

    public function forgotPassword(): array
    {
        if (current_user()) {
            redirect(route('dashboard'));
        }

        ensure_csrf_token();

        return [
            'view' => 'auth/forgot_password',
            'data' => [
                'csrfToken' => $_SESSION['csrf_token'],
                'flashError' => flash('auth_error'),
            ],
            'title' => 'Lupa Kata Sandi',
            'layout' => 'auth',
        ];
    }

    public function processForgotPassword(): array|string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(route('forgot_password'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('auth_error', 'Sesi berakhir. Silakan coba lagi.', 'danger');
            redirect(route('forgot_password'));
        }

        $identifier = trim($_POST['identifier'] ?? '');

        if ($identifier === '') {
            flash('auth_error', 'Email atau nama pengguna wajib diisi.', 'danger');
            redirect(route('forgot_password'));
        }

        $userModel = new User();
        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $userModel->findByEmail($identifier)
            : $userModel->findByName($identifier);

        if (!$user) {
            flash('auth_error', 'Pengguna tidak ditemukan.', 'danger');
            redirect(route('forgot_password'));
        }

        $selector = bin2hex(random_bytes(8));
        $token = bin2hex(random_bytes(32));
        $expiresAt = new DateTimeImmutable('+30 minutes');

        $resetModel = new PasswordReset();
        $resetModel->createToken((int) $user['id'], $selector, hash('sha256', $token), $expiresAt);

        activity_log('auth.reset_request', 'Token reset dibuat untuk pengguna #' . $user['id']);

        $resetUrl = route('reset_password', [
            'selector' => $selector,
            'token' => $token,
        ]);

        return [
            'view' => 'auth/forgot_password_success',
            'data' => [
                'resetUrl' => $resetUrl,
                'selector' => $selector,
                'token' => $token,
                'expiresAt' => $expiresAt->format('Y-m-d H:i'),
                'user' => $user,
            ],
            'title' => 'Instruksi Reset Password',
            'layout' => 'auth',
        ];
    }

    public function showResetForm(): array
    {
        if (current_user()) {
            redirect(route('dashboard'));
        }

        $selector = $_GET['selector'] ?? '';
        $token = $_GET['token'] ?? '';

        if ($selector === '' || $token === '') {
            flash('auth_error', 'Link reset tidak valid.', 'danger');
            redirect(route('forgot_password'));
        }

        $resetModel = new PasswordReset();
        $record = $resetModel->findValidBySelector($selector);

        if (!$record || !hash_equals($record['token_hash'], hash('sha256', $token))) {
            flash('auth_error', 'Token reset tidak valid atau sudah kedaluwarsa.', 'danger');
            redirect(route('forgot_password'));
        }

        ensure_csrf_token();

        return [
            'view' => 'auth/reset_password',
            'data' => [
                'csrfToken' => $_SESSION['csrf_token'],
                'selector' => $selector,
                'token' => $token,
                'flashError' => flash('auth_error'),
            ],
            'title' => 'Reset Kata Sandi',
            'layout' => 'auth',
        ];
    }

    public function resetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(route('forgot_password'));
        }

        $selector = $_POST['selector'] ?? '';
        $token = $_POST['token'] ?? '';

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('auth_error', 'Sesi berakhir. Silakan ulangi proses reset.', 'danger');
            redirect(route('reset_password', ['selector' => $selector, 'token' => $token]));
        }

        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirmation'] ?? '';

        if ($password === '' || $confirm === '') {
            flash('auth_error', 'Kata sandi baru wajib diisi.', 'danger');
            redirect(route('reset_password', ['selector' => $selector, 'token' => $token]));
        }

        if ($password !== $confirm) {
            flash('auth_error', 'Konfirmasi kata sandi tidak sama.', 'danger');
            redirect(route('reset_password', ['selector' => $selector, 'token' => $token]));
        }

        if (strlen($password) < 8) {
            flash('auth_error', 'Kata sandi minimal 8 karakter.', 'danger');
            redirect(route('reset_password', ['selector' => $selector, 'token' => $token]));
        }

        $resetModel = new PasswordReset();
        $record = $resetModel->findValidBySelector($selector);

        if (!$record || !hash_equals($record['token_hash'], hash('sha256', $token))) {
            flash('auth_error', 'Token reset tidak valid atau sudah kedaluwarsa.', 'danger');
            redirect(route('forgot_password'));
        }

        $userModel = new User();
        $user = $userModel->findById((int) $record['user_id']);

        if (!$user) {
            flash('auth_error', 'Pengguna tidak ditemukan.', 'danger');
            redirect(route('forgot_password'));
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $userModel->updatePassword((int) $user['id'], $hashedPassword);
        $resetModel->deleteById((int) $record['id']);

        activity_log('auth.reset_password', 'Kata sandi diperbarui untuk pengguna #' . $user['id']);

        flash('auth_success', 'Kata sandi berhasil diperbarui. Silakan login.', 'success');
        redirect(route('login'));
    }

    public function logout(): void
    {
        $user = $_SESSION['user'] ?? null;
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        if ($user) {
            activity_log('auth.logout', 'Pengguna #' . $user['id'] . ' keluar.');
        }
        redirect(route('login'));
    }
}
