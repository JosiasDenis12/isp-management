<?php include 'views/layouts/header.php'; ?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-database text-primary me-2"></i>Base de datos y respaldos</h1>
        <p class="text-muted mb-0">Consulta y restaura copias de seguridad de SkyNetwork.</p>
    </div>
</div>

<div id="backupUnsupported" class="alert alert-warning d-none" role="alert">
    <i class="fas fa-triangle-exclamation me-2"></i>
    La restauración de respaldos está disponible únicamente desde la aplicación de escritorio SkyNetwork.
</div>

<div class="alert alert-danger">
    <div class="fw-semibold mb-1"><i class="fas fa-shield-halved me-2"></i>Restaurar reemplaza los datos actuales</div>
    Antes de reemplazar la base persistente, SkyNetwork crea un respaldo de seguridad automático y valida la integridad SQLite. La aplicación se reiniciará al finalizar.
</div>

<div class="card">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
        <div>
            <h2 class="h5 mb-0">Respaldos disponibles</h2>
            <small class="text-muted">Solo se restauran copias guardadas por SkyNetwork.</small>
        </div>
        <button id="refreshBackups" type="button" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-rotate me-1"></i>Actualizar
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">Backup</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Tamaño</th>
                    <th scope="col">Motivo</th>
                    <th scope="col" class="text-end">Acción</th>
                </tr>
            </thead>
            <tbody id="backupRows">
                <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Cargando respaldos...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="backupMessage" class="mt-3" aria-live="polite"></div>

<script>
(function () {
    const api = window.skyNetworkBackups;
    const rows = document.getElementById('backupRows');
    const refresh = document.getElementById('refreshBackups');
    const message = document.getElementById('backupMessage');

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = String(value || '');
        return element.innerHTML;
    }

    function formatSize(bytes) {
        if (!Number.isFinite(bytes) || bytes < 1) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        return (bytes / Math.pow(1024, index)).toFixed(index ? 1 : 0) + ' ' + units[index];
    }

    function formatDate(value) {
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? 'Fecha desconocida' : date.toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' });
    }

    function showMessage(type, text) {
        message.innerHTML = '<div class="alert alert-' + type + '" role="alert">' + escapeHtml(text) + '</div>';
    }

    async function loadBackups() {
        if (!api) return;
        refresh.disabled = true;
        const result = await api.list();
        refresh.disabled = false;

        if (!result.success) {
            rows.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">No fue posible cargar los respaldos.</td></tr>';
            showMessage('danger', result.error || 'No fue posible cargar los respaldos.');
            return;
        }

        if (!result.backups.length) {
            rows.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Aún no hay respaldos disponibles.</td></tr>';
            return;
        }

        rows.innerHTML = result.backups.map(function (backup) {
            return '<tr>'
                + '<td><code>' + escapeHtml(backup.file) + '</code></td>'
                + '<td>' + escapeHtml(formatDate(backup.createdAt)) + '</td>'
                + '<td>' + escapeHtml(formatSize(backup.size)) + '</td>'
                + '<td>' + escapeHtml(backup.reason) + '</td>'
                + '<td class="text-end"><button type="button" class="btn btn-outline-danger btn-sm restore-backup" data-backup="' + escapeHtml(backup.file) + '"><i class="fas fa-clock-rotate-left me-1"></i>Restaurar</button></td>'
                + '</tr>';
        }).join('');
    }

    document.addEventListener('click', async function (event) {
        const button = event.target.closest('.restore-backup');
        if (!button || !api) return;

        const backupFile = button.getAttribute('data-backup');
        if (!window.confirm('¿Restaurar el respaldo seleccionado? Se creará una copia de seguridad de la base actual y SkyNetwork se reiniciará.')) return;

        document.querySelectorAll('.restore-backup, #refreshBackups').forEach(function (control) { control.disabled = true; });
        showMessage('warning', 'Validando el respaldo y preparando la restauración...');
        const result = await api.restore(backupFile);

        if (result.success) {
            showMessage('success', 'Base restaurada correctamente. SkyNetwork se reiniciará ahora.');
        } else {
            if (!result.cancelled) {
                showMessage('danger', result.error || 'No fue posible restaurar el respaldo.');
            }
            await loadBackups();
        }
    });

    refresh.addEventListener('click', loadBackups);

    if (!api) {
        document.getElementById('backupUnsupported').classList.remove('d-none');
        rows.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Abre esta sección desde SkyNetwork para ver los respaldos.</td></tr>';
        refresh.disabled = true;
        return;
    }

    loadBackups();
}());
</script>

<?php include 'views/layouts/footer.php'; ?>
