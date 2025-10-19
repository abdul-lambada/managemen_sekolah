<div class="container-fluid">
    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?= sanitize($alert['type']) ?> alert-dismissible fade show" role="alert">
            <?= $alert['message'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Guru</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($guruList) ? count($guruList) : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Dengan Akun</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($guruList) ? count(array_filter($guruList, fn($guru) => !empty($guru['user_id']))) : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Laki-laki</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        <?= isset($guruList) ? count(array_filter($guruList, fn($guru) => $guru['jenis_kelamin'] === 'Laki-laki')) : 0 ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-mars fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Perempuan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($guruList) ? count(array_filter($guruList, fn($guru) => $guru['jenis_kelamin'] === 'Perempuan')) : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-venus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 col-lg-8 mb-3 mb-md-0">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users"></i> Daftar Guru
                    </h6>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <!-- Search Input - Hidden on mobile, shown on larger screens -->
                        <div class="input-group d-none d-sm-flex" style="width: 100%; max-width: 250px;">
                            <input type="text" class="form-control" id="searchInput" placeholder="Cari guru...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>

                        <!-- Mobile Search Button -->
                        <button class="btn btn-outline-secondary d-sm-none" type="button" data-toggle="collapse" data-target="#mobileSearch">
                            <i class="fas fa-search"></i> Cari
                        </button>

                        <!-- Add Button -->
                        <a href="<?= route('guru', ['action' => 'create']) ?>" class="btn btn-primary">
                            <i class="fas fa-plus d-sm-none"></i>
                            <span class="d-none d-sm-inline">Tambah Guru</span>
                        </a>
                    </div>

                    <!-- Mobile Search Collapse -->
                    <div class="collapse mt-2" id="mobileSearch">
                        <div class="input-group">
                            <input type="text" class="form-control" id="mobileSearchInput" placeholder="Cari guru...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="mobileSearch()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="dataGuru" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>
                                <i class="fas fa-user"></i> Nama
                            </th>
                            <th>
                                <i class="fas fa-id-card"></i> NIP
                            </th>
                            <th>
                                <i class="fas fa-venus-mars"></i> Gender
                            </th>
                            <th>
                                <i class="fas fa-birthday-cake"></i> Tanggal Lahir
                            </th>
                            <th>
                                <i class="fas fa-phone"></i> Telepon
                            </th>
                            <th>
                                <i class="fas fa-user-circle"></i> Akun
                            </th>
                            <th>
                                <i class="fas fa-cogs"></i> Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($guruList) || !isset($guruList)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-users fa-4x text-muted mb-4"></i>
                                        <h5 class="text-muted">Belum ada data guru</h5>
                                        <p class="text-muted mb-4">Sistem belum memiliki data guru yang tersimpan</p>
                                        <a href="<?= route('guru', ['action' => 'create']) ?>" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Tambah Guru Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($guruList as $index => $guru): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm mr-3">
                                                <div class="bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <?= strtoupper(substr($guru['nama_guru'] ?? 'N', 0, 1)) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <strong><?= sanitize($guru['nama_guru'] ?? 'Unknown') ?></strong>
                                                <?php if (!empty($guru['user_id'])): ?>
                                                    <br><small class="text-success"><i class="fas fa-user-check"></i> Terhubung</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= sanitize($guru['nip'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($guru['jenis_kelamin'] ?? '') === 'Laki-laki'): ?>
                                            <span class="badge badge-info">
                                                <i class="fas fa-mars"></i> Laki-laki
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-pink">
                                                <i class="fas fa-venus"></i> Perempuan
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= !empty($guru['tanggal_lahir']) ? indo_date($guru['tanggal_lahir']) : '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td>
                                        <?= !empty($guru['phone']) ? '<span class="badge badge-light">' . sanitize($guru['phone']) . '</span>' : '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td>
                                        <?= !empty($guru['user_name']) ? '<span class="badge badge-success">' . sanitize($guru['user_name']) . '</span>' : '<span class="text-muted">Tidak terhubung</span>' ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="btn-group" role="group">
                                            <a href="<?= route('guru', ['action' => 'show', 'id' => $guru['id_guru'] ?? 0]) ?>"
                                               class="btn btn-sm btn-outline-secondary" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= route('guru', ['action' => 'edit', 'id' => $guru['id_guru'] ?? 0]) ?>"
                                               class="btn btn-sm btn-outline-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?= route('guru', ['action' => 'delete']) ?>" method="POST" class="d-inline delete-form"
                                                  data-guru-id="<?= (int) ($guru['id_guru'] ?? 0) ?>"
                                                  data-guru-name="<?= sanitize($guru['nama_guru'] ?? '') ?>">
                                                <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken ?? '') ?>">
                                                <input type="hidden" name="id" value="<?= (int) ($guru['id_guru'] ?? 0) ?>">
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" data-toggle="modal" data-target="#deleteModal">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = $('#dataGuru').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
        },
        responsive: true,
        order: [[1, 'asc']], // Sort by nama_guru ascending
        columnDefs: [
            { orderable: false, targets: [7] }, // Disable sorting on actions column
            { searchable: false, targets: [0, 7] } // Disable search on # and actions columns
        ],
        initComplete: function() {
            // Add search functionality
            const searchInput = $('#searchInput');
            searchInput.on('keyup', function() {
                table.search(this.value).draw();
            });

            // Custom search placeholder
            $('.dataTables_filter').hide();
        }
    });

    // Handle delete button click
    $('[data-toggle="modal"]').on('click', function() {
        const form = $(this).closest('.delete-form');
        const guruName = form.data('guru-name');
        const guruId = form.data('guru-id');

        $('#deleteGuruName').text(guruName);
        $('#confirmDeleteBtn').data('form', form);
    });

    // Handle confirm delete button
    $('#confirmDeleteBtn').on('click', function() {
        const form = $(this).data('form');
        if (form) {
            form.submit();
        }
    });

    // Mobile search functionality
    function mobileSearch() {
        const searchValue = $('#mobileSearchInput').val();
        $('#searchInput').val(searchValue);
        table.search(searchValue).draw();

        // Close mobile search collapse
        $('#mobileSearch').collapse('hide');
    }

    // Sync search inputs
    $('#searchInput, #mobileSearchInput').on('keyup', function() {
        const searchValue = $(this).val();
        $('#searchInput').val(searchValue);
        $('#mobileSearchInput').val(searchValue);
        table.search(searchValue).draw();
    });
});
</script>

<style>
.badge-pink {
    background-color: #e91e63;
    color: white;
}

.avatar-sm {
    width: 40px;
    height: 40px;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-info:hover {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Empty state styling */
.empty-state {
    padding: 3rem 1rem;
}

.empty-state i {
    opacity: 0.5;
}

.empty-state h5 {
    color: #6c757d;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #adb5bd;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}

.empty-state .btn {
    font-size: 0.9rem;
    padding: 0.5rem 1.5rem;
}

/* Enhanced table styling */
.table-hover tbody tr:hover {
    background-color: rgba(79, 70, 229, 0.05);
}

/* Statistics cards hover effects */
.card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-group .btn {
        margin-bottom: 2px;
        margin-right: 0;
    }

    .empty-state {
        padding: 2rem 1rem;
    }

    .empty-state i {
        font-size: 3rem;
    }

    /* Card header responsive */
    .card-header .row > div {
        margin-bottom: 1rem;
    }

    .card-header .row > div:last-child {
        margin-bottom: 0;
    }

    /* Mobile search styling */
    #mobileSearch {
        border-top: 1px solid #dee2e6;
        padding-top: 1rem;
        margin-top: 1rem;
    }

    #mobileSearch .input-group {
        max-width: none;
    }

    /* Button responsive text */
    .btn .d-sm-none {
        margin-right: 0.25rem;
    }

    /* Statistics cards mobile layout */
    .card-body .row.no-gutters {
        text-align: center;
    }

    .card-body .col-auto {
        margin-top: 1rem;
    }
}

@media (max-width: 576px) {
    /* Very small screens */
    .d-flex.gap-2 {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }

    .btn:last-child {
        margin-bottom: 0;
    }
}
</style>
