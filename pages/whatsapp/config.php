<div class="container-fluid">
    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?= sanitize($alert['type']) ?> alert-dismissible fade show" role="alert">
            <?= sanitize($alert['message']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

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
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Konfigurasi WhatsApp</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= route('whatsapp_config', ['action' => 'save_config']) ?>">
                <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="api_key">API Key</label>
                        <input type="text" class="form-control" id="api_key" name="api_key" value="<?= sanitize($config['api_key'] ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="api_url">API URL</label>
                        <input type="url" class="form-control" id="api_url" name="api_url" value="<?= sanitize($config['api_url'] ?? 'https://api.fonnte.com') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="country_code">Kode Negara</label>
                        <input type="text" class="form-control" id="country_code" name="country_code" value="<?= sanitize($config['country_code'] ?? '62') ?>" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="device_id">Device ID</label>
                        <input type="text" class="form-control" id="device_id" name="device_id" value="<?= sanitize($config['device_id'] ?? '') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="delay">Delay (detik)</label>
                        <input type="number" class="form-control" id="delay" name="delay" value="<?= sanitize($config['delay'] ?? 2) ?>" min="0">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="retry">Retry</label>
                        <input type="number" class="form-control" id="retry" name="retry" value="<?= sanitize($config['retry'] ?? 0) ?>" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="callback_url">Callback URL</label>
                        <input type="url" class="form-control" id="callback_url" name="callback_url" value="<?= sanitize($config['callback_url'] ?? '') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="template_language">Bahasa Template</label>
                        <input type="text" class="form-control" id="template_language" name="template_language" value="<?= sanitize($config['template_language'] ?? 'id') ?>" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="webhook_secret">Webhook Secret</label>
                        <input type="text" class="form-control" id="webhook_secret" name="webhook_secret" value="<?= sanitize($config['webhook_secret'] ?? '') ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <div>
                        <a href="<?= route('whatsapp_logs') ?>" class="btn btn-info btn-sm"><i class="fas fa-history"></i> Lihat Log</a>
                        <a href="<?= route('whatsapp_config', ['action' => 'templates']) ?>" class="btn btn-secondary btn-sm"><i class="fas fa-list"></i> Kelola Template</a>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
