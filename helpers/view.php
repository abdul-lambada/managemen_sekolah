<?php

declare(strict_types=1);

function render_view(string $view, array $data = [], array $options = []): void
{
    $viewPath = BASE_PATH . '/pages/' . $view . '.php';

    if (!file_exists($viewPath)) {
        http_response_code(404);
        echo 'View not found';
        return;
    }

    $layout = $options['layout'] ?? 'default';
    $title = $options['title'] ?? '';
    $styles = $options['styles'] ?? [];
    $scripts = $options['scripts'] ?? [];
    $breadcrumbs = $options['breadcrumbs'] ?? [];
    $breadcrumbActions = $options['breadcrumb_actions'] ?? [];

    if ($layout === 'auth') {
        $title = $options['title'] ?? APP_NAME;
        include $viewPath;
        return;
    }

    global $menu;

    require BASE_PATH . '/includes/partials/header.php';
    require BASE_PATH . '/includes/partials/sidebar.php';

    echo '<div id="content-wrapper" class="d-flex flex-column">';
    echo '<div id="content">';

    require BASE_PATH . '/includes/partials/topbar.php';

    if (!empty($breadcrumbs)) {
        $labels = [];
        foreach ($breadcrumbs as $label => $url) {
            $labels[] = is_int($label) ? $url : $label;
        }
        $breadcrumbTitle = end($labels) ?: '';
        $breadcrumbActions = $breadcrumbActions;
        include BASE_PATH . '/pages/partials/breadcrumbs.php';
    }

    include $viewPath;

    echo '</div>'; // end content

    require BASE_PATH . '/includes/partials/footer.php';
    echo '</div>'; // end content-wrapper
    echo '</div>'; // end wrapper

    require BASE_PATH . '/includes/partials/scripts.php';

    foreach ($scripts as $script) {
        printf('<script src="%s"></script>', asset($script));
    }

    echo '</body></html>';
}
