<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-dollar-sign me-2"></i>
        Pagos y Facturación
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo url('pagos/create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            Registrar Pago
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo htmlspecialchars($_GET['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Estadísticas -->
<?php
$totalPagos = count($pagos);
$pagados = array_filter($pagos, function($p) { return $p['estado'] === 'pagado'; });
$pendientes = array_filter($pagos, function($p) { return $p['estado'] === 'pendiente'; });
$vencidos = array_filter($pagos, function($p) { return $p['estado'] === 'vencido'; });
$ingresos = array_sum(array_column($pagados, 'monto'));
?>

<div class="row mb-4">
    <div class="col-md-2">
        <div class="card text-white bg-primary">
            <div class="card-body text-center">
                <h4><?php echo $totalPagos; ?></h4>
                <p class="mb-0">Total Pagos</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-white bg-success">
            <div class="card-body text-center">
                <h4><?php echo count($pagados); ?></h4>
                <p class="mb-0">Pagados</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-white bg-warning">
            <div class="card-body text-center">
                <h4><?php echo count($pendientes); ?></h4>
                <p class="mb-0">Pendientes</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-white bg-danger">
            <div class="card-body text-center">
                <h4><?php echo count($vencidos); ?></h4>
                <p class="mb-0">Vencidos</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body text-center">
                <h4>$<?php echo number_format($ingresos); ?></h4>
                <p class="mb-0">Total Ingresos</p>
            </div>
        </div>
    </div>
</div>

<!-- Lista de pagos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Historial de Pagos</h5>
    </div>
    <div class="card-body">
        <?php if (empty($pagos)): ?>
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                <h3>No hay pagos registrados</h3>
                <p class="text-muted">Comienza registrando el primer pago</p>
                <a href="<?php echo url('pagos/create'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Registrar Pago
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Cliente</th>
                            <th>Factura</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Fecha Pago</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($pago['cliente_nombre']); ?></strong>
                                </td>
                                <td>
                                    <code><?php echo htmlspecialchars($pago['numero_factura']); ?></code>
                                </td>
                                <td>
                                    <strong class="text-success">$<?php echo number_format($pago['monto']); ?></strong>
                                </td>
                                <td>
                                    <?php
                                    $iconos = [
                                        'transferencia' => 'fas fa-university',
                                        'efectivo' => 'fas fa-money-bill',
                                        'paypal' => 'fab fa-paypal',
                                        'tarjeta' => 'fas fa-credit-card'
                                    ];
                                    $icono = $iconos[$pago['metodo_pago']] ?? 'fas fa-money-bill';
                                    ?>
                                    <i class="<?php echo $icono; ?> me-1"></i>
                                    <?php echo ucfirst($pago['metodo_pago']); ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = '';
                                    $icon = '';
                                    switch ($pago['estado']) {
                                        case 'pagado':
                                            $badgeClass = 'bg-success';
                                            $icon = 'fas fa-check';
                                            break;
                                        case 'vencido':
                                            $badgeClass = 'bg-danger';
                                            $icon = 'fas fa-exclamation-triangle';
                                            break;
                                        case 'pendiente':
                                            $badgeClass = 'bg-warning';
                                            $icon = 'fas fa-clock';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <i class="<?php echo $icon; ?> me-1"></i>
                                        <?php echo ucfirst($pago['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?php echo url('pagos/' . $pago['id']); ?>" class="btn btn-outline-primary" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                                <a href="<?php echo url('pagos/' . $pago['id'] . '/print'); ?>" class="btn btn-outline-success" title="Imprimir factura" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <a href="<?php echo url('pagos/' . $pago['id'] . '/print') . '?type=ticket'; ?>" class="btn btn-outline-secondary" title="Imprimir ticket" target="_blank">
                                                    <i class="fas fa-receipt"></i>
                                                </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
