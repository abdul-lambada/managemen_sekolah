<div class="container-fluid text-center py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-left-danger shadow mb-4">
                <div class="card-body">
                    <?php
                    $code = $code ?? 'Error';
                    $title = $title ?? 'Terjadi Kesalahan';
                    $message = $message ?? 'Maaf, permintaan Anda tidak dapat diproses saat ini.';
                    ?>
                    <h1 class="display-4 font-weight-bold text-danger mb-3"><?= sanitize($code) ?></h1>
                    <h5 class="font-weight-bold mb-3"><?= sanitize($title) ?></h5>
                    <p class="text-muted mb-4"><?= sanitize($message) ?></p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="<?= route('dashboard') ?>" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Kembali ke Dashboard
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="history.back();">
                            <i class="fas fa-arrow-left"></i> Halaman Sebelumnya
                        </button>
                    </div>
                </div>
            </div>
            <p class="small text-muted">Jika masalah berlanjut, silakan hubungi administrator.</p>
        </div>
    </div>
</div>
