<div class="container-fluid">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= sanitize($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <?= $isEdit ? 'Edit Template WhatsApp' : 'Tambah Template WhatsApp' ?>
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= route('whatsapp_config', ['action' => $isEdit ? 'save_template' : 'save_template']) ?>">
                <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                <?php if ($isEdit && !empty($template['id'])): ?>
                    <input type="hidden" name="id" value="<?= (int) $template['id'] ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="name">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= sanitize($template['name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="display_name">Nama Tampilan</label>
                        <input type="text" class="form-control" id="display_name" name="display_name" value="<?= sanitize($template['display_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="category">Kategori</label>
                        <select class="form-control" id="category" name="category">
                            <?php foreach (['AUTHENTICATION', 'MARKETING', 'UTILITY'] as $option): ?>
                                <option value="<?= $option ?>" <?= ($template['category'] ?? 'UTILITY') === $option ? 'selected' : '' ?>>
                                    <?= $option ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="language">Bahasa</label>
                        <input type="text" class="form-control" id="language" name="language" value="<?= sanitize($template['language'] ?? 'id') ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <?php foreach (['PENDING', 'APPROVED', 'REJECTED'] as $status): ?>
                                <option value="<?= $status ?>" <?= ($template['status'] ?? 'PENDING') === $status ? 'selected' : '' ?>>
                                    <?= $status ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="body">Isi Pesan</label>
                    <textarea class="form-control" id="body" name="body" rows="5" required><?= sanitize($template['body'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="header">Header</label>
                        <input type="text" class="form-control" id="header" name="header" value="<?= sanitize($template['header'] ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="footer">Footer</label>
                        <input type="text" class="form-control" id="footer" name="footer" value="<?= sanitize($template['footer'] ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="variables">Variabel (json)</label>
                        <input type="text" class="form-control" id="variables" name="variables" value="<?= sanitize($template['variables'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="template_id">Template ID</label>
                        <input type="text" class="form-control" id="template_id" name="template_id" value="<?= sanitize($template['template_id'] ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="buttons">Buttons (json)</label>
                        <input type="text" class="form-control" id="buttons" name="buttons" value="<?= sanitize($template['buttons'] ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="components">Components (json)</label>
                        <input type="text" class="form-control" id="components" name="components" value="<?= sanitize($template['components'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="example">Example (json)</label>
                        <input type="text" class="form-control" id="example" name="example" value="<?= sanitize($template['example'] ?? '') ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="template_id">Aktif?</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?= ($template['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?= route('whatsapp_config', ['action' => 'templates']) ?>" class="btn btn-light"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
