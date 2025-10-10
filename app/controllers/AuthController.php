<?php

declare(strict_types=1);

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
            flash('auth_error', 'Kredensial tidak valid.', 'danger');
            redirect(route('login'));
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
            'avatar' => $user['avatar'] ?? null,
        ];

        flash('auth_success', 'Selamat datang kembali, ' . $user['name'] . '!', 'success');
        redirect(route('dashboard'));
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        redirect(route('login'));
    }
}
