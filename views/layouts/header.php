<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --app-bg: #f5f7fb;
            --card-radius: 1rem;
            --card-shadow: 0 1px 2px rgba(16, 24, 40, 0.06), 0 10px 24px rgba(16, 24, 40, 0.06);
            --card-shadow-hover: 0 2px 6px rgba(16, 24, 40, 0.08), 0 16px 32px rgba(16, 24, 40, 0.08);
            --soft-border: rgba(15, 23, 42, 0.10);
        }

        .app-body {
            background: radial-gradient(1200px 600px at 30% -10%, rgba(102, 126, 234, 0.14), transparent 60%),
                        radial-gradient(900px 600px at 100% 0%, rgba(118, 75, 162, 0.12), transparent 55%),
                        var(--app-bg);
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        .sidebar::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(900px 500px at 20% 10%, rgba(255,255,255,0.14), transparent 60%),
                        radial-gradient(800px 500px at 80% 80%, rgba(0,0,0,0.14), transparent 55%);
            pointer-events: none;
            opacity: .75;
        }
        .sidebar .position-sticky {
            position: relative;
            z-index: 1;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            margin: 0.25rem 0;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            transition: box-shadow 0.25s ease, transform 0.25s ease;
        }
        .card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-1px);
        }

        .card .card-header {
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        }

        .badge-status {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
        .stats-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .stats-card.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stats-card.warning {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        .stats-card.danger {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .app-topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: rgba(245, 247, 251, 0.75);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        }
        .app-topbar .topbar-inner {
            padding-top: .75rem;
            padding-bottom: .75rem;
        }
        .app-topbar .topbar-search {
            max-width: 520px;
        }
        .app-topbar .input-group-text {
            background: #fff;
            border-color: var(--soft-border);
        }
        .app-topbar .form-control {
            border-color: var(--soft-border);
        }
        .app-topbar .btn-icon {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
        }
        .app-topbar .btn-icon.btn-outline-secondary {
            border-color: rgba(15, 23, 42, 0.12);
            background: #fff;
        }
        .app-topbar .btn-icon.btn-outline-secondary:hover {
            background: #f8fafc;
        }
        .app-topbar .badge-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 18px;
            height: 18px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            font-size: .7rem;
            line-height: 1;
            border: 2px solid var(--app-bg);
        }

        .search-suggest-menu {
            position: absolute;
            top: calc(100% + .5rem);
            left: 0;
            right: 0;
            max-height: 420px;
            overflow: auto;
            padding: .5rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1rem;
            box-shadow: var(--card-shadow-hover);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            display: none;
        }
        .search-suggest-menu.show {
            display: block;
        }
        .search-suggest-section {
            padding: .35rem .5rem;
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .10em;
            color: rgba(15, 23, 42, 0.55);
        }
        .search-suggest-item {
            display: flex;
            gap: .75rem;
            align-items: flex-start;
            padding: .6rem .65rem;
            border-radius: .85rem;
            text-decoration: none;
            color: inherit;
        }
        .search-suggest-item:hover,
        .search-suggest-item.active {
            background: rgba(15, 23, 42, 0.05);
        }
        .search-suggest-icon {
            width: 36px;
            height: 36px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        }
        .search-suggest-title {
            font-weight: 800;
            font-size: .92rem;
            letter-spacing: -0.01em;
            line-height: 1.15;
        }
        .search-suggest-subtitle {
            font-size: .82rem;
            color: rgba(15, 23, 42, 0.55);
            line-height: 1.2;
        }
        .search-suggest-meta {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: .4rem;
            flex: 0 0 auto;
        }
        .search-suggest-hint {
            padding: .5rem .65rem;
            border-radius: .85rem;
            background: rgba(15, 23, 42, 0.03);
            color: rgba(15, 23, 42, 0.55);
            font-size: .85rem;
        }

        .page-title {
            letter-spacing: -0.02em;
        }
        .page-subtitle {
            font-size: .95rem;
        }

        .stat-tile {
            background: #fff;
            overflow: hidden;
        }
        .stat-label {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .10em;
            color: rgba(15, 23, 42, 0.55);
            margin-bottom: .25rem;
        }
        .stat-value {
            font-size: 1.65rem;
            font-weight: 900;
            line-height: 1.1;
            color: rgba(15, 23, 42, 0.95);
        }
        .stat-meta {
            font-size: .85rem;
            color: rgba(15, 23, 42, 0.55);
        }
        .stat-side {
            min-width: 124px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: .5rem;
        }
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 1.05rem;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.10);
        }
        .sparkline {
            width: 120px;
            height: 40px;
            opacity: .95;
        }
        .stat-link {
            font-size: .88rem;
            font-weight: 700;
            text-decoration: none;
            color: #2563eb;
        }
        .stat-link:hover {
            color: #1d4ed8;
        }
        .stat-link-success {
            color: #16a34a;
        }
        .stat-link-success:hover {
            color: #15803d;
        }
        .stat-link-warning {
            color: #ea580c;
        }
        .stat-link-warning:hover {
            color: #c2410c;
        }
        .stat-link-danger {
            color: #db2777;
        }
        .stat-link-danger:hover {
            color: #be185d;
        }

        .quick-card .quick-icon {
            width: 56px;
            height: 56px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            margin: 0 auto .85rem;
            font-size: 1.25rem;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        }
        .quick-card .card-title {
            letter-spacing: -0.01em;
        }
        .quick-card .card-text {
            font-size: .92rem;
        }

        .section-card-header {
            background: transparent;
            padding: 1rem 1rem;
        }
        .section-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        }
        .section-link {
            font-weight: 700;
            font-size: .9rem;
            text-decoration: none;
            color: rgba(15, 23, 42, 0.55);
        }
        .section-link:hover {
            color: rgba(15, 23, 42, 0.80);
        }

        .empty-state {
            text-align: center;
            padding: 1.25rem 0;
        }
        .empty-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            margin: 0 auto .75rem;
            font-size: 1.3rem;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        }

        .modern-table thead th {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .10em;
            color: rgba(15, 23, 42, 0.55);
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            padding-top: .65rem;
            padding-bottom: .65rem;
            background: rgba(255, 255, 255, 0.6);
        }
        .modern-table tbody td {
            border-top: 1px solid rgba(15, 23, 42, 0.06);
            padding-top: .75rem;
            padding-bottom: .75rem;
        }
        .action-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .summary-card h5 {
            letter-spacing: -0.01em;
        }
        .summary-metric {
            padding: .85rem .75rem;
            border-radius: 1rem;
            border: 1px solid rgba(15, 23, 42, 0.06);
            background: rgba(255, 255, 255, 0.7);
        }
        .summary-icon {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            margin: 0 auto .5rem;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        }
        .summary-value {
            font-weight: 900;
            font-size: 1.25rem;
            line-height: 1.1;
            color: rgba(15, 23, 42, 0.95);
        }
        .summary-label {
            font-size: .85rem;
            color: rgba(15, 23, 42, 0.55);
        }

        .filter-card .input-group-text {
            background: #fff;
            border-color: var(--soft-border);
        }
        .filter-card .form-control,
        .filter-card .form-select {
            border-color: var(--soft-border);
        }
        .filter-card .form-label {
            margin-bottom: .25rem;
        }

        .view-toggle {
            display: inline-flex;
            gap: .5rem;
        }
        .view-toggle .btn {
            width: 40px;
            height: 36px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .view-toggle .btn.active {
            background: rgba(15, 23, 42, 0.06);
            border-color: rgba(15, 23, 42, 0.12);
        }

        .client-card {
            background: rgba(255, 255, 255, 0.92);
        }
        .client-avatar {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            font-weight: 900;
            letter-spacing: -0.02em;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.10);
        }
        .client-name {
            font-weight: 900;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
            max-width: 320px;
        }
        .client-id {
            font-weight: 700;
        }
        .client-status {
            margin-top: .35rem;
        }
        .client-info .info-label {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .10em;
            color: rgba(15, 23, 42, 0.45);
            margin-bottom: .25rem;
        }
        .client-info .info-value {
            font-weight: 700;
            color: rgba(15, 23, 42, 0.85);
        }
        .client-footer {
            border-top: 1px solid rgba(15, 23, 42, 0.06);
        }
        .plan-value {
            font-weight: 900;
            font-size: 1.15rem;
            letter-spacing: -0.01em;
        }
        .client-actions {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }
        .client-actions .btn {
            border-radius: 12px;
            padding: .45rem .75rem;
            font-weight: 700;
        }
        .pagination-card .pagination .page-link {
            border-radius: 10px;
        }
    </style>
</head>
<body class="app-body">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="fas fa-wifi me-2"></i>
                            Skynetwok
                        </h4>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isActiveRoute('/dashboard') || $_SERVER['REQUEST_URI'] === url('')) ? 'active' : ''; ?>" href="<?php echo url('dashboard'); ?>">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActiveRoute('/clientes') ? 'active' : ''; ?>" href="<?php echo url('clientes'); ?>">
                                <i class="fas fa-users me-2"></i>
                                Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActiveRoute('/pagos') ? 'active' : ''; ?>" href="<?php echo url('pagos'); ?>">
                                <i class="fas fa-dollar-sign me-2"></i>
                                Pagos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActiveRoute('/equipos') ? 'active' : ''; ?>" href="<?php echo url('equipos'); ?>">
                                <i class="fas fa-router me-2"></i>
                                Equipos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActiveRoute('/reportes') ? 'active' : ''; ?>" href="<?php echo url('reportes'); ?>">
                                <i class="fas fa-chart-bar me-2"></i>
                                Reportes
                            </a>
                        </li>
                        <?php if (isAuthenticated()): ?>
                        <li class="nav-item mt-2">
                            <a class="nav-link" href="<?php echo url('logout'); ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Cerrar sesión (<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>)
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <?php if (isAuthenticated()): ?>
                <div class="app-topbar mx-n3 px-3">
                    <div class="topbar-inner d-flex align-items-center justify-content-between gap-3">
                        <div class="flex-grow-1 d-flex justify-content-center justify-content-lg-start">
                            <form class="topbar-search w-100 position-relative" action="<?php echo url('search'); ?>" method="GET" role="search" aria-label="Búsqueda global">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-magnifying-glass"></i>
                                    </span>
                                    <input
                                        id="topbarSearch"
                                        type="search"
                                        name="q"
                                        class="form-control"
                                        placeholder="Buscar clientes, pagos, equipos..."
                                        aria-label="Buscar"
                                        autocomplete="off"
                                        data-suggest-url="<?php echo url('search/suggest'); ?>"
                                    >
                                </div>
                                <div id="topbarSearchMenu" class="dropdown-menu search-suggest-menu" role="listbox" aria-label="Sugerencias"></div>
                            </form>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-icon position-relative" title="Notificaciones" aria-label="Notificaciones">
                                <i class="fas fa-bell"></i>
                                <span class="badge bg-danger badge-dot">2</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-icon" title="Ayuda" aria-label="Ayuda">
                                <i class="fas fa-circle-question"></i>
                            </button>

                            <div class="dropdown">
                                <button class="btn btn-outline-secondary d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle"></i>
                                    <span class="d-none d-lg-inline">
                                        <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? ''); ?>
                                    </span>
                                    <i class="fas fa-chevron-down small opacity-75"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <div class="dropdown-item-text">
                                            <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></div>
                                            <div class="small text-muted">Administrador</div>
                                        </div>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo url('logout'); ?>">
                                            <i class="fas fa-right-from-bracket me-2"></i>
                                            Cerrar sesión
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
