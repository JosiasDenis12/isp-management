<?php include 'views/layouts/header.php'; ?>

<?php
    $pagos = $pagos ?? [];

    $kpis = $kpis ?? [];
    $totalPagos  = (int)($kpis['total_pagos_mes'] ?? 0);
    $pagados     = array_values(array_filter($pagos, fn($p) => ($p['estado'] ?? '') === 'pagado'));
    $pendientes  = array_values(array_filter($pagos, fn($p) => ($p['estado'] ?? '') === 'pendiente'));
    $vencidos    = array_values(array_filter($pagos, fn($p) => ($p['estado'] ?? '') === 'vencido'));
    $ingresos    = (float)($kpis['ingresos_mes'] ?? 0);
    $pagadosMes = (int)($kpis['pagos_realizados_mes'] ?? 0);
    $pendientesMes = (int)($kpis['pagos_pendientes'] ?? 0);
    $vencidosMes = (int)($kpis['clientes_vencidos'] ?? 0);

    // % change placeholders – replace with real queries from controller if available
    $variacion = function ($actual, $anterior) {
        $actual = (float)$actual; $anterior = (float)$anterior;
        if ($anterior == 0.0) return $actual > 0 ? 100 : 0;
        return round((($actual - $anterior) / abs($anterior)) * 100);
    };
    $pctTotal = $variacion($totalPagos, $kpis['total_pagos_mes_anterior'] ?? 0);
    $pctPagados = $variacion($pagadosMes, $kpis['pagos_realizados_mes_anterior'] ?? 0);
    $pctPend = $variacion($pendientesMes, $kpis['pagos_pendientes_mes_anterior'] ?? 0);
    $pctIngresos = $variacion($ingresos, $kpis['ingresos_mes_anterior'] ?? 0);

    // Método de pago options
    $metodoPagoOptions = [];
    foreach ($pagos as $p) {
        $m = (string)($p['metodo_pago'] ?? '');
        if ($m !== '') $metodoPagoOptions[$m] = true;
    }
    $metodoPagoOptions = array_keys($metodoPagoOptions);
    sort($metodoPagoOptions);

    // Próximos vencimientos: preferir cálculo de suscripción del controlador; mantener fallback al historial crudo.
    if (!empty($proximosVencimientos) && is_array($proximosVencimientos)) {
        $proximosVenc = array_slice(array_values($proximosVencimientos), 0, 4);
    } else {
        $proximosVenc = array_filter($pagos, fn($p) => ($p['estado'] ?? '') === 'pendiente' && !empty($p['fecha_vencimiento']));
        usort($proximosVenc, fn($a, $b) => strtotime($a['fecha_vencimiento']) - strtotime($b['fecha_vencimiento']));
        $proximosVenc = array_slice(array_values($proximosVenc), 0, 4);
    }

    // Datos para gráfico de ingresos por mes (últimos 6 meses)
    $monthlyData = [];
    for ($i = 5; $i >= 0; $i--) {
        $ts    = strtotime("-$i months");
        $key   = date('Y-m', $ts);
       $label = ucfirst(date('M', $ts));
        $monthlyData[$key] = ['label' => $label, 'total' => 0];
    }
    foreach ($pagados as $p) {
        $key = date('Y-m', strtotime((string)($p['fecha_pago'] ?? '')));
        if (isset($monthlyData[$key])) {
            $monthlyData[$key]['total'] += (float)($p['monto'] ?? 0);
        }
    }
    $chartLabels = json_encode(array_values(array_column($monthlyData, 'label')));
    $chartData   = json_encode(array_values(array_column($monthlyData, 'total')));

    // Método íconos
    $metodosIconos = [
        'transferencia' => 'fa-building-columns',
        'efectivo'      => 'fa-money-bill-wave',
        'paypal'        => 'fa-paypal',
        'tarjeta'       => 'fa-credit-card',
    ];

    // Avatar initials helper
    function avatarInitials(string $nombre): string {
        $words = preg_split('/\s+/', trim($nombre));
        $letters = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $letters .= mb_strtoupper(mb_substr($w, 0, 1, 'UTF-8'), 'UTF-8');
        }
        return $letters ?: 'CL';
    }

    // Avatar colors pool
    $avatarPalette = [
        ['bg'=>'bg-primary-subtle','text'=>'text-primary'],
        ['bg'=>'bg-success-subtle','text'=>'text-success'],
        ['bg'=>'bg-info-subtle',   'text'=>'text-info'],
        ['bg'=>'bg-warning-subtle','text'=>'text-warning'],
        ['bg'=>'bg-purple-subtle', 'text'=>'text-purple'],
    ];
?>

<style>
/* ─── Pagos page overrides ─────────────────────────────────────────────── */
.bg-purple-subtle { background-color: rgba(139,92,246,.12) !important; }
.text-purple      { color: #7c3aed !important; }

/* Stat tiles */
.stat-tile        { border:none; border-radius:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); transition:box-shadow .2s; }
.stat-tile:hover  { box-shadow:0 4px 16px rgba(0,0,0,.10); }
.stat-label       { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--bs-secondary-color); margin-bottom:.2rem; }
.stat-value       { font-size:1.9rem; font-weight:800; line-height:1.1; }
.stat-meta        { font-size:.76rem; color:var(--bs-secondary-color); margin-top:.2rem; }
.stat-icon-circle { width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.25rem; flex-shrink:0; }
.stat-side        { display:flex; flex-direction:column; align-items:flex-end; justify-content:space-between; gap:.4rem; }
.sparkline        { width:100px; height:36px; display:block; }
.pct-up           { color:#22c55e; font-size:.75rem; font-weight:600; }
.pct-down         { color:#ef4444; font-size:.75rem; font-weight:600; }

/* Charts row */
.chart-card       { border:none; border-radius:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); }
.chart-title      { font-size:1rem; font-weight:700; }

/* Donut legend */
.donut-legend-dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
.donut-legend-row { display:flex; align-items:center; justify-content:space-between; gap:1rem; font-size:.82rem; padding:.25rem 0; }

/* Vencimientos card */
.venc-card        { border:none; border-radius:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); height:100%; }
.venc-item        { padding:.6rem 0; border-bottom:1px solid rgba(0,0,0,.06); }
.venc-item:last-child { border-bottom:none; }

/* Filter card */
.filter-card      { border:none; border-radius:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); }
.view-toggle .btn { border-radius:8px; }
.view-toggle .btn.active { background:var(--bs-primary); color:#fff; border-color:var(--bs-primary); }

/* Table */
.pagos-table-card { border:none; border-radius:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); overflow:hidden; }
.pagos-table thead th { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; background:var(--bs-light); border-bottom:2px solid rgba(0,0,0,.07); padding:.8rem 1rem; white-space:nowrap; }
.pagos-table tbody td { padding:.85rem 1rem; vertical-align:middle; border-bottom:1px solid rgba(0,0,0,.05); font-size:.875rem; }
.pagos-table tbody tr:last-child td { border-bottom:none; }
.pagos-table tbody tr:hover td { background:rgba(59,130,246,.03); }

/* Client avatar (table) */
.client-avatar-sm { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:700; flex-shrink:0; }

/* Factura link */
.factura-code     { font-family:'SFMono-Regular',Consolas,monospace; font-size:.82rem; color:var(--bs-danger); font-weight:600; }

/* Monto */
.monto-value      { font-weight:700; color:#16a34a; font-size:.95rem; }

/* Estado badge */
.estado-badge     { font-size:.7rem; font-weight:600; padding:.3em .8em; border-radius:50px; display:inline-flex; align-items:center; gap:.3rem; }
.estado-badge .dot { width:6px; height:6px; border-radius:50%; }

/* Action buttons */
.action-btn       { width:30px; height:30px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; padding:0; font-size:.78rem; }

/* Pagination card */
.pagination-card  { border:none; border-radius:1rem; box-shadow:0 1px 4px rgba(0,0,0,.07); }

/* Page title */
.page-title    { font-weight:800; font-size:1.6rem; }
.page-subtitle { font-size:.85rem; }

/* Grid view cards */
.pago-grid-card { border:1px solid rgba(0,0,0,.07); border-radius:1rem; transition:box-shadow .2s, transform .15s; }
.pago-grid-card:hover { box-shadow:0 6px 20px rgba(0,0,0,.10); transform:translateY(-2px); }
</style>

<!-- ─── Header ──────────────────────────────────────────────────────────── -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
    <div class="d-flex align-items-center gap-3">
        <div class="stat-icon-circle bg-primary text-white" style="width:52px;height:52px;font-size:1.4rem;">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div>
            <h1 class="h2 mb-0 page-title">Pagos y Facturación</h1>
            <div class="text-muted page-subtitle">Gestiona y da seguimiento a todos los pagos y facturación de tus clientes.</div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo url('pagos/create'); ?>" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="fas fa-plus"></i>
            Registrar Pago
            <i class="fas fa-chevron-down ms-1" style="font-size:.7rem;opacity:.7;"></i>
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>¡Éxito!</strong> <?php echo htmlspecialchars(urldecode($_GET['success'])); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Error:</strong> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- ─── Stat Tiles ─────────────────────────────────────────────────────── -->
<div class="row mb-4 g-3">
    <!-- Total Pagos -->
    <div class="col-xl col-md-6">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Total Pagos</div>
                        <div class="stat-value" id="kpi-total"><?php echo $totalPagos; ?></div>
                        <div class="stat-meta">Este mes</div>
                        <div class="mt-1 <?php echo $pctTotal >= 0 ? 'pct-up' : 'pct-down'; ?>">
                            <i class="fas fa-arrow-<?php echo $pctTotal >= 0 ? 'up' : 'down'; ?> me-1"></i><?php echo abs($pctTotal); ?>% vs mes anterior
                        </div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon-circle bg-primary-subtle text-primary"><i class="fas fa-file-invoice"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 32 C 20 28, 30 20, 44 22 S 66 30, 80 18 S 100 8, 118 10" fill="none" stroke="rgba(59,130,246,.9)" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagados -->
    <div class="col-xl col-md-6">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Pagados</div>
                        <div class="stat-value" id="kpi-pagados"><?php echo $pagadosMes; ?></div>
                        <div class="stat-meta">Este mes</div>
                        <div class="mt-1 pct-up">
                            <i class="fas fa-arrow-up me-1"></i><?php echo $pctPagados; ?>% vs mes anterior
                        </div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon-circle bg-success-subtle text-success"><i class="fas fa-circle-check"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 28 C 14 30, 26 16, 40 18 S 60 26, 74 16 S 98 20, 118 12" fill="none" stroke="rgba(34,197,94,.95)" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pendientes -->
    <div class="col-xl col-md-6">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Pendientes</div>
                        <div class="stat-value" id="kpi-pendientes"><?php echo $pendientesMes; ?></div>
                        <div class="stat-meta">Este mes</div>
                        <div class="mt-1 <?php echo $pctPend > 0 ? 'pct-down' : 'pct-up'; ?>">
                            <i class="fas fa-arrow-<?php echo $pctPend > 0 ? 'up' : 'down'; ?> me-1"></i><?php echo abs($pctPend); ?>% vs mes anterior
                        </div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon-circle bg-warning-subtle text-warning"><i class="fas fa-clock"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 20 C 16 28, 28 16, 42 22 S 64 34, 78 20 S 98 14, 118 24" fill="none" stroke="rgba(234,179,8,.95)" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vencidos -->
    <div class="col-xl col-md-6">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Vencidos</div>
                        <div class="stat-value" id="kpi-vencidos"><?php echo $vencidosMes; ?></div>
                        <div class="stat-meta">Clientes con corte vencido hoy</div>
                        <div class="mt-1 <?php echo $vencidosMes > 0 ? 'pct-down' : 'pct-up'; ?>">
                            <i class="fas fa-triangle-exclamation me-1"></i>Actualizado con la fecha actual
                        </div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon-circle bg-danger-subtle text-danger"><i class="fas fa-triangle-exclamation"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 26 C 16 30, 26 18, 38 22 S 58 34, 72 18 S 94 10, 118 14" fill="none" stroke="rgba(239,68,68,.9)" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Ingresos -->
    <div class="col-xl col-md-12">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Total Ingresos</div>
                        <div class="stat-value" id="kpi-ingresos" style="font-size:1.6rem;">$<?php echo number_format($ingresos, 2); ?></div>
                        <div class="stat-meta">Este mes</div>
                        <div class="mt-1 pct-up">
                            <i class="fas fa-arrow-<?php echo $pctIngresos >= 0 ? 'up' : 'down'; ?> me-1"></i><?php echo abs($pctIngresos); ?>% vs mes anterior
                        </div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon-circle bg-info-subtle text-info"><i class="fas fa-dollar-sign"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 34 C 18 30, 24 24, 38 20 S 58 18, 74 12 S 96 6, 118 4" fill="none" stroke="rgba(6,182,212,.9)" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ─── Charts Row ─────────────────────────────────────────────────────── -->
<div class="row mb-4 g-3">
    <!-- Ingresos últimos 6 meses -->
    <div class="col-xl-6 col-lg-12">
        <div class="card chart-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="chart-title">Ingresos últimos 6 meses</div>
                    <select class="form-select form-select-sm" style="width:auto;" id="chartPeriodSelect">
                        <option value="6" selected>Últimos 6 meses</option>
                        <option value="3">Últimos 3 meses</option>
                        <option value="12">Últimos 12 meses</option>
                    </select>
                </div>
                <canvas id="ingresosChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <!-- Resumen de pagos (donut) -->
    <div class="col-xl-3 col-md-6">
        <div class="card chart-card h-100">
            <div class="card-body">
                <div class="chart-title mb-3">Resumen de pagos</div>
                <div class="d-flex flex-column align-items-center">
                    <div style="position:relative; width:160px; height:160px;">
                        <canvas id="donutChart" width="160" height="160"></canvas>
                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                            <div style="font-size:1.8rem;font-weight:800;"><?php echo $totalPagos; ?></div>
                            <div style="font-size:.75rem;color:var(--bs-secondary-color);font-weight:600;">Total</div>
                        </div>
                    </div>
                    <div class="w-100 mt-3">
                        <div class="donut-legend-row">
                            <div class="d-flex align-items-center gap-2"><span class="donut-legend-dot" style="background:#22c55e;"></span> Pagados</div>
                            <div class="fw-semibold"><?php echo $pagadosMes; ?></div>
                        </div>
                        <div class="donut-legend-row">
                            <div class="d-flex align-items-center gap-2"><span class="donut-legend-dot" style="background:#eab308;"></span> Pendientes</div>
                            <div class="fw-semibold"><?php echo $pendientesMes; ?></div>
                        </div>
                        <div class="donut-legend-row">
                            <div class="d-flex align-items-center gap-2"><span class="donut-legend-dot" style="background:#ef4444;"></span> Vencidos</div>
                            <div class="fw-semibold"><?php echo $vencidosMes; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Próximos vencimientos -->
    <div class="col-xl-3 col-md-6">
        <div class="card venc-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="chart-title"><i class="fas fa-calendar-exclamation me-2 text-warning"></i>Próximos vencimientos</div>
                </div>
                <?php if (empty($proximosVenc)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-check fa-2x mb-2"></i>
                        <div>Sin vencimientos próximos</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($proximosVenc as $v):
                        $vFecha = !empty($v['fecha_vencimiento']) ? date('d/m/Y', strtotime($v['fecha_vencimiento'])) : '—';
                        $diasRestantes = !empty($v['fecha_vencimiento']) ? (int)ceil((strtotime($v['fecha_vencimiento']) - time()) / 86400) : 0;
                        $urgente = $diasRestantes <= 3;
                    ?>
                    <div class="venc-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold" style="font-size:.87rem;"><?php echo htmlspecialchars($v['cliente_nombre'] ?? $v['nombre'] ?? '—'); ?></div>
                                <div class="text-muted" style="font-size:.75rem;">Factura: <?php echo htmlspecialchars($v['numero_factura'] ?? '—'); ?></div>
                            </div>
                            <div class="text-end">
                                <div style="font-size:.8rem; font-weight:700; color:<?php echo $urgente ? '#ef4444' : '#eab308'; ?>;"><?php echo $vFecha; ?></div>
                                <span class="estado-badge bg-warning-subtle text-warning mt-1">
                                    <span class="dot" style="background:#eab308;"></span>Pendiente
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="mt-3 text-center">
                        <a href="<?php echo url('pagos?estado=pendiente'); ?>" class="text-primary" style="font-size:.82rem; font-weight:600; text-decoration:none;">
                            Ver todos los próximos <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ─── Filtros ────────────────────────────────────────────────────────── -->
<div class="card filter-card mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                    <input id="pagosSearch" type="search" class="form-control" placeholder="Buscar por cliente, factura..." autocomplete="off">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="form-label small text-muted mb-1" for="pagosEstado">Estado</label>
                <select id="pagosEstado" class="form-select">
                    <option value="">Todos</option>
                    <option value="pagado">Pagado</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="vencido">Vencido</option>
                </select>
            </div>

            <div class="col-lg-3">
                <label class="form-label small text-muted mb-1" for="pagosMetodo">Método</label>
                <select id="pagosMetodo" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($metodoPagoOptions as $m): ?>
                        <option value="<?php echo htmlspecialchars($m); ?>"><?php echo htmlspecialchars(ucfirst($m)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-3">
                <label class="form-label small text-muted mb-1">Fecha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                    <input id="pagosFechaDesde" type="date" class="form-control" placeholder="Desde">
                    <input id="pagosFechaHasta" type="date" class="form-control" placeholder="Hasta">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small" id="pagosCount">&nbsp;</div>
            <div class="view-toggle d-flex gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="pagosGridBtn" aria-label="Vista en cuadrícula" title="Vista en cuadrícula">
                    <i class="fas fa-grip"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm active" id="pagosListBtn" aria-label="Vista en lista" title="Vista en lista">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ─── Tabla / Grid de pagos ─────────────────────────────────────────── -->
<?php if (empty($pagos)): ?>
    <div class="card" style="border-radius:1rem;border:none;box-shadow:0 1px 4px rgba(0,0,0,.07);">
        <div class="card-body text-center py-5">
            <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
            <h3>No hay pagos registrados</h3>
            <p class="text-muted">Comienza registrando el primer pago</p>
            <a href="<?php echo url('pagos/create'); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Registrar Pago
            </a>
        </div>
    </div>
<?php else: ?>

    <!-- ── TABLE VIEW (default) ── -->
    <div id="pagosTableView">
        <div class="card pagos-table-card">
            <div class="card-body p-0">
                <div class="px-4 py-3 border-bottom">
                    <div class="fw-700" style="font-size:1rem; font-weight:700;">Historial de Pagos</div>
                </div>
                <div class="table-responsive">
                    <table class="table pagos-table mb-0" id="pagosTable">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Factura</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Fecha Pago</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $idx => $pago):
                                $estado = (string)($pago['estado'] ?? '');
                                $estadoBadgeClass = 'bg-secondary-subtle text-secondary';
                                $estadoDot = '#6b7280';
                                $estadoLabel = ucfirst($estado);
                                $estadoIcon  = 'fa-circle';
                                if ($estado === 'pagado')    { $estadoBadgeClass = 'bg-success-subtle text-success'; $estadoDot = '#22c55e'; $estadoIcon = 'fa-circle-check'; }
                                if ($estado === 'pendiente') { $estadoBadgeClass = 'bg-warning-subtle text-warning'; $estadoDot = '#eab308'; $estadoIcon = 'fa-clock'; }
                                if ($estado === 'vencido')   { $estadoBadgeClass = 'bg-danger-subtle text-danger';   $estadoDot = '#ef4444'; $estadoIcon = 'fa-triangle-exclamation'; }

                                $nombreCliente = (string)($pago['cliente_nombre'] ?? '—');
                                $initials = avatarInitials($nombreCliente);
                                $palette  = $avatarPalette[$idx % count($avatarPalette)];

                                $metodo   = (string)($pago['metodo_pago'] ?? '');
                                $metodIcon = $metodosIconos[$metodo] ?? 'fa-money-bill-wave';

                                $fechaPago = !empty($pago['fecha_pago']) ? date('d/m/Y', strtotime($pago['fecha_pago'])) : '—';
                                $fechaVenc = !empty($pago['fecha_vencimiento']) ? date('d/m/Y', strtotime($pago['fecha_vencimiento'])) : '—';
                                $pagoId    = (int)($pago['id'] ?? 0);
                                $monto     = (float)($pago['monto'] ?? 0);
                                $factura   = (string)($pago['numero_factura'] ?? '—');

                                $clienteId = (int)($pago['cliente_id'] ?? 0);
                                $clienteRef = $clienteId > 0 ? htmlspecialchars(mb_strtoupper(mb_substr(explode(' ', $nombreCliente)[0], 0, 2, 'UTF-8'), 'UTF-8')) : $initials;
                            ?>
                            <tr
                                data-pago-row
                                data-nombre="<?php echo htmlspecialchars(mb_strtolower($nombreCliente, 'UTF-8')); ?>"
                                data-factura="<?php echo htmlspecialchars(mb_strtolower($factura, 'UTF-8')); ?>"
                                data-estado="<?php echo htmlspecialchars($estado); ?>"
                                data-metodo="<?php echo htmlspecialchars($metodo); ?>"
                                data-fecha-pago="<?php echo htmlspecialchars((string)($pago['fecha_pago'] ?? '')); ?>"
                            >
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="client-avatar-sm <?php echo $palette['bg']; ?> <?php echo $palette['text']; ?>">
                                            <?php echo htmlspecialchars($initials); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:.87rem;"><?php echo htmlspecialchars($nombreCliente); ?></div>
                                            <?php if (!empty($pago['cliente_referencia'])): ?>
                                            <div class="text-muted" style="font-size:.72rem;"><?php echo htmlspecialchars($pago['cliente_referencia']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="factura-code"><?php echo htmlspecialchars($factura); ?></span></td>
                                <td><span class="monto-value">$<?php echo number_format($monto); ?></span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2 text-muted">
                                        <i class="fas <?php echo $metodIcon; ?>"></i>
                                        <span style="font-size:.85rem;"><?php echo htmlspecialchars(ucfirst($metodo)); ?></span>
                                    </div>
                                </td>
                                <td style="font-size:.85rem;"><?php echo $fechaPago; ?></td>
                                <td style="font-size:.85rem;"><?php echo $fechaVenc; ?></td>
                                <td>
                                    <span class="estado-badge <?php echo $estadoBadgeClass; ?>">
                                        <span class="dot" style="background:<?php echo $estadoDot; ?>;"></span>
                                        <i class="fas <?php echo $estadoIcon; ?>" style="font-size:.65rem;"></i>
                                        <?php echo htmlspecialchars($estadoLabel); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="<?php echo url('pagos/' . $pagoId); ?>" class="btn btn-outline-primary action-btn" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo url('pagos/' . $pagoId . '/print'); ?>" class="btn btn-outline-success action-btn" title="Imprimir factura" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="<?php echo url('pagos/' . $pagoId . '/print') . '?type=ticket'; ?>" class="btn btn-outline-secondary action-btn" title="Imprimir ticket" target="_blank">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Más acciones">
                                                <i class="fas fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li><a class="dropdown-item" href="<?php echo url('pagos/' . $pagoId); ?>"><i class="fas fa-eye me-2"></i>Ver detalles</a></li>
                                                <li><a class="dropdown-item" href="<?php echo url('pagos/' . $pagoId . '/print'); ?>" target="_blank"><i class="fas fa-print me-2"></i>Imprimir factura</a></li>
                                                <li><a class="dropdown-item" href="<?php echo url('pagos/' . $pagoId . '/print') . '?type=ticket'; ?>" target="_blank"><i class="fas fa-receipt me-2"></i>Imprimir ticket</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── GRID VIEW ── -->
    <div id="pagosGridView" style="display:none;">
        <div class="row" id="pagosGridContainer">
            <?php foreach ($pagos as $idx => $pago):
                $estado = (string)($pago['estado'] ?? '');
                $estadoBadgeClass = 'bg-secondary-subtle text-secondary';
                $estadoDot = '#6b7280';
                $estadoLabel = ucfirst($estado);
                if ($estado === 'pagado')    { $estadoBadgeClass = 'bg-success-subtle text-success'; $estadoDot = '#22c55e'; }
                if ($estado === 'pendiente') { $estadoBadgeClass = 'bg-warning-subtle text-warning'; $estadoDot = '#eab308'; }
                if ($estado === 'vencido')   { $estadoBadgeClass = 'bg-danger-subtle text-danger';   $estadoDot = '#ef4444'; }

                $nombreCliente = (string)($pago['cliente_nombre'] ?? '—');
                $initials = avatarInitials($nombreCliente);
                $palette  = $avatarPalette[$idx % count($avatarPalette)];
                $metodo   = (string)($pago['metodo_pago'] ?? '');
                $metodIcon = $metodosIconos[$metodo] ?? 'fa-money-bill-wave';
                $fechaPago = !empty($pago['fecha_pago']) ? date('d/m/Y', strtotime($pago['fecha_pago'])) : '—';
                $fechaVenc = !empty($pago['fecha_vencimiento']) ? date('d/m/Y', strtotime($pago['fecha_vencimiento'])) : '—';
                $pagoId    = (int)($pago['id'] ?? 0);
                $monto     = (float)($pago['monto'] ?? 0);
                $factura   = (string)($pago['numero_factura'] ?? '—');
            ?>
            <div class="col-lg-4 col-md-6 mb-4 pago-grid-item"
                data-pago-grid
                data-nombre="<?php echo htmlspecialchars(mb_strtolower($nombreCliente, 'UTF-8')); ?>"
                data-factura="<?php echo htmlspecialchars(mb_strtolower($factura, 'UTF-8')); ?>"
                data-estado="<?php echo htmlspecialchars($estado); ?>"
                data-metodo="<?php echo htmlspecialchars($metodo); ?>"
                data-fecha-pago="<?php echo htmlspecialchars((string)($pago['fecha_pago'] ?? '')); ?>"
            >
                <div class="card pago-grid-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="client-avatar-sm <?php echo $palette['bg']; ?> <?php echo $palette['text']; ?>" style="width:44px;height:44px;border-radius:12px;font-size:.9rem;">
                                    <?php echo htmlspecialchars($initials); ?>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($nombreCliente); ?></div>
                                    <span class="factura-code"><?php echo htmlspecialchars($factura); ?></span>
                                </div>
                            </div>
                            <span class="estado-badge <?php echo $estadoBadgeClass; ?>">
                                <span class="dot" style="background:<?php echo $estadoDot; ?>;"></span>
                                <?php echo htmlspecialchars($estadoLabel); ?>
                            </span>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="info-label" style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Monto</div>
                                <div class="monto-value">$<?php echo number_format($monto); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="info-label" style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Método</div>
                                <div style="font-size:.85rem;"><i class="fas <?php echo $metodIcon; ?> me-1 text-muted"></i><?php echo htmlspecialchars(ucfirst($metodo)); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="info-label" style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Fecha pago</div>
                                <div style="font-size:.83rem;"><?php echo $fechaPago; ?></div>
                            </div>
                            <div class="col-6">
                                <div class="info-label" style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Vencimiento</div>
                                <div style="font-size:.83rem;"><?php echo $fechaVenc; ?></div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-2 border-top">
                            <a href="<?php echo url('pagos/' . $pagoId); ?>" class="btn btn-outline-primary btn-sm" style="border-radius:8px;font-size:.78rem;"><i class="fas fa-eye me-1"></i>Ver</a>
                            <a href="<?php echo url('pagos/' . $pagoId . '/print'); ?>" class="btn btn-outline-success btn-sm" style="border-radius:8px;font-size:.78rem;" target="_blank"><i class="fas fa-print me-1"></i>Factura</a>
                            <a href="<?php echo url('pagos/' . $pagoId . '/print') . '?type=ticket'; ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;font-size:.78rem;" target="_blank"><i class="fas fa-receipt me-1"></i>Ticket</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Paginación ── -->
    <div class="card pagination-card mt-2">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="text-muted small" id="pagosRange">Mostrando 0 a 0 de 0 pagos</div>
            <nav aria-label="Paginación de pagos">
                <ul class="pagination pagination-sm mb-0" id="pagosPagination"></ul>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <div class="text-muted small">Mostrar</div>
                <select id="pagosPageSize" class="form-select form-select-sm" style="width:84px;">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <div class="text-muted small">por página</div>
            </div>
        </div>
    </div>

<?php endif; ?>

<!-- ─── Chart.js ──────────────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    /* ── Ingresos line chart ── */
    const labels   = <?php echo $chartLabels; ?>;
    const data     = <?php echo $chartData; ?>;
    const maxVal   = Math.max(...data, 1);

    const ctxLine  = document.getElementById('ingresosChart');
    if (ctxLine) {
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Ingresos',
                    data,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,.08)',
                    fill: true,
                    tension: 0.45,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    pointHoverRadius: 6,
                    borderWidth: 2.5,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' $' + ctx.parsed.y.toLocaleString()
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11 } } },
                    y: {
                        grid: { color: 'rgba(0,0,0,.05)' },
                        border: { display: false },
                        ticks: { callback: v => '$' + (v >= 1000 ? (v/1000)+'K' : v), font: { size: 11 } },
                        min: 0,
                    }
                }
            }
        });
    }

    /* ── Donut chart ── */
    const ctxDonut = document.getElementById('donutChart');
    const pagados   = <?php echo $pagadosMes; ?>;
    const pendientes= <?php echo $pendientesMes; ?>;
    const vencidos  = <?php echo $vencidosMes; ?>;
    if (ctxDonut) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Pagados', 'Pendientes', 'Vencidos'],
                datasets: [{
                    data: [pagados || 0, pendientes || 0, vencidos || 0],
                    backgroundColor: ['#22c55e', '#eab308', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                cutout: '70%',
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed } } },
                responsive: false,
            }
        });
    }
})();

/* ── Filter + pagination ── */
(function initPagosList() {
    const searchInput    = document.getElementById('pagosSearch');
    const estadoSelect   = document.getElementById('pagosEstado');
    const metodoSelect   = document.getElementById('pagosMetodo');
    const fechaDesde     = document.getElementById('pagosFechaDesde');
    const fechaHasta     = document.getElementById('pagosFechaHasta');
    const countEl        = document.getElementById('pagosCount');
    const rangeEl        = document.getElementById('pagosRange');
    const paginationEl   = document.getElementById('pagosPagination');
    const pageSizeSelect = document.getElementById('pagosPageSize');
    const gridBtn        = document.getElementById('pagosGridBtn');
    const listBtn        = document.getElementById('pagosListBtn');
    const tableView      = document.getElementById('pagosTableView');
    const gridView       = document.getElementById('pagosGridView');

    const rows      = Array.from(document.querySelectorAll('[data-pago-row]'));
    const gridItems = Array.from(document.querySelectorAll('[data-pago-grid]'));

    let page      = 1;
    let pageSize  = 10;
    let isGrid    = false;

    function normalize(v) { return (v || '').toString().trim().toLowerCase(); }

    function matchesItem(dataset) {
        const q      = normalize(searchInput ? searchInput.value : '');
        const estado = (estadoSelect ? estadoSelect.value : '').trim();
        const metodo = (metodoSelect ? metodoSelect.value : '').trim();
        const desde  = fechaDesde ? fechaDesde.value : '';
        const hasta  = fechaHasta ? fechaHasta.value : '';

        if (q && !dataset.nombre.includes(q) && !dataset.factura.includes(q)) return false;
        if (estado && dataset.estado !== estado) return false;
        if (metodo && dataset.metodo !== metodo) return false;
        if (desde  && dataset.fechaPago && dataset.fechaPago < desde) return false;
        if (hasta  && dataset.fechaPago && dataset.fechaPago > hasta) return false;
        return true;
    }

    function setView(grid) {
        isGrid = grid;
        if (gridBtn) gridBtn.classList.toggle('active', grid);
        if (listBtn) listBtn.classList.toggle('active', !grid);
        if (tableView) tableView.style.display = grid ? 'none' : '';
        if (gridView)  gridView.style.display  = grid ? ''     : 'none';
    }

    function renderPagination(total, totalPages) {
        if (!paginationEl) return;
        paginationEl.innerHTML = '';
        if (totalPages <= 1) return;

        function addPage(label, targetPage, disabled) {
            const li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (targetPage === page ? ' active' : '');
            const a = document.createElement('a');
            a.className = 'page-link'; a.href = '#'; a.innerHTML = label;
            a.addEventListener('click', (e) => {
                e.preventDefault(); if (disabled) return;
                page = targetPage; update();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            li.appendChild(a); paginationEl.appendChild(li);
        }

        addPage('&laquo;', 1, page === 1);
        addPage('&lsaquo;', Math.max(1, page - 1), page === 1);
        for (let p = Math.max(1, page - 3); p <= Math.min(totalPages, page + 3); p++) addPage(String(p), p, false);
        addPage('&rsaquo;', Math.min(totalPages, page + 1), page === totalPages);
        addPage('&raquo;', totalPages, page === totalPages);
    }

    function update() {
        pageSize = parseInt(pageSizeSelect ? pageSizeSelect.value : '10', 10);

        const activeItems = isGrid ? gridItems : rows;
        const matched = activeItems.filter(el => matchesItem(el.dataset));
        const total      = matched.length;
        const totalPages = Math.max(1, Math.ceil(total / pageSize));
        if (page > totalPages) page = totalPages;

        const start = (page - 1) * pageSize;
        const end   = Math.min(total, start + pageSize);

        activeItems.forEach(el => { el.style.display = 'none'; });
        matched.slice(start, end).forEach(el => { el.style.display = ''; });

        if (countEl) countEl.textContent = total + ' pago' + (total === 1 ? '' : 's') + ' encontrado' + (total === 1 ? '' : 's');
        if (rangeEl) rangeEl.textContent = 'Mostrando ' + (total ? (start + 1) : 0) + ' a ' + (total ? end : 0) + ' de ' + total + ' pagos';

        renderPagination(total, totalPages);
    }

    const debounced = (() => {
        let t = null;
        return (fn) => { if (t) clearTimeout(t); t = setTimeout(fn, 140); };
    })();

    if (searchInput)    searchInput.addEventListener('input', () => debounced(update));
    if (estadoSelect)   estadoSelect.addEventListener('change', () => { page = 1; update(); });
    if (metodoSelect)   metodoSelect.addEventListener('change', () => { page = 1; update(); });
    if (fechaDesde)     fechaDesde.addEventListener('change', () => { page = 1; update(); });
    if (fechaHasta)     fechaHasta.addEventListener('change', () => { page = 1; update(); });
    if (pageSizeSelect) pageSizeSelect.addEventListener('change', () => { page = 1; update(); });

    if (gridBtn) gridBtn.addEventListener('click', () => { setView(true);  page = 1; update(); });
    if (listBtn) listBtn.addEventListener('click', () => { setView(false); page = 1; update(); });

    setView(false);
    update();
})();

/* Mantiene los KPIs sincronizados si otra acción actualiza pagos o suscripciones. */
(function syncPaymentKpis() {
    const endpoint = '<?php echo url('pagos/kpis'); ?>';
    const money = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    async function refreshKpis() {
        try {
            const response = await fetch(endpoint, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            const payload = await response.json();
            if (!payload.success) return;
            const data = payload.data;
            const values = {
                'kpi-total': data.total_pagos_mes,
                'kpi-pagados': data.pagos_realizados_mes,
                'kpi-pendientes': data.pagos_pendientes,
                'kpi-vencidos': data.clientes_vencidos,
            };
            Object.entries(values).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) element.textContent = Number(value || 0);
            });
            const ingresos = document.getElementById('kpi-ingresos');
            if (ingresos) ingresos.textContent = '$' + money.format(Number(data.ingresos_mes || 0));
        } catch (_) {
            // Se conserva el último valor confirmado si la conexión se interrumpe.
        }
    }
    document.addEventListener('pago:actualizado', refreshKpis);
    window.refreshPaymentKpis = refreshKpis;
    window.setInterval(refreshKpis, 30000);
})();
</script>

<?php include 'views/layouts/footer.php'; ?>
