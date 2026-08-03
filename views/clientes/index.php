<?php include 'views/layouts/header.php'; ?>
<style>.client-card.client-card-vencido{border:2px solid #dc3545;background:#fff7f7}.billing-alert{border-radius:.5rem}</style>

<?php
    $clientes = $clientes ?? [];
    $activos = array_values(array_filter($clientes, function($c) { return ($c['estado'] ?? '') === 'activo'; }));
    $suspendidos = array_values(array_filter($clientes, function($c) { return ($c['estado'] ?? '') === 'suspendido'; }));
    $pendientes = array_values(array_filter($clientes, function($c) { return ($c['estado'] ?? '') === 'pendiente'; }));
    $totalClientes = count($clientes);
    $ingresosPotenciales = array_sum(array_map(function($c) { return (float)($c['plan_mensual'] ?? 0); }, $clientes));

    $tipoConexionOptions = [];
    foreach ($clientes as $c) {
        $tipo = (string)($c['tipo_conexion'] ?? '');
        if ($tipo !== '') {
            $tipoConexionOptions[$tipo] = true;
        }
    }
    $tipoConexionOptions = array_keys($tipoConexionOptions);
    sort($tipoConexionOptions);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
    <div>
        <h1 class="h2 mb-1 page-title">
            <i class="fas fa-users me-2"></i>
            Gestión de Clientes
        </h1>
        <div class="text-muted page-subtitle">Administra tus clientes y su información de conexión</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo url('clientes/create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Nuevo Cliente
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

<!-- Estadísticas rápidas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Clientes Activos</div>
                        <div class="stat-value"><?php echo count($activos); ?></div>
                        <div class="stat-meta"><?php echo $totalClientes > 0 ? round((count($activos) / $totalClientes) * 100) : 0; ?>% del total</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-user-check"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 28 C 14 30, 26 16, 40 18 S 60 26, 74 16 S 98 20, 118 12" fill="none" stroke="rgba(34,197,94,.95)" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Suspendidos</div>
                        <div class="stat-value"><?php echo count($suspendidos); ?></div>
                        <div class="stat-meta"><?php echo $totalClientes > 0 ? round((count($suspendidos) / $totalClientes) * 100) : 0; ?>% del total</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-danger-subtle text-danger"><i class="fas fa-user-xmark"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 26 C 16 30, 26 18, 38 22 S 58 34, 72 18 S 94 10, 118 14" fill="none" stroke="rgba(239,68,68,.9)" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Pendientes</div>
                        <div class="stat-value"><?php echo count($pendientes); ?></div>
                        <div class="stat-meta"><?php echo $totalClientes > 0 ? round((count($pendientes) / $totalClientes) * 100) : 0; ?>% del total</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-clock"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 28 C 16 18, 28 30, 42 24 S 64 16, 78 22 S 98 34, 118 18" fill="none" stroke="rgba(234,88,12,.95)" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Ingresos Potenciales</div>
                        <div class="stat-value">$<?php echo number_format($ingresosPotenciales); ?></div>
                        <div class="stat-meta">Este mes</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-dollar-sign"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 30 C 20 26, 22 18, 36 20 S 56 28, 70 18 S 92 8, 118 12" fill="none" stroke="rgba(59,130,246,.9)" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card filter-card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-lg-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                    <input id="clientesSearch" type="search" class="form-control" placeholder="Buscar cliente, teléfono, email..." autocomplete="off">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="form-label small text-muted mb-1" for="clientesEstado">Estado</label>
                <select id="clientesEstado" class="form-select">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="suspendido">Suspendido</option>
                    <option value="pendiente">Pendiente</option>
                </select>
            </div>

            <div class="col-lg-3">
                <label class="form-label small text-muted mb-1" for="clientesTipo">Tipo de conexión</label>
                <select id="clientesTipo" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($tipoConexionOptions as $tipo): ?>
                        <option value="<?php echo htmlspecialchars($tipo); ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $tipo))); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label small text-muted mb-1" for="clientesPlan">Plan</label>
                <select id="clientesPlan" class="form-select">
                    <option value="">Todos</option>
                    <option value="0-499">Hasta $499</option>
                    <option value="500-999">$500 - $999</option>
                    <option value="1000-999999">$1,000+</option>
                </select>
            </div>

            <div class="col-lg-1 d-flex align-items-end justify-content-lg-end gap-2">
                <button type="button" class="btn btn-outline-secondary w-100" disabled>
                    <i class="fas fa-sliders me-1"></i>
                    Más
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small" id="clientesCount">&nbsp;</div>

            <div class="view-toggle">
                <button type="button" class="btn btn-outline-secondary btn-sm active" id="clientesGridBtn" aria-label="Vista en cuadrícula" title="Vista en cuadrícula">
                    <i class="fas fa-grip"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="clientesListBtn" aria-label="Vista en lista" title="Vista en lista">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lista de clientes -->
<?php if (empty($clientes)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-4x text-muted mb-3"></i>
            <h3>No hay clientes registrados</h3>
            <p class="text-muted">Comienza agregando tu primer cliente</p>
            <a href="<?php echo url('clientes/create'); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Agregar Cliente
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row" id="clientesGrid">
        <?php foreach ($clientes as $cliente): ?>
            <?php
                $estado = (string)($cliente['estado'] ?? '');
                $estadoLabel = $estado !== '' ? ucfirst($estado) : '—';
                $estadoClass = 'bg-secondary-subtle text-secondary';
                if ($estado === 'activo') $estadoClass = 'bg-success-subtle text-success';
                if ($estado === 'suspendido') $estadoClass = 'bg-danger-subtle text-danger';
                if ($estado === 'pendiente') $estadoClass = 'bg-warning-subtle text-warning';

                $nombre = (string)($cliente['nombre'] ?? '');
                $avatarText = strtoupper(mb_substr(trim($nombre), 0, 2, 'UTF-8'));
                if ($avatarText === '') $avatarText = 'CL';

                $tipoConexion = (string)($cliente['tipo_conexion'] ?? '');
                $tipoConexionLabel = $tipoConexion !== '' ? ucwords(str_replace('_', ' ', $tipoConexion)) : '—';
                $estadoSuscripcion = (string)($cliente['estado_calculado'] ?? 'sinpagos');
                $esVencido = $estadoSuscripcion === 'vencido';
                $planMensual = (float)($cliente['plan_mensual'] ?? 0);
                $megasContratados = (int)($cliente['megas_contratados'] ?? 0);
            ?>

            <div
                class="col-lg-6 col-xl-6 mb-4 client-item"
                data-client-card
                data-name="<?php echo htmlspecialchars(mb_strtolower($nombre, 'UTF-8')); ?>"
                data-phone="<?php echo htmlspecialchars(mb_strtolower((string)($cliente['telefono'] ?? ''), 'UTF-8')); ?>"
                data-email="<?php echo htmlspecialchars(mb_strtolower((string)($cliente['email'] ?? ''), 'UTF-8')); ?>"
                data-address="<?php echo htmlspecialchars(mb_strtolower((string)($cliente['direccion'] ?? ''), 'UTF-8')); ?>"
                data-estado="<?php echo htmlspecialchars($estado); ?>"
                data-tipo="<?php echo htmlspecialchars($tipoConexion); ?>"
                data-plan="<?php echo htmlspecialchars((string)$planMensual); ?>"
            >
                <div class="card client-card h-100 <?php echo $esVencido ? 'client-card-vencido' : ''; ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="client-avatar bg-success-subtle text-success">
                                    <?php echo htmlspecialchars($avatarText); ?>
                                </div>

                                <div class="min-w-0">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="client-name text-truncate"><?php echo htmlspecialchars($nombre); ?></div>
                                        <div class="client-id small text-muted">ID: <?php echo (int)($cliente['id'] ?? 0); ?></div>
                                    </div>
                                    <div class="client-status badge rounded-pill <?php echo $estadoClass; ?>">
                                        <?php echo htmlspecialchars($estadoLabel); ?>
                                    </div>
                                    <?php if ($esVencido): ?><span class="badge bg-danger ms-1"><i class="fas fa-triangle-exclamation me-1"></i>Vencido</span><?php endif; ?>
                                </div>
                            </div>

                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones">
                                    <i class="fas fa-ellipsis"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item" href="<?php echo url('clientes/' . ($cliente['id'] ?? '')); ?>"><i class="fas fa-eye me-2"></i>Ver detalles</a></li>
                                    <li><a class="dropdown-item" href="<?php echo url('clientes/' . ($cliente['id'] ?? '') . '/edit'); ?>"><i class="fas fa-pen me-2"></i>Editar</a></li>
                                    <li><a class="dropdown-item" href="<?php echo url('pagos?cliente=' . ($cliente['id'] ?? '')); ?>"><i class="fas fa-dollar-sign me-2"></i>Pagos</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="billing-alert mt-3 p-3 <?php echo $esVencido ? 'bg-danger-subtle text-danger' : ($estadoSuscripcion === 'porvencer' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-light'); ?>">
                            <div class="row g-2 small">
                                <div class="col-6"><strong>Último pago:</strong><br><?php echo !empty($cliente['fecha_ultimo_pago']) ? date('d/m/Y', strtotime($cliente['fecha_ultimo_pago'])) : 'Sin pagos'; ?></div>
                                <div class="col-6"><strong>Próximo pago:</strong><br><?php echo !empty($cliente['proxima_fecha_pago']) ? date('d/m/Y', strtotime($cliente['proxima_fecha_pago'])) : '—'; ?></div>
                                <div class="col-6"><strong>Fecha de corte:</strong><br><?php echo !empty($cliente['fecha_corte']) ? date('d/m/Y', strtotime($cliente['fecha_corte'])) : '—'; ?></div>
                                <div class="col-6"><strong><?php echo $esVencido ? 'Venció hace:' : 'Restan:'; ?></strong><br><?php echo $esVencido ? (int)($cliente['dias_vencido'] ?? 0) . ' días' : (($cliente['dias_para_pago'] ?? null) !== null ? max(0, (int)$cliente['dias_para_pago']) . ' días' : '—'); ?></div>
                            </div>
                        </div>

                        <div class="row g-3 client-info">
                            <div class="col-md-6">
                                <div class="info-label">Tipo de conexión</div>
                                <div class="info-value"><i class="fas fa-wifi me-2"></i><?php echo htmlspecialchars($tipoConexionLabel); ?></div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Megas contratados</div>
                                <div class="info-value"><i class="fas fa-tachometer-alt me-2"></i><?php echo $megasContratados > 0 ? htmlspecialchars((string)$megasContratados) . ' Mbps' : '-'; ?></div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Teléfono</div>
                                <div class="info-value"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars((string)($cliente['telefono'] ?? '—')); ?></div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Dirección</div>
                                <div class="info-value text-truncate"><i class="fas fa-location-dot me-2"></i><?php echo htmlspecialchars((string)($cliente['direccion'] ?? '—')); ?></div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Email</div>
                                <div class="info-value text-truncate"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars((string)($cliente['email'] ?? '—')); ?></div>
                            </div>
                        </div>

                        <div class="client-footer mt-3 pt-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-label">Plan mensual</div>
                                    <div class="plan-value text-success">$<?php echo number_format($planMensual); ?></div>
                                    <div class="text-muted small">Plan</div>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="info-label">Cliente desde</div>
                                    <div class="fw-semibold"><i class="fas fa-calendar me-2"></i><?php echo !empty($cliente['fecha_contratacion']) ? htmlspecialchars(date('d/m/Y', strtotime((string)$cliente['fecha_contratacion']))) : '—'; ?></div>
                                    <div class="text-muted small">Registro</div>
                                </div>
                            </div>
                        </div>

                        <div class="client-actions mt-3">
                            <a href="<?php echo url('clientes/' . ($cliente['id'] ?? '')); ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-2"></i>
                                Ver detalles
                            </a>
                            <a href="<?php echo url('clientes/' . ($cliente['id'] ?? '') . '/edit'); ?>" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-pen me-2"></i>
                                Editar
                            </a>
                            <a href="<?php echo url('pagos?cliente=' . ($cliente['id'] ?? '')); ?>" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-dollar-sign me-2"></i>
                                Pagos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card pagination-card mt-2">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="text-muted small" id="clientesRange">Mostrando 0 a 0 de 0 clientes</div>
            <nav aria-label="Paginación de clientes">
                <ul class="pagination pagination-sm mb-0" id="clientesPagination"></ul>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <div class="text-muted small">Mostrar</div>
                <select id="clientesPageSize" class="form-select form-select-sm" style="width: 84px;">
                    <option value="6">6</option>
                    <option value="10" selected>10</option>
                    <option value="20">20</option>
                </select>
                <div class="text-muted small">por página</div>
            </div>
        </div>
    </div>

    <script>
        (function initClientesList() {
            const searchInput = document.getElementById('clientesSearch');
            const estadoSelect = document.getElementById('clientesEstado');
            const tipoSelect = document.getElementById('clientesTipo');
            const planSelect = document.getElementById('clientesPlan');
            const countEl = document.getElementById('clientesCount');
            const rangeEl = document.getElementById('clientesRange');
            const paginationEl = document.getElementById('clientesPagination');
            const pageSizeSelect = document.getElementById('clientesPageSize');
            const gridBtn = document.getElementById('clientesGridBtn');
            const listBtn = document.getElementById('clientesListBtn');

            const cards = Array.from(document.querySelectorAll('[data-client-card]'));

            let page = 1;
            let pageSize = parseInt(pageSizeSelect ? pageSizeSelect.value : '10', 10);
            let currentView = 'grid';

            function normalize(v) {
                return (v || '').toString().trim().toLowerCase();
            }

            function parsePlanRange(value) {
                if (!value) return null;
                const parts = value.split('-');
                if (parts.length !== 2) return null;
                const min = parseFloat(parts[0]);
                const max = parseFloat(parts[1]);
                if (Number.isNaN(min) || Number.isNaN(max)) return null;
                return { min, max };
            }

            function matches(card) {
                const q = normalize(searchInput ? searchInput.value : '');
                const estado = (estadoSelect ? estadoSelect.value : '').trim();
                const tipo = (tipoSelect ? tipoSelect.value : '').trim();
                const planRange = parsePlanRange(planSelect ? planSelect.value : '');

                if (q) {
                    const haystack = [
                        card.dataset.name,
                        card.dataset.phone,
                        card.dataset.email,
                        card.dataset.address,
                    ].join(' ');
                    if (!haystack.includes(q)) return false;
                }

                if (estado && card.dataset.estado !== estado) return false;
                if (tipo && card.dataset.tipo !== tipo) return false;

                if (planRange) {
                    const plan = parseFloat(card.dataset.plan || '0');
                    if (Number.isNaN(plan)) return false;
                    if (plan < planRange.min || plan > planRange.max) return false;
                }

                return true;
            }

            function setView(view) {
                currentView = view;
                if (gridBtn) gridBtn.classList.toggle('active', view === 'grid');
                if (listBtn) listBtn.classList.toggle('active', view === 'list');

                cards.forEach((c) => {
                    const wrapper = c;
                    if (!wrapper.dataset.gridClasses) {
                        wrapper.dataset.gridClasses = wrapper.className;
                    }
                    if (view === 'list') {
                        wrapper.className = 'col-12 mb-3 client-item';
                    } else {
                        wrapper.className = wrapper.dataset.gridClasses;
                    }
                });
            }

            function renderPagination(total, totalPages) {
                if (!paginationEl) return;
                paginationEl.innerHTML = '';
                if (totalPages <= 1) return;

                function add(label, targetPage, disabled, ariaLabel) {
                    const li = document.createElement('li');
                    li.className = 'page-item' + (disabled ? ' disabled' : '') + (targetPage === page ? ' active' : '');
                    const a = document.createElement('a');
                    a.className = 'page-link';
                    a.href = '#';
                    a.innerHTML = label;
                    if (ariaLabel) a.setAttribute('aria-label', ariaLabel);
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (disabled) return;
                        page = targetPage;
                        update();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                    li.appendChild(a);
                    paginationEl.appendChild(li);
                }

                add('&laquo;', 1, page === 1, 'Primera');
                add('&lsaquo;', Math.max(1, page - 1), page === 1, 'Anterior');

                const windowSize = 3;
                const start = Math.max(1, page - windowSize);
                const end = Math.min(totalPages, page + windowSize);
                for (let p = start; p <= end; p++) {
                    add(String(p), p, false);
                }

                add('&rsaquo;', Math.min(totalPages, page + 1), page === totalPages, 'Siguiente');
                add('&raquo;', totalPages, page === totalPages, 'Última');
            }

            function update() {
                pageSize = parseInt(pageSizeSelect ? pageSizeSelect.value : '10', 10);

                const matched = cards.filter(matches);
                const total = matched.length;
                const totalPages = Math.max(1, Math.ceil(total / pageSize));
                if (page > totalPages) page = totalPages;

                const startIndex = (page - 1) * pageSize;
                const endIndex = Math.min(total, startIndex + pageSize);

                cards.forEach((c) => { c.style.display = 'none'; });
                matched.slice(startIndex, endIndex).forEach((c) => { c.style.display = ''; });

                if (countEl) {
                    countEl.textContent = total + ' cliente' + (total === 1 ? '' : 's') + ' encontrado' + (total === 1 ? '' : 's');
                }
                if (rangeEl) {
                    rangeEl.textContent = 'Mostrando ' + (total ? (startIndex + 1) : 0) + ' a ' + (total ? endIndex : 0) + ' de ' + total + ' clientes';
                }
                renderPagination(total, totalPages);
            }

            const debounced = (() => {
                let t = null;
                return (fn) => {
                    if (t) window.clearTimeout(t);
                    t = window.setTimeout(fn, 140);
                };
            })();

            if (searchInput) searchInput.addEventListener('input', () => debounced(update));
            if (estadoSelect) estadoSelect.addEventListener('change', () => { page = 1; update(); });
            if (tipoSelect) tipoSelect.addEventListener('change', () => { page = 1; update(); });
            if (planSelect) planSelect.addEventListener('change', () => { page = 1; update(); });
            if (pageSizeSelect) pageSizeSelect.addEventListener('change', () => { page = 1; update(); });

            if (gridBtn) gridBtn.addEventListener('click', () => { setView('grid'); update(); });
            if (listBtn) listBtn.addEventListener('click', () => { setView('list'); update(); });

            setView('grid');
            update();
        })();
    </script>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>
