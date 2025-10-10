<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once BASE_PATH . '/helpers/view.php';

$routes = require BASE_PATH . '/routes/web.php';
$page = $_GET['page'] ?? 'dashboard';

if (!isset($routes[$page])) {
    http_response_code(404);
    echo 'Halaman tidak ditemukan';
    exit;
}

[$controllerClass, $method] = $routes[$page];

if (!class_exists($controllerClass)) {
    http_response_code(500);
    echo 'Controller tidak ditemukan';
    exit;
}

$controller = new $controllerClass();
$result = $controller->$method();

if (is_array($result) && isset($result['view'])) {
    render_view($result['view'], $result['data'] ?? [], [
        'title' => $result['title'] ?? '',
        'styles' => $result['styles'] ?? [],
        'scripts' => $result['scripts'] ?? [],
        'breadcrumbs' => $result['breadcrumbs'] ?? [],
        'layout' => $result['layout'] ?? 'default',
    ]);
    return;
}

if (is_string($result)) {
    echo $result;
}
