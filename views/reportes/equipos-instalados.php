<?php include 'views/layouts/header.php'; ?>

<?php
$rows = $rows ?? [];
$stats = $stats ?? [];
$filters = $filters ?? [];

$tipoLabels = [
    'antena' => 'Antena',
    'modem' => 'Módem',
    'router' => 'Router',
    'switch' => 'Switch',
    'access_point' => 'Access Point',
    'otro' => 'Otro',
];

$estadoLabels = [
    'operativo' => 'Operativo',
    'necesita_revision' => 'Necesita revisión',
    'dañado' => 'Dañado',
    'fuera_de_servicio' => 'Fuera de servicio',
    'en_mantenimiento' => 'En mantenimiento',
];

function equipoReportDate($value) {
    if (empty($value)) return '-';
    $ts = strtotime($value);
    return $ts ? date('d/m/Y', $ts) : '-';
}

function equipoReportBadge($estado) {
    switch ($estado) {
        case 'operativo': return 'bg-success';
        case 'necesita_revision': return 'bg-warning text-dark';
        case 'dañado': return 'bg-danger';
        case 'fuera_de_servicio': return 'bg-dark';
        case 'en_mantenimiento': return 'bg-info';
        default: return 'bg-secondary';
    }
}
?>

<style>
    .rep-page-header {
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    .rep-page-header .rep-title {
        font-weight: 700;
        letter-spacing: -0.2px;
    }
    .rep-page-header .rep-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(135deg, #2563eb 0%, #16a34a 100%);
        box-shadow: 0 10px 22px rgba(37, 99, 235, 0.24);
    }
    .soft-card {
        border: 1px solid rgba(15, 23, 42, 0.06);
    }
    .report-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #fff;
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.06);
        white-space: nowrap;
    }
    .report-table td {
        vertical-align: middle;
    }
    .report-table tbody tr:hover {
        background: #f8fafc;
    }
    @media print {
        .sidebar, .app-topbar, .rep-actions, .rep-filters, .btn, .modal {
            display: none !important;
        }
        main {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            break-inside: avoid;
            box-shadow: none !important;
        }
        .report-table {
            font-size: 11px;
        }
    }
</style>

<div class="rep-page-header d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
    <div class="d-flex align-items-start gap-3">
        <div class="rep-icon flex-shrink-0">
            <i class="fas fa-router"></i>
        </div>
        <div>
            <h1 class="rep-title h3 mb-1">Reporte de Equipos Instalados</h1>
            <div class="text-muted">Consulta antenas y módems instalados con credenciales, red, estado y cliente asociado.</div>
        </div>
    </div>
    <div class="rep-actions d-flex flex-wrap gap-2">
        <a href="<?php echo url('reportes'); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver
        </a>
        <button type="button" class="btn btn-outline-secondary" id="printReportBtn">
            <i class="fas fa-file-pdf me-1"></i>
            Imprimir / PDF
        </button>
        <button type="button" class="btn btn-outline-primary" id="exportCsvBtn">
            <i class="fas fa-file-csv me-1"></i>
            Exportar CSV
        </button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="small text-white-50">Equipos</div>
                <div class="h3 mb-0"><?php echo (int)($stats['total'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="small text-white-50">Antenas</div>
                <div class="h3 mb-0"><?php echo (int)($stats['antenas'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="small text-white-50">Módems</div>
                <div class="h3 mb-0"><?php echo (int)($stats['modems'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stats-card danger">
            <div class="card-body">
                <div class="small text-white-50">Accesos activos</div>
                <div class="h3 mb-0"><?php echo (int)($stats['acceso_activo'] ?? 0); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card soft-card rep-filters mb-3">
    <div class="card-header bg-white">
        <div class="fw-semibold">
            <i class="fas fa-filter me-2 text-primary"></i>
            Filtros
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo url('reportes/equipos-instalados'); ?>">
            <div class="row g-2 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small text-muted">Cliente</label>
                    <input type="search" class="form-control" name="cliente" value="<?php echo htmlspecialchars($filters['cliente'] ?? ''); ?>" placeholder="Nombre o teléfono">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Tipo</label>
                    <select class="form-select" name="tipo_equipo">
                        <option value="">Todos</option>
                        <option value="antena" <?php echo (($filters['tipo_equipo'] ?? '') === 'antena') ? 'selected' : ''; ?>>Antena</option>
                        <option value="modem" <?php echo (($filters['tipo_equipo'] ?? '') === 'modem') ? 'selected' : ''; ?>>Módem</option>
                        <option value="router" <?php echo (($filters['tipo_equipo'] ?? '') === 'router') ? 'selected' : ''; ?>>Router</option>
                        <option value="switch" <?php echo (($filters['tipo_equipo'] ?? '') === 'switch') ? 'selected' : ''; ?>>Switch</option>
                        <option value="access_point" <?php echo (($filters['tipo_equipo'] ?? '') === 'access_point') ? 'selected' : ''; ?>>Access Point</option>
                        <option value="otro" <?php echo (($filters['tipo_equipo'] ?? '') === 'otro') ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Estado</label>
                    <select class="form-select" name="estado_tecnico">
                        <option value="">Todos</option>
                        <?php foreach ($estadoLabels as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (($filters['estado_tecnico'] ?? '') === $value) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small text-muted">MAC Address</label>
                    <input type="search" class="form-control" name="mac_address" value="<?php echo htmlspecialchars($filters['mac_address'] ?? ''); ?>" placeholder="AA:BB">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Dirección IP</label>
                    <input type="search" class="form-control" name="direccion_ip" value="<?php echo htmlspecialchars($filters['direccion_ip'] ?? ''); ?>" placeholder="192.168">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Desde</label>
                    <input type="date" class="form-control" name="fecha_desde" value="<?php echo htmlspecialchars($filters['fecha_desde'] ?? ''); ?>">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Hasta</label>
                    <input type="date" class="form-control" name="fecha_hasta" value="<?php echo htmlspecialchars($filters['fecha_hasta'] ?? ''); ?>">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Orden fecha</label>
                    <select class="form-select" name="orden_fecha">
                        <option value="desc" <?php echo (($filters['orden_fecha'] ?? 'desc') === 'desc') ? 'selected' : ''; ?>>Más recientes</option>
                        <option value="asc" <?php echo (($filters['orden_fecha'] ?? '') === 'asc') ? 'selected' : ''; ?>>Más antiguas</option>
                    </select>
                </div>
                <div class="col-md-12 col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-magnifying-glass me-1"></i>
                            Filtrar
                        </button>
                        <a href="<?php echo url('reportes/equipos-instalados'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-rotate me-1"></i>
                            Restablecer
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card soft-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold">
                <i class="fas fa-table me-2 text-primary"></i>
                Equipos instalados
            </div>
            <div class="small text-muted"><?php echo count($rows); ?> registros encontrados</div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0 report-table" id="equiposInstaladosTable">
                <thead class="table-light">
                    <tr>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Equipo</th>
                        <th>MAC</th>
                        <th>IP</th>
                        <th>SSID</th>
                        <th>Usuario</th>
                        <th>Contraseña</th>
                        <th>Acceso</th>
                        <th>Fecha instalación</th>
                        <th>Estado</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $tipo = (string)($row['tipo_equipo'] ?? '');
                        $estado = (string)($row['estado_tecnico'] ?? '');
                        $equipoTxt = trim(($row['marca'] ?? '') . ' ' . ($row['modelo'] ?? ''));
                        if ($equipoTxt === '') $equipoTxt = 'Equipo #' . (int)($row['id'] ?? 0);
                        $detalle = [
                            'Cliente' => $row['cliente_nombre'] ?? '',
                            'Telefono' => $row['cliente_telefono'] ?? '',
                            'Tipo' => $tipoLabels[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo)),
                            'Equipo' => $equipoTxt,
                            'Serie' => $row['numero_serie'] ?? '',
                            'MAC' => $row['mac_address'] ?? '',
                            'IP' => $row['direccion_ip'] ?? '',
                            'SSID' => $row['ssid'] ?? '',
                            'Usuario' => $row['usuario_acceso'] ?? '',
                            'Password' => $row['password_acceso'] ?? '',
                            'Acceso' => ((int)($row['acceso_habilitado'] ?? 0) === 1) ? 'Activado' : 'Desactivado',
                            'Fecha' => equipoReportDate($row['fecha_instalacion'] ?? ''),
                            'Estado' => $estadoLabels[$estado] ?? ucfirst(str_replace('_', ' ', $estado)),
                            'Instalacion' => !empty($row['instalacion_id']) ? '#' . (int)$row['instalacion_id'] : 'Sin instalación agrupada',
                            'Observaciones' => $row['observaciones_tecnico'] ?? '',
                        ];
                        ?>
                        <tr>
                            <td>
                                <a href="<?php echo url('clientes/' . (int)($row['cliente_id'] ?? 0)); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($row['cliente_nombre'] ?? 'Sin cliente'); ?>
                                </a>
                                <div class="small text-muted"><?php echo htmlspecialchars($row['cliente_telefono'] ?? ''); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($tipoLabels[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo))); ?></td>
                            <td>
                                <a href="<?php echo url('equipos/' . (int)($row['id'] ?? 0)); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($equipoTxt); ?>
                                </a>
                                <div class="small text-muted"><?php echo !empty($row['numero_serie']) ? 'Serie: ' . htmlspecialchars($row['numero_serie']) : 'Sin serie'; ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($row['mac_address'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['direccion_ip'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['ssid'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['usuario_acceso'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['password_acceso'] ?: '-'); ?></td>
                            <td>
                                <?php if ($tipo === 'modem'): ?>
                                    <span class="badge <?php echo ((int)($row['acceso_habilitado'] ?? 0) === 1) ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ((int)($row['acceso_habilitado'] ?? 0) === 1) ? 'Activado' : 'Desactivado'; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(equipoReportDate($row['fecha_instalacion'] ?? '')); ?></td>
                            <td>
                                <span class="badge <?php echo htmlspecialchars(equipoReportBadge($estado)); ?>">
                                    <?php echo htmlspecialchars($estadoLabels[$estado] ?? ucfirst(str_replace('_', ' ', $estado))); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-primary btn-sm btn-detalle" data-detail="<?php echo htmlspecialchars(json_encode($detalle, JSON_UNESCAPED_UNICODE)); ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($rows)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                <div class="fw-semibold">No hay equipos para mostrar</div>
                <div class="text-muted small">Ajusta los filtros o registra equipos instalados.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="detalleEquipoModal" tabindex="-1" aria-labelledby="detalleEquipoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleEquipoModalLabel">
                    <i class="fas fa-router me-2"></i>
                    Detalle del equipo instalado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody id="detalleEquipoBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const table = document.getElementById('equiposInstaladosTable');
    const exportBtn = document.getElementById('exportCsvBtn');
    const printBtn = document.getElementById('printReportBtn');
    const detalleBody = document.getElementById('detalleEquipoBody');
    const detalleModalEl = document.getElementById('detalleEquipoModal');
    const detalleModal = detalleModalEl && window.bootstrap ? new bootstrap.Modal(detalleModalEl) : null;

    function escapeCsv(value) {
        const s = String(value ?? '');
        if (/[\n\r,\"]/g.test(s)) return '"' + s.replace(/\"/g, '""') + '"';
        return s;
    }

    function exportCsv() {
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        if (!rows.length) {
            window.alert('No hay registros para exportar.');
            return;
        }

        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim()).slice(0, -1);
        const lines = [headers.map(escapeCsv).join(',')];
        rows.forEach(tr => {
            const values = Array.from(tr.querySelectorAll('td')).slice(0, -1).map(td => td.innerText.replace(/\s+/g, ' ').trim());
            lines.push(values.map(escapeCsv).join(','));
        });

        const blob = new Blob(["\ufeff" + lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const d = new Date();
        const stamp = d.getFullYear() + String(d.getMonth() + 1).padStart(2, '0') + String(d.getDate()).padStart(2, '0');
        a.href = url;
        a.download = 'equipos_instalados_' + stamp + '.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-detalle');
        if (!btn || !detalleBody) return;

        const detail = JSON.parse(btn.dataset.detail || '{}');
        detalleBody.innerHTML = '';
        Object.keys(detail).forEach((key) => {
            const tr = document.createElement('tr');
            const th = document.createElement('th');
            const td = document.createElement('td');
            th.textContent = key;
            td.textContent = detail[key] || '-';
            tr.appendChild(th);
            tr.appendChild(td);
            detalleBody.appendChild(tr);
        });

        if (detalleModal) detalleModal.show();
    });

    if (exportBtn) exportBtn.addEventListener('click', exportCsv);
    if (printBtn) printBtn.addEventListener('click', function() { window.print(); });
})();
</script>

<?php include 'views/layouts/footer.php'; ?>
