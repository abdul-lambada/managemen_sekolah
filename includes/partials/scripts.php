<script src="<?= asset('vendor/jquery/jquery.min.js') ?>"></script>
<script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('vendor/jquery-easing/jquery.easing.min.js') ?>"></script>
<script src="<?= asset('js/sb-admin-2.min.js') ?>"></script>
<script src="<?= asset('vendor/chart.js/Chart.min.js') ?>"></script>
<script src="<?= asset('vendor/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= asset('vendor/datatables/dataTables.bootstrap4.min.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const deleteModalElement = document.getElementById('deleteConfirmModal');
        const confirmButton = document.getElementById('confirmDeleteButton');
        if (!deleteModalElement || !confirmButton) {
            return;
        }

        const deleteMessageElement = deleteModalElement.querySelector('.delete-message');
        let formPendingSubmit = null;

        document.querySelectorAll('form[data-confirm="delete"]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();

                formPendingSubmit = form;
                const message = form.getAttribute('data-confirm-message')
                    || 'Apakah Anda yakin ingin menghapus data ini?';

                if (deleteMessageElement) {
                    deleteMessageElement.textContent = message;
                }

                $('#deleteConfirmModal').modal('show');
            });
        });

        confirmButton.addEventListener('click', () => {
            if (formPendingSubmit) {
                formPendingSubmit.submit();
                formPendingSubmit = null;
            }
        });

        $('#deleteConfirmModal').on('hidden.bs.modal', () => {
            formPendingSubmit = null;
        });
    });
</script>
