<?php
$menu = $menu ?? [];
$currentPage = $_GET['page'] ?? 'dashboard';
$appSettings = app_settings();
$appName = $appSettings['app_name'] ?: APP_NAME;
?>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= route('dashboard') ?>">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-school"></i>
        </div>
        <div class="sidebar-brand-text mx-3"><?= sanitize($appName) ?></div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= route('dashboard') ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <?php foreach ($menu as $section): ?>
        <?php if (!empty($section['heading'])): ?>
            <div class="sidebar-heading">
                <?= sanitize($section['heading']) ?>
            </div>
        <?php endif; ?>

        <?php foreach ($section['items'] as $item): ?>
            <?php
            $isActive = $currentPage === ($item['page'] ?? '') || in_array($currentPage, $item['active'] ?? [], true);
            $classes = ['nav-item'];
            if (!empty($item['children'])) {
                $classes[] = 'nav-item';
            }
            if ($isActive) {
                $classes[] = 'active';
            }
            ?>
            <li class="<?= implode(' ', $classes) ?>">
                <?php if (empty($item['children'])): ?>
                    <a class="nav-link" href="<?= route($item['page']) ?>">
                        <i class="<?= sanitize($item['icon'] ?? 'fas fa-circle') ?>"></i>
                        <span><?= sanitize($item['label']) ?></span>
                    </a>
                <?php else: ?>
                    <?php $collapseId = 'collapse-' . preg_replace('/[^a-z0-9]/i', '', $item['page']); ?>
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#<?= $collapseId ?>"
                       aria-expanded="<?= $isActive ? 'true' : 'false' ?>" aria-controls="<?= $collapseId ?>">
                        <i class="<?= sanitize($item['icon'] ?? 'fas fa-folder') ?>"></i>
                        <span><?= sanitize($item['label']) ?></span>
                    </a>
                    <div id="<?= $collapseId ?>" class="collapse <?= $isActive ? 'show' : '' ?>" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php foreach ($item['children'] as $child): ?>
                                <a class="collapse-item <?= $currentPage === $child['page'] ? 'active' : '' ?>"
                                   href="<?= route($child['page']) ?>">
                                    <?= sanitize($child['label']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>

        <hr class="sidebar-divider">
    <?php endforeach; ?>

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
