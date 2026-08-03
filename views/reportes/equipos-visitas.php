<?php include 'views/layouts/header.php'; ?>

<?php
$rows = $rows ?? [];
$stats = $stats ?? [];
$filters = $filters ?? [];

$estadoVisitaLabels = [
    'programada' => 'Programada',
    'pendiente' => 'Pendiente',
    'completada' => 'Completada',
    'cancelada' => 'Cancelada',
    'reprogramada' => 'Reprogramada',
];

$estadoEquipoLabels = [
    'operativo' => 'Activo',
    'necesita_revision' => 'Pendiente / Revisión',
    'dañado' => 'En reparación',
    'danado' => 'En reparación',
    'fuera_de_servicio' => 'Fuera de servicio',
    'en_mantenimiento' => 'En mantenimiento',
];

$tipoVisitaLabels = [
    'instalacion' => 'Instalación',
    'mantenimiento' => 'Mantenimiento',
    'reparacion' => 'Reparación',
    'revision' => 'Revisión',
];

function reporteBadgeVisita($estado) {
    switch ($estado) {
        case 'completada': return 'bg-success';
        case 'cancelada': return 'bg-danger';
        case 'reprogramada': return 'bg-warning text-dark';
        case 'pendiente': return 'bg-secondary';
        case 'programada': return 'bg-info';
        default: return 'bg-light text-dark border';
    }
}

function reporteBadgeEquipo($estado) {
    switch ($estado) {
        case 'operativo': return 'bg-success';
        case 'necesita_revision': return 'bg-warning text-dark';
        case 'dañado':
        case 'danado': return 'bg-danger';
        case 'fuera_de_servicio': return 'bg-dark';
        case 'en_mantenimiento': return 'bg-info';
        default: return 'bg-secondary';
    }
}

function reporteFecha($value, $format = 'd/m/Y H:i') {
    if (empty($value)) return '-';
    $ts = strtotime($value);
    return $ts ? date($format, $ts) : '-';
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
        background: linear-gradient(135deg, #0ea5e9 0%, #22c55e 100%);
        box-shadow: 0 10px 22px rgba(14, 165, 233, 0.24);
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
    .report-text-clip {
        max-width: 280px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .report-empty {
        border: 1px dashed rgba(15, 23, 42, 0.18);
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.65);
    }
    @media print {
        .sidebar, .app-topbar, .rep-actions, .rep-filters, .btn, .dropdown {
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
            <i class="fas fa-clipboard-check"></i>
        </div>
        <div>
            <h1 class="rep-title h3 mb-1">Reporte de Equipos y Visitas Técnicas</h1>
            <div class="text-muted">Registro de actividades realizadas, seguimiento técnico y resultado de cada visita.</div>
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
                <div class="small text-white-50">Visitas registradas</div>
                <div class="h3 mb-0"><?php echo (int)($stats['total_visitas'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="small text-white-50">Completadas</div>
                <div class="h3 mb-0"><?php echo (int)($stats['completadas'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="small text-white-50">Pendientes</div>
                <div class="h3 mb-0"><?php echo (int)($stats['pendientes'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stats-card danger">
            <div class="card-body">
                <div class="small text-white-50">Equipos involucrados</div>
                <div class="h3 mb-0"><?php echo (int)($stats['equipos_involucrados'] ?? 0); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card soft-card rep-filters mb-3">
    <div class="card-header bg-white">
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
            <div>
                <div class="fw-semibold">
                    <i class="fas fa-filter me-2 text-primary"></i>
                    Filtros
                </div>
                <div class="small text-muted">Busca por fecha, cliente, técnico, equipo, tipo y estado.</div>
            </div>
            <div class="small text-muted"><?php echo count($rows); ?> registros encontrados</div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo url('reportes/equipos-visitas'); ?>">
            <div class="row g-2 align-items-end">
                <div class="col-md-3 col-lg-2">
                    <label class="form-label small text-muted">Desde</label>
                    <input type="date" class="form-control" name="fecha_desde" value="<?php echo htmlspecialchars($filters['fecha_desde'] ?? ''); ?>">
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label small text-muted">Hasta</label>
                    <input type="date" class="form-control" name="fecha_hasta" value="<?php echo htmlspecialchars($filters['fecha_hasta'] ?? ''); ?>">
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label small text-muted">Cliente</label>
                    <input type="search" class="form-control" name="cliente" placeholder="Nombre o teléfono" value="<?php echo htmlspecialchars($filters['cliente'] ?? ''); ?>">
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label small text-muted">Técnico</label>
                    <input type="search" class="form-control" name="tecnico" placeholder="Responsable" value="<?php echo htmlspecialchars($filters['tecnico'] ?? ''); ?>">
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label small text-muted">Equipo</label>
                    <input type="search" class="form-control" name="equipo" placeholder="Marca, modelo, serie" value="<?php echo htmlspecialchars($filters['equipo'] ?? ''); ?>">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Estado visita</label>
                    <select class="form-select" name="estado_visita">
                        <option value="">Todos</option>
                        <?php foreach ($estadoVisitaLabels as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (($filters['estado_visita'] ?? '') === $value) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Estado equipo</label>
                    <select class="form-select" name="estado_equipo">
                        <option value="">Todos</option>
                        <?php foreach ($estadoEquipoLabels as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (($filters['estado_equipo'] ?? '') === $value) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small text-muted">Tipo servicio</label>
                    <select class="form-select" name="tipo_visita">
                        <option value="">Todos</option>
                        <?php foreach ($tipoVisitaLabels as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (($filters['tipo_visita'] ?? '') === $value) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12 col-lg-6">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-magnifying-glass me-1"></i>
                            Filtrar
                        </button>
                        <a href="<?php echo url('reportes/equipos-visitas'); ?>" class="btn btn-outline-secondary">
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
    <div class="card-header bg-white">
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
            <div>
                <div class="fw-semibold">
                    <i class="fas fa-table me-2 text-primary"></i>
                    Registro de actividades
                </div>
                <div class="small text-muted">Trabajos realizados por visita, equipo, cliente y responsable.</div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0 report-table" id="equiposVisitasTable">
                <thead class="table-light">
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Cliente</th>
                        <th>Equipo</th>
                        <th>Estado equipo</th>
                        <th>Tipo servicio</th>
                        <th>Técnico</th>
                        <th>Actividades / observaciones</th>
                        <th>Estado visita</th>
                        <th>Seguimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $estadoVisita = (string)($row['estado_visita'] ?? '');
                        $estadoEquipo = (string)($row['estado_tecnico'] ?? '');
                        $tipoVisita = (string)($row['tipo_visita'] ?? '');
                        $equipoTxt = trim(($row['tipo_equipo'] ?? '') . ' ' . ($row['marca'] ?? '') . ' ' . ($row['modelo'] ?? ''));
                        if ($equipoTxt === '') $equipoTxt = 'Equipo #' . (int)($row['equipo_id'] ?? 0);
                        $serie = trim((string)($row['numero_serie'] ?? ''));
                        $actividad = trim((string)($row['actividades_observaciones'] ?? ''));
                        $actividadTxt = $actividad !== '' ? $actividad : 'Sin actividades registradas';
                        $seguimiento = 'Registrada: ' . reporteFecha($row['visita_registrada'] ?? '', 'd/m/Y H:i');
                        if (!empty($row['equipo_actualizado'])) {
                            $seguimiento .= ' | Equipo actualizado: ' . reporteFecha($row['equipo_actualizado'], 'd/m/Y H:i');
                        }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars(reporteFecha($row['fecha_visita'] ?? '')); ?></td>
                            <td>
                                <a href="<?php echo url('clientes/' . (int)($row['cliente_id'] ?? 0)); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($row['cliente_nombre'] ?? 'Sin cliente'); ?>
                                </a>
                                <div class="small text-muted"><?php echo htmlspecialchars($row['cliente_telefono'] ?? ''); ?></div>
                            </td>
                            <td>
                                <a href="<?php echo url('equipos/' . (int)($row['equipo_id'] ?? 0)); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($equipoTxt); ?>
                                </a>
                                <div class="small text-muted"><?php echo $serie !== '' ? 'Serie: ' . htmlspecialchars($serie) : 'Sin serie'; ?></div>
                            </td>
                            <td>
                                <span class="badge <?php echo htmlspecialchars(reporteBadgeEquipo($estadoEquipo)); ?>">
                                    <?php echo htmlspecialchars($estadoEquipoLabels[$estadoEquipo] ?? ucfirst(str_replace('_', ' ', $estadoEquipo))); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($tipoVisitaLabels[$tipoVisita] ?? ucfirst(str_replace('_', ' ', $tipoVisita))); ?></td>
                            <td><?php echo htmlspecialchars($row['tecnico_nombre'] ?: 'No asignado'); ?></td>
                            <td>
                                <div class="report-text-clip" title="<?php echo htmlspecialchars($actividadTxt); ?>">
                                    <?php echo htmlspecialchars($actividadTxt); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?php echo htmlspecialchars(reporteBadgeVisita($estadoVisita)); ?>">
                                    <?php echo htmlspecialchars($estadoVisitaLabels[$estadoVisita] ?? ucfirst(str_replace('_', ' ', $estadoVisita))); ?>
                                </span>
                            </td>
                            <td>
                                <div class="report-text-clip" title="<?php echo htmlspecialchars($seguimiento); ?>">
                                    <?php echo htmlspecialchars($seguimiento); ?>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-outline-primary" href="<?php echo url('equipos/' . (int)$row['equipo_id'] . '/visitas/' . (int)$row['visita_id']); ?>" title="Ver visita">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a class="btn btn-outline-secondary" href="<?php echo url('equipos/' . (int)$row['equipo_id'] . '/visitas/' . (int)$row['visita_id'] . '/edit'); ?>" title="Editar visita">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($rows)): ?>
            <div class="p-3 p-md-4">
                <div class="report-empty text-center py-4">
                    <div class="mb-2">
                        <i class="fas fa-inbox fa-2x text-muted"></i>
                    </div>
                    <div class="fw-semibold">No hay actividades para mostrar</div>
                    <div class="text-muted small">Ajusta los filtros o registra visitas técnicas para alimentar este reporte.</div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    const table = document.getElementById('equiposVisitasTable');
    const exportBtn = document.getElementById('exportCsvBtn');
    const printBtn = document.getElementById('printReportBtn');
    if (!table) return;

    function escapeCsv(value) {
        const s = String(value ?? '');
        if (/[\n\r,\"]/g.test(s)) {
            return '"' + s.replace(/\"/g, '""') + '"';
        }
        return s;
    }

    function exportCsv() {
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        if (!rows.length) {
            window.alert('No hay registros para exportar.');
            return;
        }

        const headers = Array.from(table.querySelectorAll('thead th'))
            .map(th => (th.innerText || '').trim())
            .slice(0, -1);

        const lines = [headers.map(escapeCsv).join(',')];
        rows.forEach(tr => {
            const values = Array.from(tr.querySelectorAll('td'))
                .slice(0, -1)
                .map(td => (td.innerText || '').replace(/\s+/g, ' ').trim());
            lines.push(values.map(escapeCsv).join(','));
        });

        const blob = new Blob(["\ufeff" + lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const d = new Date();
        const stamp = d.getFullYear() + String(d.getMonth() + 1).padStart(2, '0') + String(d.getDate()).padStart(2, '0');
        a.href = url;
        a.download = 'equipos_visitas_tecnicas_' + stamp + '.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }

    if (exportBtn) exportBtn.addEventListener('click', exportCsv);
    if (printBtn) printBtn.addEventListener('click', function() {
        window.print();
    });
})();
</script>

<?php include 'views/layouts/footer.php'; ?>
