<?php

declare(strict_types=1);

function asset(string $path): string
{
    return APP_URL . '/public/assets/' . ltrim($path, '/');
}

function uploads_url(string $path = ''): string
{
    $relative = ltrim($path, '/');
    if (strpos($relative, 'uploads/') === 0) {
        $relative = substr($relative, strlen('uploads/'));
    }
    $fullPath = BASE_PATH . '/public/uploads/' . $relative;

    if ($relative === '' || !file_exists($fullPath)) {
        return asset('img/undraw_profile.svg');
    }

    return APP_URL . '/public/uploads/' . $relative;
}

function route(string $page, array $params = []): string
{
    $query = array_merge(['page' => $page], $params);
    return APP_URL . '/public/index.php?' . http_build_query($query);
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function has_role(string ...$roles): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }

    return in_array($user['role'], $roles, true);
}

function ensure_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    return $token !== null && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
