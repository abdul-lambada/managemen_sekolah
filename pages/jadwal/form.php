<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= sanitize($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?= $isEdit ? 'Edit Jadwal' : 'Tambah Jadwal' ?>
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= route('jadwal', ['action' => $isEdit ? 'update' : 'store']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= (int) ($jadwal['id_jadwal'] ?? 0) ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="id_kelas">Kelas</label>
                            <select class="form-control" id="id_kelas" name="id_kelas" required>
                                <option value="">Pilih kelas</option>
                                <?php foreach ($kelasOptions as $kelas): ?>
                                    <?php $kelasId = (int) $kelas['id_kelas']; ?>
                                    <option value="<?= $kelasId ?>" <?= (($jadwal['id_kelas'] ?? null) == $kelasId) ? 'selected' : '' ?>>
                                        <?= sanitize(($kelas['nama_jurusan'] ?? '') . ' - ' . $kelas['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="id_mata_pelajaran">Mata Pelajaran</label>
                            <select class="form-control" id="id_mata_pelajaran" name="id_mata_pelajaran" required>
                                <option value="">Pilih mata pelajaran</option>
                                <?php foreach ($mapelOptions as $mapel): ?>
                                    <?php $mapelId = (int) $mapel['id_mata_pelajaran']; ?>
                                    <option value="<?= $mapelId ?>" <?= (($jadwal['id_mata_pelajaran'] ?? null) == $mapelId) ? 'selected' : '' ?>>
                                        <?= sanitize(($mapel['kode_mapel'] ?? '') . ' - ' . $mapel['nama_mapel']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="id_guru">Guru Pengampu</label>
                            <select class="form-control" id="id_guru" name="id_guru" required>
                                <option value="">Pilih guru</option>
                                <?php foreach ($guruOptions as $guru): ?>
                                    <?php $guruId = (int) $guru['id_guru']; ?>
                                    <option value="<?= $guruId ?>" <?= (($jadwal['id_guru'] ?? null) == $guruId) ? 'selected' : '' ?>>
                                        <?= sanitize($guru['nama_guru']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="hari">Hari</label>
                            <select class="form-control" id="hari" name="hari" required>
                                <option value="">Pilih hari</option>
                                <?php foreach ($hariOptions as $hari): ?>
                                    <option value="<?= sanitize($hari) ?>" <?= (($jadwal['hari'] ?? '') === $hari) ? 'selected' : '' ?>>
                                        <?= sanitize($hari) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="jam_mulai">Jam Mulai</label>
                                <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" value="<?= sanitize($jadwal['jam_mulai'] ?? '') ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="jam_selesai">Jam Selesai</label>
                                <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" value="<?= sanitize($jadwal['jam_selesai'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ruang">Ruang</label>
                            <input type="text" class="form-control" id="ruang" name="ruang" value="<?= sanitize($jadwal['ruang'] ?? '') ?>" placeholder="Misal: Lab Komputer">
                        </div>

                        <div class="form-group">
                            <label for="catatan">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Opsional"><?= sanitize($jadwal['catatan'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= route('jadwal') ?>" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
