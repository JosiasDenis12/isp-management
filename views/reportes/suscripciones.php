<?php include 'views/layouts/header.php'; ?>

<style>
    .sus-page-header {
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    .sus-page-header .sus-title {
        font-weight: 700;
        letter-spacing: -0.2px;
    }
    .sus-page-header .sus-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        box-shadow: 0 10px 22px rgba(79, 172, 254, 0.28);
    }

    .soft-card {
        border: 1px solid rgba(15, 23, 42, 0.06);
    }

    .suscripciones-toolbar .form-label {
        font-size: .85rem;
        color: var(--bs-secondary-color);
    }
    .suscripciones-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #fff;
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.06);
        white-space: nowrap;
    }
    .suscripciones-table td {
        vertical-align: middle;
    }
    .suscripciones-table tbody tr {
        background: #fff;
        box-shadow: inset 4px 0 0 transparent;
        transition: background-color .15s ease, box-shadow .15s ease;
    }
    .suscripciones-table tbody tr:hover {
        background: #f8fafc;
    }
    .sus-row-vencido {
        box-shadow: inset 4px 0 0 rgba(220, 53, 69, 0.85);
    }
    .sus-row-porvencer {
        box-shadow: inset 4px 0 0 rgba(255, 193, 7, 0.9);
    }
    .sus-row-aldia {
        box-shadow: inset 4px 0 0 rgba(25, 135, 84, 0.85);
    }
    .sus-badge {
        font-weight: 600;
        letter-spacing: .2px;
    }
    .sus-empty {
        border: 1px dashed rgba(15, 23, 42, 0.18);
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.65);
    }
</style>

<div class="sus-page-header d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
    <div class="d-flex align-items-start gap-3">
        <div class="sus-icon flex-shrink-0">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div>
            <h1 class="sus-title h3 mb-1">Reporte de Suscripciones</h1>
            <div class="text-muted">Consulta vencimientos, pagos registrados y clientes sin pagos rápidamente.</div>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?php echo url('reportes'); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver
        </a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-download me-1"></i>
                Exportar
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                    <button type="button" class="dropdown-item" id="exportCsvBtn">
                        <i class="fas fa-file-csv me-2"></i>
                        Exportar CSV (visibles)
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="row g-3 mb-3" id="summaryRow">
    <div class="col-6 col-lg-3">
        <div class="card stats-card secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50">Sin pagos</div>
                        <div class="h3 mb-0" id="statSinPagos">0</div>
                    </div>
                    <i class="fas fa-receipt fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50">Al día</div>
                        <div class="h3 mb-0" id="statAlDia">0</div>
                    </div>
                    <i class="fas fa-circle-check fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50">Por vencer</div>
                        <div class="h3 mb-0" id="statPorVencer">0</div>
                    </div>
                    <i class="fas fa-triangle-exclamation fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stats-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50">Vencidos</div>
                        <div class="h3 mb-0" id="statVencidos">0</div>
                    </div>
                    <i class="fas fa-circle-xmark fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50">Total</div>
                        <div class="h3 mb-0" id="statTotal">0</div>
                    </div>
                    <i class="fas fa-users fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="card soft-card">
            <div class="card-header bg-white">
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
                    <div>
                        <div class="fw-semibold">
                            <i class="fas fa-filter me-2 text-primary"></i>
                            Filtros
                        </div>
                        <div class="small text-muted">Busca por nombre o teléfono y filtra por estado.</div>
                    </div>
                    <div class="small text-muted" id="countLabel"></div>
                </div>
            </div>
            <div class="card-body suscripciones-toolbar">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-6">
                        <label class="form-label">Buscar</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Nombre, teléfono..." autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="clearBtn" title="Limpiar búsqueda">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Estado</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                            <select class="form-select" id="filterSelect">
                                <option value="all">Todos</option>
                                <option value="sinpagos">Sin pagos</option>
                                <option value="aldia">Al día</option>
                                <option value="porvencer">Por vencer (≤ 7 días)</option>
                                <option value="vencidos">Vencidos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 text-lg-end">
                        <label class="form-label">Acciones</label>
                        <div class="d-grid d-lg-block">
                            <button type="button" class="btn btn-outline-primary" id="resetFiltersBtn">
                                <i class="fas fa-rotate me-1"></i>
                                Restablecer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card soft-card">
            <div class="card-header bg-white">
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
                    <div>
                        <div class="fw-semibold">
                            <i class="fas fa-table me-2 text-primary"></i>
                            Suscripciones
                        </div>
                        <div class="small text-muted">Estado calculado por fecha de vencimiento.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="exportCsvBtnSecondary">
                            <i class="fas fa-download me-1"></i>
                            Exportar CSV
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0 suscripciones-table" id="suscripcionesTable">
                        <thead class="table-light">
                            <tr>
                                <th class="d-none d-lg-table-cell">Fecha Suscripción</th>
                                <th>Nombre</th>
                                <th class="d-none d-xl-table-cell">Número Cel</th>
                                <th>Servicio</th>
                                <th class="text-end d-none d-xl-table-cell">Pagado</th>
                                <th class="text-center d-none d-xl-table-cell">Meses</th>
                                <th>Vence</th>
                                <th class="text-center d-none d-lg-table-cell">Corte</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Días</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php foreach (($rows ?? []) as $r): ?>
                        <?php
                        $fechaContr = $r['fecha_contratacion'] ?? null;
                        $fechaVenc = $r['fecha_vencimiento_calc'] ?? null;
                        $ultimoEstadoPago = (string)($r['ultimo_estado_pago'] ?? '');
                        $tienePagos = (int)($r['total_pagos'] ?? 0) > 0;

                        $diasRestantes = null;
                        $diaCorte = (int)($r['dia_corte'] ?? 0);
                        if (!empty($fechaVenc)) {
                            $hoy = new DateTime(date('Y-m-d'));
                            $venc = new DateTime(date('Y-m-d', strtotime($fechaVenc)));
                            $diasRestantes = (int)$hoy->diff($venc)->format('%r%a');
                        }

                        $badgeDias = 'bg-secondary';
                        if ($diasRestantes !== null) {
                            if ($diasRestantes < 0) $badgeDias = 'bg-danger';
                            else if ($diasRestantes <= 7) $badgeDias = 'bg-warning';
                            else $badgeDias = 'bg-success';
                        }

                        $statusKey = 'sinpagos';
                        $statusTxt = 'Sin pagos';
                        $statusBadge = 'bg-secondary';
                        $rowClass = '';

                        if ($tienePagos) {
                            if ($ultimoEstadoPago === 'pagado') {
                                $statusKey = 'aldia';
                                $statusTxt = 'Al día';
                                $statusBadge = 'bg-success';
                                $rowClass = 'sus-row-aldia';
                                $diasRestantes = null;
                            } elseif ($ultimoEstadoPago !== '' && $diasRestantes !== null) {
                                if ($diasRestantes < 0) {
                                    $statusKey = 'vencido';
                                    $statusTxt = 'Vencido';
                                    $statusBadge = 'bg-danger';
                                    $rowClass = 'sus-row-vencido';
                                } else if ($diasRestantes <= 7) {
                                    $statusKey = 'porvencer';
                                    $statusTxt = 'Por vencer';
                                    $statusBadge = 'bg-warning text-dark';
                                    $rowClass = 'sus-row-porvencer';
                                } else {
                                    $statusKey = 'aldia';
                                    $statusTxt = 'Al día';
                                    $statusBadge = 'bg-success';
                                    $rowClass = 'sus-row-aldia';
                                }
                            } else {
                                $statusKey = 'aldia';
                                $statusTxt = 'Al día';
                                $statusBadge = 'bg-success';
                                $rowClass = 'sus-row-aldia';
                            }
                        }

                        $tipoConexion = (string)($r['tipo_conexion'] ?? '');
                        $tipoConexionTxt = $tipoConexion !== '' ? ucfirst(str_replace('_', ' ', $tipoConexion)) : '—';
                        $plan = $r['plan_mensual'] ?? null;
                        $planTxt = $plan !== null && $plan !== '' ? '$' . number_format((float)$plan, 2) . '/mes' : '—';
                        $servicioTxt = $tipoConexionTxt . ' • ' . $planTxt;

                        $telefonoRaw = (string)($r['telefono'] ?? '');
                        $telefonoDigits = preg_replace('/\D+/', '', $telefonoRaw);
                        $nombre = (string)($r['nombre'] ?? '');

                        $vencTxt = !empty($fechaVenc) ? date('d/m/Y', strtotime($fechaVenc)) : '—';
                        $contrTxt = !empty($fechaContr) ? date('d/m/Y', strtotime($fechaContr)) : '—';

                        $defaultMsg = "Hola {$nombre}, te recordamos tu servicio ({$servicioTxt}). Tu fecha de vencimiento es {$vencTxt}.";

                        $searchText = $nombre . ' ' . $telefonoRaw;
                        $searchText = function_exists('mb_strtolower')
                            ? mb_strtolower($searchText, 'UTF-8')
                            : strtolower($searchText);
                        ?>
                        <tr class="<?php echo htmlspecialchars($rowClass); ?>"
                            data-search="<?php echo htmlspecialchars($searchText); ?>"
                            data-dias="<?php echo htmlspecialchars((string)($diasRestantes ?? '')); ?>"
                            data-status="<?php echo htmlspecialchars($statusKey); ?>"
                        >
                            <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($contrTxt); ?></td>
                            <td>
                                <a href="<?php echo url('clientes/' . $r['id']); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($nombre); ?>
                                </a>
                                <div class="small text-muted d-xl-none">
                                    <?php echo htmlspecialchars($telefonoRaw !== '' ? $telefonoRaw : 'Sin teléfono'); ?>
                                </div>
                            </td>
                            <td class="d-none d-xl-table-cell"><?php echo htmlspecialchars($telefonoRaw !== '' ? $telefonoRaw : '—'); ?></td>
                            <td><?php echo htmlspecialchars($servicioTxt); ?></td>
                            <td class="text-end d-none d-xl-table-cell"><?php echo '$' . number_format((float)($r['total_pagado'] ?? 0), 2); ?></td>
                            <td class="text-center d-none d-xl-table-cell"><?php echo (int)($r['meses_pagados'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars($vencTxt); ?></td>
                            <td class="text-center d-none d-lg-table-cell">
                                <?php if ($diaCorte): ?>
                                    <span class="badge rounded-pill text-bg-light border" title="Día de corte">
                                        <?php echo (int)$diaCorte; ?>
                                    </span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge sus-badge <?php echo htmlspecialchars($statusBadge); ?>">
                                    <?php echo htmlspecialchars($statusTxt); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($diasRestantes === null): ?>
                                    —
                                <?php else: ?>
                                    <span class="badge sus-badge <?php echo $badgeDias; ?>">
                                        <?php echo (int)$diasRestantes; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($telefonoDigits === ''): ?>
                                    <span class="text-muted small">Sin teléfono</span>
                                <?php else: ?>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button
                                            type="button"
                                            class="btn btn-outline-success btn-whatsapp"
                                            data-phone="<?php echo htmlspecialchars($telefonoDigits); ?>"
                                            data-default-message="<?php echo htmlspecialchars($defaultMsg); ?>"
                                            title="Enviar WhatsApp"
                                        >
                                            <i class="fab fa-whatsapp"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary btn-copy-phone"
                                            data-phone="<?php echo htmlspecialchars($telefonoDigits); ?>"
                                            title="Copiar teléfono"
                                        >
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($rows)): ?>
                    <div class="p-3 p-md-4">
                        <div class="sus-empty text-center py-4">
                            <div class="mb-2">
                                <i class="fas fa-inbox fa-2x text-muted"></i>
                            </div>
                            <div class="fw-semibold">No hay datos para mostrar</div>
                            <div class="text-muted small">Cuando existan suscripciones, aparecerán aquí con su estado y días restantes.</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="small text-muted mt-3">
    Tip: “Días” &lt; 0 significa vencido.
</div>

<script>
(function() {
    const searchInput = document.getElementById('searchInput');
    const filterSelect = document.getElementById('filterSelect');
    const clearBtn = document.getElementById('clearBtn');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const exportCsvBtnSecondary = document.getElementById('exportCsvBtnSecondary');
    const table = document.getElementById('suscripcionesTable');
    const countLabel = document.getElementById('countLabel');
    const statTotal = document.getElementById('statTotal');
    const statSinPagos = document.getElementById('statSinPagos');
    const statAlDia = document.getElementById('statAlDia');
    const statPorVencer = document.getElementById('statPorVencer');
    const statVencidos = document.getElementById('statVencidos');
    if (!table) return;

    const rows = Array.from(table.querySelectorAll('tbody tr'));

    function updateStats() {
        const totals = { total: 0, sinpagos: 0, aldia: 0, porvencer: 0, vencido: 0 };
        rows.forEach(tr => {
            totals.total++;
            const st = tr.dataset.status || 'sindato';
            if (st === 'sinpagos') totals.sinpagos++;
            if (st === 'aldia') totals.aldia++;
            if (st === 'porvencer') totals.porvencer++;
            if (st === 'vencido') totals.vencido++;
        });

        if (statTotal) statTotal.textContent = String(totals.total);
        if (statSinPagos) statSinPagos.textContent = String(totals.sinpagos);
        if (statAlDia) statAlDia.textContent = String(totals.aldia);
        if (statPorVencer) statPorVencer.textContent = String(totals.porvencer);
        if (statVencidos) statVencidos.textContent = String(totals.vencido);
    }

    function applyFilters() {
        const q = (searchInput.value || '').trim().toLowerCase();
        const filter = filterSelect.value;

        let visible = 0;
        rows.forEach(tr => {
            const hay = (tr.dataset.search || '');
            const diasStr = tr.dataset.dias;
            const dias = diasStr === '' || diasStr == null ? null : parseInt(diasStr, 10);
            const st = tr.dataset.status || 'sindato';

            let ok = true;
            if (q && !hay.includes(q)) ok = false;

            if (ok && filter !== 'all') {
                if (filter === 'sinpagos') ok = st === 'sinpagos';
                else if (filter === 'aldia') ok = st === 'aldia';
                else if (filter === 'vencidos') ok = st === 'vencido';
                else if (filter === 'porvencer') ok = st === 'porvencer';
                else if (dias === null) ok = false;
            }

            tr.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });

        if (countLabel) {
            countLabel.textContent = visible + ' visibles';
        }
    }

    function exportVisibleToCsv() {
        const visibleRows = rows.filter(tr => tr.style.display !== 'none');
        if (!visibleRows.length) {
            window.alert('No hay filas visibles para exportar.');
            return;
        }

        const headerCells = Array.from(table.querySelectorAll('thead th'));
        const headers = headerCells
            .map(th => (th.innerText || '').trim())
            .filter(Boolean);

        // Quitar la última columna (Acciones)
        const csvHeaders = headers.slice(0, Math.max(0, headers.length - 1));

        const lines = [];
        lines.push(csvHeaders.map(escapeCsv).join(','));

        visibleRows.forEach(tr => {
            const cells = Array.from(tr.querySelectorAll('td'));
            const values = cells
                .slice(0, Math.max(0, cells.length - 1))
                .map(td => (td.innerText || '').replace(/\s+/g, ' ').trim());
            lines.push(values.map(escapeCsv).join(','));
        });

        const csvContent = lines.join('\n');
        const blob = new Blob(["\ufeff" + csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const d = new Date();
        const stamp = d.getFullYear() + String(d.getMonth() + 1).padStart(2, '0') + String(d.getDate()).padStart(2, '0');
        a.href = url;
        a.download = 'suscripciones_' + stamp + '.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }

    function escapeCsv(value) {
        const s = String(value ?? '');
        if (/[\n\r,\"]/g.test(s)) {
            return '"' + s.replace(/\"/g, '""') + '"';
        }
        return s;
    }

    let debounceTimer = null;
    function scheduleApply() {
        if (debounceTimer) window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(applyFilters, 60);
    }

    searchInput.addEventListener('input', scheduleApply);
    filterSelect.addEventListener('change', applyFilters);
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            applyFilters();
        });
    }
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterSelect.value = 'all';
            applyFilters();
        });
    }

    updateStats();
    applyFilters();

    if (exportCsvBtn) exportCsvBtn.addEventListener('click', exportVisibleToCsv);
    if (exportCsvBtnSecondary) exportCsvBtnSecondary.addEventListener('click', exportVisibleToCsv);

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-whatsapp');
        const copyBtn = e.target.closest('.btn-copy-phone');

        if (btn) {
            const phone = btn.dataset.phone;
            const def = btn.dataset.defaultMessage || '';
            const msg = window.prompt('Mensaje a enviar (puedes editarlo):', def);
            if (msg == null) return;

            const url = 'https://wa.me/' + encodeURIComponent(phone) + '?text=' + encodeURIComponent(msg);
            window.open(url, '_blank');
            return;
        }

        if (copyBtn) {
            const phone = copyBtn.dataset.phone || '';
            if (!phone) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(phone).catch(() => {
                    window.prompt('Copia el teléfono:', phone);
                });
            } else {
                window.prompt('Copia el teléfono:', phone);
            }
        }
    });
})();
</script>

<?php include 'views/layouts/footer.php'; ?>
