<?php

declare(strict_types=1);

function flash(string $key, ?string $message = null, string $type = 'success'): ?array
{
    if ($message === null) {
        if (!isset($_SESSION['flash'][$key])) {
            return null;
        }

        $data = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $data;
    }

    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type,
    ];

    return null;
}
