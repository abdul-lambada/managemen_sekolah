<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= sanitize($breadcrumbTitle ?? 'Halaman') ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <?php foreach ($breadcrumbs as $label => $url): ?>
                        <?php if (is_int($label)) {
                            $label = $url;
                            $url = null;
                        } ?>
                        <li class="breadcrumb-item<?= $url ? '' : ' active' ?>">
                            <?php if ($url): ?>
                                <a href="<?= $url ?>"><?= sanitize($label) ?></a>
                            <?php else: ?>
                                <?= sanitize($label) ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </div>
        <?php if (!empty($breadcrumbActions)): ?>
            <div>
                <?php foreach ($breadcrumbActions as $action): ?>
                    <a href="<?= $action['href'] ?>" class="btn btn-sm btn-<?= $action['variant'] ?? 'primary' ?> shadow-sm">
                        <?php if (!empty($action['icon'])): ?>
                            <i class="<?= $action['icon'] ?> fa-sm text-white-50"></i>
                        <?php endif; ?>
                        <?= sanitize($action['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
