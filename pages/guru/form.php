<div class="container-fluid">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Ada kesalahan dalam pengisian form:</h6>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= sanitize($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?>"></i>
                        <?= $isEdit ? 'Edit Data Guru' : 'Tambah Guru Baru' ?>
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= route('guru', ['action' => $isEdit ? 'update' : 'store']) ?>" id="guruForm">
                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= (int) ($guru['id_guru'] ?? 0) ?>">
                        <?php endif; ?>

                        <!-- Personal Information Section -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="section-title mb-3">
                                    <i class="fas fa-user-circle text-primary"></i>
                                    Informasi Pribadi
                                </h6>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_guru" class="form-label">
                                        <i class="fas fa-user"></i> Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control <?= isset($errors) && array_filter($errors, fn($e) => strpos($e, 'Nama guru') !== false) ? 'is-invalid' : '' ?>"
                                           id="nama_guru"
                                           name="nama_guru"
                                           value="<?= sanitize($guru['nama_guru'] ?? '') ?>"
                                           required
                                           maxlength="100"
                                           placeholder="Masukkan nama lengkap guru">
                                    <div class="invalid-feedback">
                                        <?= implode('', array_filter($errors, fn($e) => strpos($e, 'Nama guru') !== false)) ?>
                                    </div>
                                    <small class="form-text text-muted">Nama guru yang akan ditampilkan dalam sistem</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nip" class="form-label">
                                        <i class="fas fa-id-card"></i> NIP <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control <?= isset($errors) && array_filter($errors, fn($e) => strpos($e, 'NIP') !== false) ? 'is-invalid' : '' ?>"
                                           id="nip"
                                           name="nip"
                                           value="<?= sanitize($guru['nip'] ?? '') ?>"
                                           required
                                           maxlength="20"
                                           pattern="[0-9]+"
                                           placeholder="Masukkan NIP guru">
                                    <div class="invalid-feedback">
                                        <?= implode('', array_filter($errors, fn($e) => strpos($e, 'NIP') !== false)) ?>
                                    </div>
                                    <small class="form-text text-muted">Nomor Induk Pegawai (hanya angka)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jenis_kelamin" class="form-label">
                                        <i class="fas fa-venus-mars"></i> Jenis Kelamin <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control <?= isset($errors) && array_filter($errors, fn($e) => strpos($e, 'Jenis kelamin') !== false) ? 'is-invalid' : '' ?>"
                                            id="jenis_kelamin"
                                            name="jenis_kelamin"
                                            required>
                                        <option value="">-- Pilih Jenis Kelamin --</option>
                                        <option value="Laki-laki" <?= ($guru['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>
                                            <i class="fas fa-mars"></i> Laki-laki
                                        </option>
                                        <option value="Perempuan" <?= ($guru['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>
                                            <i class="fas fa-venus"></i> Perempuan
                                        </option>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?= implode('', array_filter($errors, fn($e) => strpos($e, 'Jenis kelamin') !== false)) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_lahir" class="form-label">
                                        <i class="fas fa-birthday-cake"></i> Tanggal Lahir
                                    </label>
                                    <input type="date"
                                           class="form-control <?= isset($errors) && array_filter($errors, fn($e) => strpos($e, 'Tanggal lahir') !== false) ? 'is-invalid' : '' ?>"
                                           id="tanggal_lahir"
                                           name="tanggal_lahir"
                                           value="<?= sanitize($guru['tanggal_lahir'] ?? '') ?>">
                                    <div class="invalid-feedback">
                                        <?= implode('', array_filter($errors, fn($e) => strpos($e, 'Tanggal lahir') !== false)) ?>
                                    </div>
                                    <small class="form-text text-muted">Tanggal lahir guru (opsional)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="section-title mb-3">
                                    <i class="fas fa-address-book text-primary"></i>
                                    Informasi Kontak
                                </h6>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone"></i> Nomor Telepon
                                    </label>
                                    <input type="text"
                                           class="form-control <?= isset($errors) && array_filter($errors, fn($e) => strpos($e, 'Nomor telepon') !== false) ? 'is-invalid' : '' ?>"
                                           id="phone"
                                           name="phone"
                                           value="<?= sanitize($guru['phone'] ?? '') ?>"
                                           pattern="^[0-9+\- ]+$"
                                           placeholder="Contoh: 08123456789">
                                    <div class="invalid-feedback">
                                        <?= implode('', array_filter($errors, fn($e) => strpos($e, 'Nomor telepon') !== false)) ?>
                                    </div>
                                    <small class="form-text text-muted">Nomor telepon untuk kontak (opsional)</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_id" class="form-label">
                                        <i class="fas fa-user-circle"></i> Akun Pengguna
                                    </label>
                                    <select class="form-control" id="user_id" name="user_id">
                                        <option value="">-- Tidak terhubung ke akun pengguna --</option>
                                        <?php foreach ($userOptions as $user): ?>
                                            <option value="<?= (int) $user['id'] ?>"
                                                    <?= ((int) ($guru['user_id'] ?? 0)) === (int) $user['id'] ? 'selected' : '' ?>>
                                                <?= sanitize($user['name']) ?> (<?= sanitize($user['role']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">
                                        Hubungkan dengan akun pengguna yang sudah ada (opsional)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="alamat" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Alamat Lengkap <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control <?= isset($errors) && array_filter($errors, fn($e) => strpos($e, 'Alamat') !== false) ? 'is-invalid' : '' ?>"
                                      id="alamat"
                                      name="alamat"
                                      rows="4"
                                      required
                                      maxlength="500"
                                      placeholder="Masukkan alamat lengkap guru..."><?= sanitize($guru['alamat'] ?? '') ?></textarea>
                            <div class="invalid-feedback">
                                <?= implode('', array_filter($errors, fn($e) => strpos($e, 'Alamat') !== false)) ?>
                            </div>
                            <small class="form-text text-muted">Alamat lengkap tempat tinggal guru</small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <a href="<?= route('guru') ?>" class="btn btn-light btn-lg">
                                    <i class="fas fa-arrow-left"></i> Batal & Kembali
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-<?= $isEdit ? 'save' : 'plus-circle' ?>"></i>
                                    <?= $isEdit ? 'Perbarui Data' : 'Simpan Guru' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('guruForm');

    // Real-time validation
    const namaInput = document.getElementById('nama_guru');
    const nipInput = document.getElementById('nip');
    const phoneInput = document.getElementById('phone');
    const alamatInput = document.getElementById('alamat');

    // Add input event listeners for real-time validation feedback
    [namaInput, nipInput, phoneInput, alamatInput].forEach(input => {
        if (input) {
            input.addEventListener('input', function() {
                // Remove invalid class when user starts typing
                this.classList.remove('is-invalid');
            });
        }
    });

    // NIP format validation
    nipInput.addEventListener('input', function() {
        // Remove non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Phone format validation
    phoneInput.addEventListener('input', function() {
        // Remove non-numeric characters except + and -
        this.value = this.value.replace(/[^0-9+\-\s]/g, '');
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        let firstInvalidField = null;

        // Validate required fields
        if (!namaInput.value.trim()) {
            namaInput.classList.add('is-invalid');
            if (!firstInvalidField) firstInvalidField = namaInput;
            isValid = false;
        }

        if (!nipInput.value.trim()) {
            nipInput.classList.add('is-invalid');
            if (!firstInvalidField) firstInvalidField = nipInput;
            isValid = false;
        }

        if (!alamatInput.value.trim()) {
            alamatInput.classList.add('is-invalid');
            if (!firstInvalidField) firstInvalidField = alamatInput;
            isValid = false;
        }

        if (jenisKelamin.value === '') {
            jenisKelamin.classList.add('is-invalid');
            if (!firstInvalidField) firstInvalidField = jenisKelamin;
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Character counter for alamat
    const maxLength = 500;
    const charCounter = document.createElement('small');
    charCounter.className = 'form-text text-muted';
    charCounter.innerHTML = `Karakter tersisa: <span id="charCount">${maxLength}</span>`;
    alamatInput.parentNode.appendChild(charCounter);

    alamatInput.addEventListener('input', function() {
        const remaining = maxLength - this.value.length;
        document.getElementById('charCount').textContent = remaining;

        if (remaining < 50) {
            charCounter.classList.add('text-warning');
        } else {
            charCounter.classList.remove('text-warning');
        }

        if (remaining < 0) {
            charCounter.classList.add('text-danger');
        } else {
            charCounter.classList.remove('text-danger');
        }
    });

    // Trigger initial character count
    alamatInput.dispatchEvent(new Event('input'));
});
</script>

<style>
.section-title {
    border-bottom: 2px solid #e3e6f0;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
}

.btn-lg {
    padding: 0.75rem 2rem;
    font-size: 1.1rem;
}

.alert ul {
    margin-bottom: 0;
}

#charCount {
    font-weight: bold;
}

.text-warning {
    color: #ffc107 !important;
}

.text-danger {
    color: #dc3545 !important;
}

/* Custom select styling */
select.form-control {
    height: calc(2.25rem + 2px);
}

select.form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
}

/* Badge styling for empty states */
.badge:empty::after {
    content: "-";
    opacity: 0.5;
}
</style>
