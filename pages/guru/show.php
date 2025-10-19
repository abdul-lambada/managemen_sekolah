<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <!-- Profile Header Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-circle"></i> Detail Guru
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="<?= route('guru', ['action' => 'edit', 'id' => $guru['id_guru']]) ?>"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?= route('guru') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Profile Picture Section -->
                        <div class="col-md-4 text-center mb-4">
                            <div class="profile-avatar mx-auto mb-3">
                                <div class="bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold;">
                                    <?= strtoupper(substr($guru['nama_guru'], 0, 1)) ?>
                                </div>
                            </div>
                            <h4 class="font-weight-bold text-primary mb-1">
                                <?= sanitize($guru['nama_guru']) ?>
                            </h4>
                            <p class="text-muted mb-2">
                                <i class="fas fa-id-card"></i> NIP: <?= sanitize($guru['nip']) ?>
                            </p>
                            <div class="mb-3">
                                <?php if ($guru['jenis_kelamin'] === 'Laki-laki'): ?>
                                    <span class="badge badge-info px-3 py-2">
                                        <i class="fas fa-mars"></i> Laki-laki
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-pink px-3 py-2">
                                        <i class="fas fa-venus"></i> Perempuan
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($guru['user_id'])): ?>
                                <div class="alert alert-success py-2">
                                    <small><i class="fas fa-user-check"></i> Terhubung dengan akun pengguna</small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Information Section -->
                        <div class="col-md-8">
                            <div class="row">
                                <!-- Personal Information -->
                                <div class="col-md-6">
                                    <h6 class="section-title mb-3">
                                        <i class="fas fa-user text-primary"></i> Informasi Pribadi
                                    </h6>
                                    <dl class="info-list">
                                        <dt><i class="fas fa-birthday-cake text-muted"></i> Tanggal Lahir</dt>
                                        <dd>
                                            <?= $guru['tanggal_lahir'] ? indo_date($guru['tanggal_lahir']) : '<span class="text-muted">Tidak diisi</span>' ?>
                                        </dd>

                                        <dt><i class="fas fa-phone text-muted"></i> Nomor Telepon</dt>
                                        <dd>
                                            <?= !empty($guru['phone']) ? '<span class="badge badge-light">' . sanitize($guru['phone']) . '</span>' : '<span class="text-muted">Tidak diisi</span>' ?>
                                        </dd>

                                        <dt><i class="fas fa-user-circle text-muted"></i> Akun Pengguna</dt>
                                        <dd>
                                            <?= !empty($guru['user_name']) ? '<span class="badge badge-success">' . sanitize($guru['user_name']) . '</span>' : '<span class="text-muted">Tidak terhubung</span>' ?>
                                        </dd>
                                    </dl>
                                </div>

                                <!-- Address Information -->
                                <div class="col-md-6">
                                    <h6 class="section-title mb-3">
                                        <i class="fas fa-map-marker-alt text-primary"></i> Informasi Kontak
                                    </h6>
                                    <dl class="info-list">
                                        <dt><i class="fas fa-home text-muted"></i> Alamat Lengkap</dt>
                                        <dd class="address-text">
                                            <?= nl2br(sanitize($guru['alamat'])) ?: '<span class="text-muted">Tidak diisi</span>' ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <!-- Timestamps -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="section-title mb-3">
                                        <i class="fas fa-clock text-primary"></i> Informasi Sistem
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <dl class="info-list">
                                                <dt><i class="fas fa-calendar-plus text-muted"></i> Dibuat</dt>
                                                <dd>
                                                    <?= !empty($guru['created_at']) ? indo_datetime($guru['created_at']) : '<span class="text-muted">Tidak diketahui</span>' ?>
                                                </dd>
                                            </dl>
                                        </div>
                                        <div class="col-md-6">
                                            <dl class="info-list">
                                                <dt><i class="fas fa-calendar-check text-muted"></i> Diperbarui</dt>
                                                <dd>
                                                    <?= !empty($guru['updated_at']) ? indo_datetime($guru['updated_at']) : '<span class="text-muted">Tidak diketahui</span>' ?>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-left-info shadow mb-4">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Aksi Cepat</div>
                                    <div class="row">
                                        <div class="col-6">
                                            <a href="<?= route('guru', ['action' => 'edit', 'id' => $guru['id_guru']]) ?>"
                                               class="btn btn-info btn-sm btn-block">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn btn-danger btn-sm btn-block"
                                                    data-toggle="modal" data-target="#deleteModal"
                                                    data-guru-id="<?= $guru['id_guru'] ?>"
                                                    data-guru-name="<?= sanitize($guru['nama_guru']) ?>">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-cogs fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-left-success shadow mb-4">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Status Koneksi</div>
                                    <div class="mb-0">
                                        <?php if (!empty($guru['user_id'])): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle"></i> Terhubung
                                            </span>
                                            <small class="text-muted d-block">Guru dapat login ke sistem</small>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Tidak Terhubung
                                            </span>
                                            <small class="text-muted d-block">Guru belum memiliki akses login</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-<?= !empty($guru['user_id']) ? 'check' : 'times' ?> fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Apakah Anda yakin ingin menghapus data guru:</p>
                <p class="font-weight-bold text-danger mb-3" id="deleteGuruName"></p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    <strong>Peringatan:</strong> Data yang dihapus tidak dapat dikembalikan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden delete form -->
<form id="deleteForm" method="POST" action="<?= route('guru', ['action' => 'delete']) ?>" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token'] ?? '') ?>">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
// Handle delete button click in show view
document.addEventListener('DOMContentLoaded', function() {
    $('[data-toggle="modal"]').on('click', function() {
        const guruName = $(this).data('guru-name');
        const guruId = $(this).data('guru-id');

        $('#deleteGuruName').text(guruName);
        $('#confirmDeleteBtn').data('guru-id', guruId);
    });

    // Handle confirm delete button
    $('#confirmDeleteBtn').on('click', function() {
        const guruId = $(this).data('guru-id');
        if (guruId) {
            // Set the form action and submit
            $('#deleteForm').attr('action', '<?= route('guru', ['action' => 'delete']) ?>');
            $('#deleteId').val(guruId);
            $('#deleteForm').submit();
        }
    });
});
</script>

<style>
.profile-avatar {
    position: relative;
}

.section-title {
    border-bottom: 2px solid #e3e6f0;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

.info-list dt {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.info-list dd {
    margin-bottom: 1rem;
    color: #6c757d;
}

.address-text {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border-left: 4px solid #4f46e5;
    min-height: 80px;
}

.badge-pink {
    background-color: #e91e63;
    color: white;
}

.btn-block {
    width: 100%;
}

.text-muted {
    color: #6c757d !important;
}

.alert-success {
    background-color: rgba(25, 135, 84, 0.1);
    border: 1px solid rgba(25, 135, 84, 0.2);
    color: #198754;
}

.alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.2);
    color: #ffc107;
}
</style>
