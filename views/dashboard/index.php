<?php include 'views/layouts/header.php'; ?>

<?php if (isset($error) && $error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Error de conexión:</strong> <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    <hr>
    <p class="mb-0">
        <strong>Soluciones:</strong><br>
        1. Verifica que MySQL esté ejecutándose<br>
        2. Crea la base de datos 'isp_management'<br>
        3. Ejecuta los scripts SQL en database/<br>
        4. Configura las credenciales en config/database.php<br>
        <a href="setup.php" class="btn btn-sm btn-outline-primary mt-2">🔧 Ir a configuración</a>
    </p>
</div>
<?php endif; ?>

<?php
    $nombreUsuario = $_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? '';
    $nombreUsuario = trim((string)$nombreUsuario);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
    <div>
        <h1 class="h2 mb-1 page-title">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard
        </h1>
        <div class="text-muted page-subtitle">
            Bienvenido de vuelta<?php echo $nombreUsuario !== '' ? ', ' . htmlspecialchars($nombreUsuario) : ''; ?>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="setup.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-cog me-1"></i>
            Configuración
        </a>

        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-download me-1"></i>
                Exportar
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                    <a class="dropdown-item" href="<?php echo url('reportes'); ?>">
                        <i class="fas fa-file-lines me-2"></i>
                        Ir a reportes
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button class="dropdown-item" type="button" disabled>
                        <i class="fas fa-file-excel me-2"></i>
                        Excel (próximamente)
                    </button>
                </li>
                <li>
                    <button class="dropdown-item" type="button" disabled>
                        <i class="fas fa-file-pdf me-2"></i>
                        PDF (próximamente)
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Estadísticas principales -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Clientes Activos</div>
                        <div class="stat-value"><?php echo $clientesStats['activos'] ?? 0; ?></div>
                        <div class="stat-meta">de <?php echo $clientesStats['total'] ?? 0; ?> clientes totales</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 30 C 20 26, 22 18, 36 20 S 56 28, 70 18 S 92 8, 118 12" fill="none" stroke="rgba(59,130,246,.9)" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <a class="stat-link" href="<?php echo url('clientes'); ?>">Ver todos <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Ingresos del Mes</div>
                        <div class="stat-value">$<?php echo number_format($pagosStats['ingresos_mes'] ?? 0); ?></div>
                        <div class="stat-meta"><?php echo $pagosStats['total_pagos'] ?? 0; ?> pagos procesados</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-danger-subtle text-danger">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 26 C 16 30, 26 18, 38 22 S 58 34, 72 18 S 94 10, 118 14" fill="none" stroke="rgba(236,72,153,.9)" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <a class="stat-link stat-link-danger" href="<?php echo url('pagos'); ?>">Ver detalles <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Pagos Vencidos</div>
                        <div class="stat-value"><?php echo $pagosStats['pagos_vencidos'] ?? 0; ?></div>
                        <div class="stat-meta"><?php echo $pagosStats['pagos_pendientes'] ?? 0; ?> pendientes</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-warning-subtle text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 28 C 16 18, 28 30, 42 24 S 64 16, 78 22 S 98 34, 118 18" fill="none" stroke="rgba(249,115,22,.95)" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <a class="stat-link stat-link-warning" href="<?php echo url('pagos'); ?>">Ver pendientes <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-tile">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="stat-label">Equipos Operativos</div>
                        <div class="stat-value"><?php echo $equiposStats['operativos'] ?? 0; ?></div>
                        <div class="stat-meta"><?php echo $equiposStats['necesitan_revision'] ?? 0; ?> necesitan revisión</div>
                    </div>
                    <div class="stat-side">
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <svg class="sparkline" viewBox="0 0 120 40" aria-hidden="true">
                            <path d="M2 26 C 14 30, 26 16, 40 18 S 60 26, 74 16 S 98 20, 118 12" fill="none" stroke="rgba(34,197,94,.95)" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <a class="stat-link stat-link-success" href="<?php echo url('equipos'); ?>">Ver equipos <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navegación rápida -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card quick-card h-100">
            <div class="card-body text-center">
                <div class="quick-icon bg-primary-subtle text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <h5 class="card-title mb-1">Gestión de Clientes</h5>
                <p class="card-text text-muted">Administra clientes, estados y tipos de conexión de manera fácil.</p>
                <a href="<?php echo url('clientes'); ?>" class="btn btn-primary">
                    Ir a Clientes <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card quick-card h-100">
            <div class="card-body text-center">
                <div class="quick-icon bg-success-subtle text-success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h5 class="card-title mb-1">Pagos y Facturación</h5>
                <p class="card-text text-muted">Seguimiento de pagos, facturas y alertas de vencimiento.</p>
                <a href="<?php echo url('pagos'); ?>" class="btn btn-success">
                    Ir a Pagos <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card quick-card h-100">
            <div class="card-body text-center">
                <div class="quick-icon bg-info-subtle text-info">
                    <i class="fas fa-router"></i>
                </div>
                <h5 class="card-title mb-1">Equipos Técnicos</h5>
                <p class="card-text text-muted">Control de equipos instalados y mantenimiento de la red.</p>
                <a href="<?php echo url('equipos'); ?>" class="btn btn-info">
                    Ir a Equipos <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card quick-card h-100">
            <div class="card-body text-center">
                <div class="quick-icon bg-warning-subtle text-warning">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h5 class="card-title mb-1">Reportes</h5>
                <p class="card-text text-muted">Estadísticas, informes y análisis detallados del sistema.</p>
                <a href="<?php echo url('reportes'); ?>" class="btn btn-warning">
                    Ir a Reportes <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Próximos vencimientos y clientes con pagos vencidos -->
<div class="row mb-4">
    <!-- Clientes con pagos vencidos -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header section-card-header">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <h5 class="mb-0 d-flex align-items-center gap-2">
                        <span class="section-icon text-danger bg-danger-subtle">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        Clientes con Pagos Vencidos
                        <span class="badge rounded-pill bg-danger-subtle text-danger ms-1"><?php echo count($clientesVencidos); ?></span>
                    </h5>
                    <a class="section-link" href="<?php echo url('pagos'); ?>">Ver todos</a>
                </div>
            </div>

            <div class="card-body">
                <?php if (empty($clientesVencidos)): ?>
                    <div class="empty-state">
                        <div class="empty-icon bg-success-subtle text-success"><i class="fas fa-check-circle"></i></div>
                        <div class="fw-semibold">No hay pagos vencidos</div>
                        <div class="text-muted small">Todo está al día.</div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle modern-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Días Vencido</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($clientesVencidos, 0, 5) as $cliente): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($cliente['telefono']); ?></small>
                                        </td>
                                        <td>
                                            <strong class="text-danger">$<?php echo number_format($cliente['monto']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill bg-danger-subtle text-danger"><?php echo $cliente['dias_vencido']; ?> días</span>
                                        </td>
                                        <td>
                                            <a href="<?php echo url('clientes/' . $cliente['id']); ?>" class="btn btn-sm btn-outline-secondary action-btn" title="Ver cliente" aria-label="Ver cliente">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if (count($clientesVencidos) > 5): ?>
                            <div class="text-center mt-2">
                                <a href="<?php echo url('pagos'); ?>" class="btn btn-sm btn-outline-danger">
                                    Ver todos los <?php echo count($clientesVencidos); ?> pagos vencidos
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Próximos a vencer -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header section-card-header">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <h5 class="mb-0 d-flex align-items-center gap-2">
                        <span class="section-icon text-warning bg-warning-subtle">
                            <i class="fas fa-clock"></i>
                        </span>
                        Próximos a Vencer (7 días)
                        <span class="badge rounded-pill bg-warning-subtle text-warning ms-1"><?php echo count($clientesPorVencer); ?></span>
                    </h5>
                    <a class="section-link" href="<?php echo url('pagos'); ?>">Ver calendario</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($clientesPorVencer)): ?>
                    <div class="empty-state">
                        <div class="empty-icon bg-secondary-subtle text-secondary"><i class="fas fa-calendar-check"></i></div>
                        <div class="fw-semibold">No hay pagos próximos a vencer</div>
                        <div class="text-muted small">¡Excelente! Todo está al día.</div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle modern-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Días Restantes</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($clientesPorVencer, 0, 5) as $cliente): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($cliente['telefono']); ?></small>
                                        </td>
                                        <td>
                                            <strong class="text-warning">$<?php echo number_format($cliente['monto']); ?></strong>
                                        </td>
                                        <td>
                                            <?php 
                                            $dias = $cliente['dias_para_vencer'];
                                            $badgeClass = $dias <= 0 ? 'bg-danger-subtle text-danger' : ($dias <= 2 ? 'bg-danger-subtle text-danger' : ($dias <= 5 ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info'));
                                            ?>
                                            <span class="badge rounded-pill <?php echo $badgeClass; ?>">
                                                <?php echo $dias === 0 ? 'Vence hoy' : $dias . ' días'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $fecha = new DateTime($cliente['fecha_vencimiento']);
                                            echo $fecha->format('d/m/Y');
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (count($clientesPorVencer) > 5): ?>
                            <div class="text-center">
                                <a href="<?php echo url('pagos'); ?>" class="btn btn-sm btn-outline-warning">
                                    Ver todos los <?php echo count($clientesPorVencer); ?> próximos vencimientos
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Resumen adicional de pagos -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card summary-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">Resumen General</h5>
                </div>
                <div class="row g-3 text-center">
                    <div class="col-md-3">
                        <div class="summary-metric">
                            <div class="summary-icon bg-danger-subtle text-danger"><i class="fas fa-users"></i></div>
                            <div class="summary-value"><?php echo $resumenPagos['clientes_con_pagos_vencidos'] ?? 0; ?></div>
                            <div class="summary-label">Clientes con pagos vencidos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-metric">
                            <div class="summary-icon bg-success-subtle text-success"><i class="fas fa-dollar-sign"></i></div>
                            <div class="summary-value">$<?php echo number_format($resumenPagos['monto_total_vencido'] ?? 0); ?></div>
                            <div class="summary-label">Monto total vencido</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-metric">
                            <div class="summary-icon bg-warning-subtle text-warning"><i class="fas fa-calendar"></i></div>
                            <div class="summary-value"><?php echo round($resumenPagos['promedio_dias_atraso'] ?? 0); ?></div>
                            <div class="summary-label">Promedio días de atraso</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-metric">
                            <div class="summary-icon bg-info-subtle text-info"><i class="fas fa-clock"></i></div>
                            <div class="summary-value"><?php echo $resumenPagos['clientes_por_vencer_7_dias'] ?? 0; ?></div>
                            <div class="summary-label">Vencen en 7 días</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
