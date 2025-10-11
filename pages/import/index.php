<div class="container-fluid">
    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?= sanitize($alert['type']) ?>">
            <?= sanitize($alert['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (($mode ?? 'form') === 'form'): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Import Data</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Unggah file XLSX/CSV dengan format kolom sesuai panduan di bawah. Baris pertama dianggap header.</p>

                <form method="POST" action="<?= route('import', ['action' => 'upload']) ?>" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                    <div class="form-group">
                        <label for="type">Pilih Jenis Data</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($typeOptions as $key => $label): ?>
                                <option value="<?= sanitize($key) ?>" <?= ($key === ($_GET['type'] ?? '')) ? 'selected' : '' ?>>
                                    <?= sanitize($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Unduh Template</label>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Unduh template">
                            <?php foreach ($typeOptions as $key => $label): ?>
                                <a class="btn btn-outline-secondary" href="<?= route('import', ['action' => 'template', 'type' => $key]) ?>">
                                    <i class="fas fa-download"></i> <?= sanitize($label) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <small class="form-text text-muted">Template dalam format CSV dengan contoh baris pertama.</small>
                    </div>
                    <div class="form-group">
                        <label for="import_file">File XLSX/CSV</label>
                        <input type="file" class="form-control-file" id="import_file" name="import_file" accept=".xlsx,.xls,.csv" required>
                        <small class="form-text text-muted">Pastikan file bebas duplikat pada kolom kunci (misal NIP/NISN).</small>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Unggah</button>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-secondary">Panduan Kolom</h6>
            </div>
            <div class="card-body">
                <?php foreach ($columnInfo as $type => $columns): ?>
                    <h6 class="font-weight-bold"><?= sanitize($typeOptions[$type] ?? strtoupper($type)) ?></h6>
                    <ul>
                        <?php foreach ($columns as $column): ?>
                            <li><?= sanitize($column['label']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            </div>
        </div>
    <?php elseif (($mode ?? '') === 'preview'): ?>
        <?php $previewHeading = $typeLabel ?? ($type ? strtoupper((string) $type) : 'DATA'); ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div>
                    <h6 class="m-0 font-weight-bold text-primary">Pratinjau <?= sanitize($previewHeading) ?> (<?= (int) ($counts['valid'] ?? 0) ?> valid / <?= (int) ($counts['invalid'] ?? 0) ?> invalid)</h6>
                    <small class="text-muted">File: <?= sanitize($filename ?? '-') ?></small>
                </div>
                <div>
                    <a href="<?= route('import') ?>" class="btn btn-sm btn-light">Unggah Ulang</a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= route('import', ['action' => 'store']) ?>">
                    <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                    <input type="hidden" name="type" value="<?= sanitize($type) ?>">

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tableImportPreview" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <?php if (!empty($columnInfo[$type])): ?>
                                        <?php foreach ($columnInfo[$type] as $column): ?>
                                            <th><?= sanitize($column['label']) ?></th>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($previewRows as $row): ?>
                                    <tr class="<?= empty($row['errors']) ? 'table-success' : 'table-danger' ?>">
                                        <td><?= (int) $row['row_number'] ?></td>
                                        <?php if (!empty($columnInfo[$type])): ?>
                                            <?php foreach ($columnInfo[$type] as $column): ?>
                                                <td><?= sanitize($row['data'][$column['key']] ?? '') ?></td>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <td>
                                            <?php if (empty($row['errors'])): ?>
                                                <span class="badge badge-success">Valid</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><?= count($row['errors']) ?> error</span>
                                                <ul class="mb-0 small">
                                                    <?php foreach ($row['errors'] as $error): ?>
                                                        <li><?= sanitize($error) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex justify-content-between">
                        <a href="<?= route('import') ?>" class="btn btn-light"><i class="fas fa-arrow-left"></i> Kembali</a>
                        <button type="submit" class="btn btn-primary" <?= ($counts['valid'] ?? 0) === 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-save"></i> Simpan Data Valid
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (($mode ?? 'form') === 'preview'): ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        $('#tableImportPreview').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
<?php endif; ?>
