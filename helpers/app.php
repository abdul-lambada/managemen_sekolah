<?php

declare(strict_types=1);

function asset(string $path): string
{
    return APP_URL . '/public/assets/' . ltrim($path, '/');
}

function activity_log(string $action, ?string $description = null): void
{
    try {
        $user = current_user();
        $userId = $user['id'] ?? null;
        $model = new ActivityLog();
        $model->record($userId, $action, $description);
    } catch (Throwable $e) {
        error_log('[activity_log] ' . $e->getMessage());
    }
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
    $valid = $token !== null && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);

    if ($valid) {
        unset($_SESSION['csrf_token']);
        ensure_csrf_token();
    }

    return $valid;
}

function ensure_settings_table(): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    $sql = "CREATE TABLE IF NOT EXISTS settings (
        option_key VARCHAR(100) NOT NULL PRIMARY KEY,
        option_value TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    db()->exec($sql);
    $ensured = true;
}

function app_settings(bool $refresh = false): array
{
    static $cache = null;

    if ($cache !== null && !$refresh) {
        return $cache;
    }

    $defaults = [
        'app_name' => APP_NAME,
        'app_tagline' => '',
        'favicon' => null,
        'attendance_morning_start' => null,
        'attendance_morning_end' => null,
        'attendance_evening_start' => null,
        'attendance_evening_end' => null,
    ];

    try {
        ensure_settings_table();

        $stmt = db()->prepare("SELECT option_key, option_value FROM settings WHERE option_key IN ('app_name', 'app_tagline', 'favicon', 'attendance_morning_start', 'attendance_morning_end', 'attendance_evening_start', 'attendance_evening_end')");
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $values = $defaults;
        foreach ($rows as $row) {
            $key = $row['option_key'];
            if (array_key_exists($key, $values)) {
                $values[$key] = $row['option_value'];
            }
        }

        $cache = $values;
    } catch (Throwable $e) {
        $cache = $defaults;
    }

    return $cache;
}
