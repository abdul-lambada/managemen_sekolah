<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Jadwal Pelajaran Kelas <?= sanitize($profil['nama_kelas']) ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tablePortalJadwal" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru</th>
                            <th>Ruang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jadwal as $item): ?>
                            <tr>
                                <td><?= sanitize($item['hari']) ?></td>
                                <td><?= sanitize(substr($item['jam_mulai'], 0, 5) . ' - ' . substr($item['jam_selesai'], 0, 5)) ?></td>
                                <td><?= sanitize($item['nama_mapel']) ?></td>
                                <td><?= sanitize($item['nama_guru']) ?></td>
                                <td><?= sanitize($item['ruang'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($jadwal)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Jadwal belum tersedia.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        $('#tablePortalJadwal').DataTable({
            order: [[0, 'asc'], [1, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
