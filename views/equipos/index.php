<?php include 'views/layouts/header.php'; ?>

<?php
    $equipos = $equipos ?? [];

    $totalEquipos    = count($equipos);
    $operativos      = array_values(array_filter($equipos, fn($e) => ($e['estado_tecnico'] ?? '') === 'operativo'));
    $necesitanRevision = array_values(array_filter($equipos, fn($e) => ($e['estado_tecnico'] ?? '') === 'necesita_revision'));
    $dañados         = array_values(array_filter($equipos, fn($e) => ($e['estado_tecnico'] ?? '') === 'dañado'));

    $tipoEquipoOptions = [];
    foreach ($equipos as $e) {
        $t = (string)($e['tipo_equipo'] ?? '');
        if ($t !== '') $tipoEquipoOptions[$t] = true;
    }
    $tipoEquipoOptions = array_keys($tipoEquipoOptions);
    sort($tipoEquipoOptions);

    $ubicacionOptions = [];
    foreach ($equipos as $e) {
        $u = (string)($e['ubicacion'] ?? '');
        if ($u !== '') $ubicacionOptions[$u] = true;
    }
    $ubicacionOptions = array_keys($ubicacionOptions);
    sort($ubicacionOptions);

    // Icon map por tipo de equipo
    $tipoIconMap = [
        'router'            => 'fa-wifi',
        'router fibra'      => 'fa-bolt',
        'switch'            => 'fa-network-wired',
        'modem'             => 'fa-server',
        'antena'            => 'fa-satellite-dish',
        'antena inalámbrica'=> 'fa-satellite-dish',
        'otro'              => 'fa-microchip',
    ];

    $tipoColorMap = [
        'router'            => ['bg' => 'bg-primary-subtle',   'text' => 'text-primary'],
        'router fibra'      => ['bg' => 'bg-info-subtle',      'text' => 'text-info'],
        'switch'            => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary'],
        'modem'             => ['bg' => 'bg-success-subtle',   'text' => 'text-success'],
        'antena'            => ['bg' => 'bg-purple-subtle',    'text' => 'text-purple'],
        'antena inalámbrica'=> ['bg' => 'bg-purple-subtle',    'text' => 'text-purple'],
        'otro'              => ['bg' => 'bg-warning-subtle',   'text' => 'text-warning'],
    ];
?>

<style>
/* ─── Equipos page overrides ───────────────────────────────────────────── */
.bg-purple-subtle  { background-color: rgba(139,92,246,.12) !important; }
.text-purple       { color: #7c3aed !important; }

/* Stat tiles – mirror clientes */
.stat-tile { border: none; border-radius: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.07); transition: box-shadow .2s; }
.stat-tile:hover { box-shadow: 0 4px 16px rgba(0,0,0,.10); }
.stat-label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--bs-secondary-color); margin-bottom: .25rem; }
.stat-value { font-size: 2rem; font-weight: 700; line-height: 1.1; }
.stat-meta  { font-size: .78rem; color: var(--bs-secondary-color); margin-top: .2rem; }
.stat-icon  { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.stat-side  { display: flex; flex-direction: column; align-items: flex-end; justify-content: space-between; gap: .5rem; }
.sparkline  { width: 90px; height: 32px; display: block; }

/* Filter card */
.filter-card { border: none; border-radius: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
.view-toggle .btn { border-radius: 8px; }
.view-toggle .btn.active { background: var(--bs-primary); color: #fff; border-color: var(--bs-primary); }

/* Equipment card */
.equipo-card { border: 1px solid rgba(0,0,0,.07); border-radius: 1rem; transition: box-shadow .2s, transform .15s; cursor: default; }
.equipo-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.10); transform: translateY(-2px); }

.equipo-avatar { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }

.equipo-tipo-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--bs-secondary-color); margin-bottom: .1rem; }
.equipo-name { font-size: 1.05rem; font-weight: 700; line-height: 1.2; }

.status-badge { font-size: .7rem; font-weight: 600; padding: .3em .75em; border-radius: 50px; display: inline-flex; align-items: center; gap: .3rem; }
.status-badge .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }

.info-label  { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--bs-secondary-color); margin-bottom: .15rem; }
.info-value  { font-size: .88rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.serie-code  { font-family: 'SFMono-Regular', Consolas, monospace; font-size: .8rem; color: var(--bs-danger); font-weight: 600; }

.equipo-footer { border-top: 1px solid rgba(0,0,0,.06); padding-top: .85rem; margin-top: .85rem; }
.equipo-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.equipo-actions .btn { font-size: .78rem; border-radius: 8px; }

/* Pagination card */
.pagination-card { border: none; border-radius: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.07); }

/* Page title */
.page-title    { font-weight: 700; font-size: 1.5rem; }
.page-subtitle { font-size: .85rem; }

/* List view */
.equipo-item.list-view .col-lg-4, .equipo-item.list-view .col-md-6 { width: 100% !important; max-width: 100%; flex: 0 0 100%; }
</style>

<!-- ─── Header ──────────────────────────────────────────────────────────── -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
    <div>
        <h1 class="h2 mb-1 page-title">
            <i class="fas fa-router me-2"></i>
            Equipos Técnicos
        </h1>
        <div class="text-muted page-subtitle">Monitorea y gestiona todos los equipos de tu red</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?php echo url('equipos/create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Registrar Equipo
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
<div class="row mb-4">
    <!-- Total -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Total Equipos</div>
                        <div class="stat-value"><?php echo $totalEquipos; ?></div>
                        <div class="stat-meta">Todos los equipos registrados</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-cube"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 30 C 20 26, 22 18, 36 20 S 56 28, 70 18 S 92 8, 118 12" fill="none" stroke="rgba(59,130,246,.9)" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operativos -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Operativos</div>
                        <div class="stat-value"><?php echo count($operativos); ?></div>
                        <div class="stat-meta">Equipos funcionando</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-circle-check"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 28 C 14 30, 26 16, 40 18 S 60 26, 74 16 S 98 20, 118 12" fill="none" stroke="rgba(34,197,94,.95)" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Necesitan Revisión -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Necesitan Revisión</div>
                        <div class="stat-value"><?php echo count($necesitanRevision); ?></div>
                        <div class="stat-meta">Requieren atención</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-triangle-exclamation"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 28 C 16 18, 28 30, 42 24 S 64 16, 78 22 S 98 34, 118 18" fill="none" stroke="rgba(234,179,8,.95)" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dañados -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Dañados</div>
                        <div class="stat-value"><?php echo count($dañados); ?></div>
                        <div class="stat-meta">Fuera de servicio</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-danger-subtle text-danger"><i class="fas fa-circle-xmark"></i></div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 26 C 16 30, 26 18, 38 22 S 58 34, 72 18 S 94 10, 118 14" fill="none" stroke="rgba(239,68,68,.9)" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ─── Filtros ────────────────────────────────────────────────────────── -->
<div class="card filter-card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                    <input id="equiposSearch" type="search" class="form-control" placeholder="Buscar equipo, marca, modelo, serie..." autocomplete="off">
                </div>
            </div>

            <div class="col-lg-3">
                <label class="form-label small text-muted mb-1" for="equiposTipo">Tipo</label>
                <select id="equiposTipo" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($tipoEquipoOptions as $t): ?>
                        <option value="<?php echo htmlspecialchars(mb_strtolower($t, 'UTF-8')); ?>"><?php echo htmlspecialchars(ucwords($t)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-3">
                <label class="form-label small text-muted mb-1" for="equiposEstado">Estado</label>
                <select id="equiposEstado" class="form-select">
                    <option value="">Todos</option>
                    <option value="operativo">Operativo</option>
                    <option value="necesita_revision">Necesita Revisión</option>
                    <option value="dañado">Dañado</option>
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label small text-muted mb-1" for="equiposUbicacion">Ubicación</label>
                <select id="equiposUbicacion" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($ubicacionOptions as $u): ?>
                        <option value="<?php echo htmlspecialchars(mb_strtolower($u, 'UTF-8')); ?>"><?php echo htmlspecialchars(ucwords($u)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small" id="equiposCount">&nbsp;</div>
            <div class="view-toggle d-flex gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm active" id="equiposGridBtn" aria-label="Vista en cuadrícula" title="Vista en cuadrícula">
                    <i class="fas fa-grip"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="equiposListBtn" aria-label="Vista en lista" title="Vista en lista">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ─── Lista de Equipos ───────────────────────────────────────────────── -->
<?php if (empty($equipos)): ?>
    <div class="card" style="border-radius:1rem; border:none; box-shadow:0 1px 4px rgba(0,0,0,.07);">
        <div class="card-body text-center py-5">
            <i class="fas fa-router fa-4x text-muted mb-3"></i>
            <h3>No hay equipos registrados</h3>
            <p class="text-muted">Comienza registrando el primer equipo de tu red</p>
            <a href="<?php echo url('equipos/create'); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Registrar Equipo
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row" id="equiposGrid">
        <?php foreach ($equipos as $equipo): ?>
            <?php
                $estadoTecnico = (string)($equipo['estado_tecnico'] ?? '');

                // Badge estado
                $estadoBadgeClass = 'bg-secondary-subtle text-secondary';
                $estadoDot        = '#6b7280';
                $estadoLabel      = ucwords(str_replace('_', ' ', $estadoTecnico));
                if ($estadoTecnico === 'operativo') {
                    $estadoBadgeClass = 'bg-success-subtle text-success';
                    $estadoDot = '#22c55e';
                } elseif ($estadoTecnico === 'necesita_revision') {
                    $estadoBadgeClass = 'bg-warning-subtle text-warning';
                    $estadoDot = '#eab308';
                } elseif ($estadoTecnico === 'dañado') {
                    $estadoBadgeClass = 'bg-danger-subtle text-danger';
                    $estadoDot = '#ef4444';
                }

                // Tipo
                $tipoRaw   = (string)($equipo['tipo_equipo'] ?? '');
                $tipoKey   = mb_strtolower(trim($tipoRaw), 'UTF-8');
                $tipoLabel = ucwords($tipoRaw);
                $iconClass = $tipoIconMap[$tipoKey] ?? 'fa-microchip';
                $colors    = $tipoColorMap[$tipoKey] ?? ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary'];

                // Campos
                $clienteNombre  = (string)($equipo['cliente_nombre'] ?? '—');
                $marca          = (string)($equipo['marca'] ?? '—');
                $modelo         = (string)($equipo['modelo'] ?? '—');
                $numeroSerie    = (string)($equipo['numero_serie'] ?? '—');
                $fechaInst      = !empty($equipo['fecha_instalacion'])
                    ? date('d/m/Y', strtotime((string)$equipo['fecha_instalacion']))
                    : '—';
                $obs            = (string)($equipo['observaciones_tecnico'] ?? '');
                $ubicacion      = (string)($equipo['ubicacion'] ?? '');
                $equipoId       = (int)($equipo['id'] ?? 0);
            ?>
            <div
                class="col-lg-4 col-md-6 mb-4 equipo-item"
                data-equipo-card
                data-name="<?php echo htmlspecialchars(mb_strtolower($clienteNombre . ' ' . $marca . ' ' . $modelo . ' ' . $numeroSerie, 'UTF-8')); ?>"
                data-tipo="<?php echo htmlspecialchars($tipoKey); ?>"
                data-estado="<?php echo htmlspecialchars($estadoTecnico); ?>"
                data-ubicacion="<?php echo htmlspecialchars(mb_strtolower($ubicacion, 'UTF-8')); ?>"
            >
                <div class="card equipo-card h-100">
                    <div class="card-body">
                        <!-- Header -->
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="equipo-avatar <?php echo $colors['bg']; ?> <?php echo $colors['text']; ?>">
                                    <i class="fas <?php echo $iconClass; ?>"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="equipo-tipo-label"><?php echo htmlspecialchars($tipoLabel); ?></div>
                                    <div class="equipo-name text-truncate"><?php echo htmlspecialchars($clienteNombre); ?></div>
                                    <span class="status-badge <?php echo $estadoBadgeClass; ?> mt-1">
                                        <span class="dot" style="background:<?php echo $estadoDot; ?>;"></span>
                                        <?php echo htmlspecialchars($estadoLabel); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones" style="border-radius:8px; width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-ellipsis"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item" href="<?php echo url('equipos/' . $equipoId); ?>"><i class="fas fa-eye me-2"></i>Ver detalles</a></li>
                                    <li><a class="dropdown-item" href="<?php echo url('equipos/' . $equipoId . '/visitas'); ?>"><i class="fas fa-screwdriver-wrench me-2"></i>Visitas técnicas</a></li>
                                    <li><a class="dropdown-item" href="<?php echo url('equipos/' . $equipoId . '/edit'); ?>"><i class="fas fa-pen me-2"></i>Editar</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Info grid -->
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="info-label">Marca</div>
                                <div class="info-value"><i class="fas fa-tag me-1 text-muted" style="font-size:.75rem;"></i><?php echo htmlspecialchars($marca); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="info-label">Modelo</div>
                                <div class="info-value"><i class="fas fa-layer-group me-1 text-muted" style="font-size:.75rem;"></i><?php echo htmlspecialchars($modelo); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="info-label">Nº de serie</div>
                                <div class="info-value serie-code"><?php echo htmlspecialchars($numeroSerie); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="info-label">Instalación</div>
                                <div class="info-value"><i class="fas fa-calendar me-1 text-muted" style="font-size:.75rem;"></i><?php echo htmlspecialchars($fechaInst); ?></div>
                            </div>
                            <?php if ($ubicacion): ?>
                            <div class="col-12">
                                <div class="info-label">Ubicación</div>
                                <div class="info-value text-truncate"><i class="fas fa-location-dot me-1 text-muted" style="font-size:.75rem;"></i><?php echo htmlspecialchars($ubicacion); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($obs): ?>
                            <div class="col-12">
                                <div class="info-label">Observaciones</div>
                                <div class="info-value text-truncate text-muted" title="<?php echo htmlspecialchars($obs); ?>"><i class="fas fa-note-sticky me-1" style="font-size:.75rem;"></i><?php echo htmlspecialchars($obs); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Footer actions -->
                        <div class="equipo-footer">
                            <div class="equipo-actions">
                                <a href="<?php echo url('equipos/' . $equipoId); ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>Ver detalles
                                </a>
                                <a href="<?php echo url('equipos/' . $equipoId . '/visitas'); ?>" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-screwdriver-wrench me-1"></i>Visitas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <div class="card pagination-card mt-2">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="text-muted small" id="equiposRange">Mostrando 0 a 0 de 0 equipos</div>
            <nav aria-label="Paginación de equipos">
                <ul class="pagination pagination-sm mb-0" id="equiposPagination"></ul>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <div class="text-muted small">Mostrar</div>
                <select id="equiposPageSize" class="form-select form-select-sm" style="width:84px;">
                    <option value="6">6</option>
                    <option value="12" selected>12</option>
                    <option value="24">24</option>
                </select>
                <div class="text-muted small">por página</div>
            </div>
        </div>
    </div>

    <script>
    (function initEquiposList() {
        const searchInput   = document.getElementById('equiposSearch');
        const tipoSelect    = document.getElementById('equiposTipo');
        const estadoSelect  = document.getElementById('equiposEstado');
        const ubicSelect    = document.getElementById('equiposUbicacion');
        const countEl       = document.getElementById('equiposCount');
        const rangeEl       = document.getElementById('equiposRange');
        const paginationEl  = document.getElementById('equiposPagination');
        const pageSizeSelect= document.getElementById('equiposPageSize');
        const gridBtn       = document.getElementById('equiposGridBtn');
        const listBtn       = document.getElementById('equiposListBtn');

        const cards = Array.from(document.querySelectorAll('[data-equipo-card]'));

        let page     = 1;
        let pageSize = parseInt(pageSizeSelect ? pageSizeSelect.value : '12', 10);
        let currentView = 'grid';

        function normalize(v) { return (v || '').toString().trim().toLowerCase(); }

        function matches(card) {
            const q      = normalize(searchInput ? searchInput.value : '');
            const tipo   = (tipoSelect   ? tipoSelect.value   : '').trim();
            const estado = (estadoSelect ? estadoSelect.value : '').trim();
            const ubic   = normalize(ubicSelect ? ubicSelect.value : '');

            if (q && !card.dataset.name.includes(q)) return false;
            if (tipo   && card.dataset.tipo   !== tipo)   return false;
            if (estado && card.dataset.estado !== estado) return false;
            if (ubic   && !card.dataset.ubicacion.includes(ubic)) return false;

            return true;
        }

        function setView(view) {
            currentView = view;
            if (gridBtn) gridBtn.classList.toggle('active', view === 'grid');
            if (listBtn) listBtn.classList.toggle('active', view === 'list');

            cards.forEach((c) => {
                if (!c.dataset.gridClasses) c.dataset.gridClasses = c.className;
                c.className = view === 'list'
                    ? 'col-12 mb-3 equipo-item'
                    : c.dataset.gridClasses;
            });
        }

        function renderPagination(total, totalPages) {
            if (!paginationEl) return;
            paginationEl.innerHTML = '';
            if (totalPages <= 1) return;

            function addPage(label, targetPage, disabled, ariaLabel) {
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

            addPage('&laquo;', 1, page === 1, 'Primera');
            addPage('&lsaquo;', Math.max(1, page - 1), page === 1, 'Anterior');

            const start = Math.max(1, page - 3);
            const end   = Math.min(totalPages, page + 3);
            for (let p = start; p <= end; p++) addPage(String(p), p, false);

            addPage('&rsaquo;', Math.min(totalPages, page + 1), page === totalPages, 'Siguiente');
            addPage('&raquo;', totalPages, page === totalPages, 'Última');
        }

        function update() {
            pageSize = parseInt(pageSizeSelect ? pageSizeSelect.value : '12', 10);

            const matched    = cards.filter(matches);
            const total      = matched.length;
            const totalPages = Math.max(1, Math.ceil(total / pageSize));
            if (page > totalPages) page = totalPages;

            const startIndex = (page - 1) * pageSize;
            const endIndex   = Math.min(total, startIndex + pageSize);

            cards.forEach((c) => { c.style.display = 'none'; });
            matched.slice(startIndex, endIndex).forEach((c) => { c.style.display = ''; });

            if (countEl) countEl.textContent = total + ' equipo' + (total === 1 ? '' : 's') + ' encontrado' + (total === 1 ? '' : 's');
            if (rangeEl) rangeEl.textContent = 'Mostrando ' + (total ? (startIndex + 1) : 0) + ' a ' + (total ? endIndex : 0) + ' de ' + total + ' equipos';

            renderPagination(total, totalPages);
        }

        const debounced = (() => {
            let t = null;
            return (fn) => { if (t) clearTimeout(t); t = setTimeout(fn, 140); };
        })();

        if (searchInput)    searchInput.addEventListener('input', () => debounced(update));
        if (tipoSelect)     tipoSelect.addEventListener('change', () => { page = 1; update(); });
        if (estadoSelect)   estadoSelect.addEventListener('change', () => { page = 1; update(); });
        if (ubicSelect)     ubicSelect.addEventListener('change', () => { page = 1; update(); });
        if (pageSizeSelect) pageSizeSelect.addEventListener('change', () => { page = 1; update(); });
        if (gridBtn)        gridBtn.addEventListener('click', () => { setView('grid'); update(); });
        if (listBtn)        listBtn.addEventListener('click', () => { setView('list'); update(); });

        setView('grid');
        update();
    })();
    </script>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>