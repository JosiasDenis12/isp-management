<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-receipt me-2"></i>
        Factura: <?php echo htmlspecialchars($pago['numero_factura']); ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo url('pagos'); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Volver a Pagos
            </a>
            <a href="<?php echo url('pagos/' . $pago['id'] . '/print'); ?>" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-print me-1"></i>
                Imprimir
            </a>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-success" onclick="marcarComoPagado(<?php echo $pago['id']; ?>)">
                <i class="fas fa-check me-1"></i>
                Marcar como Pagado
            </button>
        </div>
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

<div class="row">
    <div class="col-md-8">
        <!-- Información del Pago -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-receipt me-2"></i>
                    <h5 class="mb-0 d-inline">Detalles del Pago</h5>
                </div>
                <div>
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
                    <span class="badge <?php echo $badgeClass; ?> fs-6">
                        <i class="<?php echo $icon; ?> me-1"></i>
                        <?php echo ucfirst($pago['estado']); ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Número de Factura:</td>
                                <td><code class="fs-5"><?php echo htmlspecialchars($pago['numero_factura']); ?></code></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Cliente:</td>
                                <td><?php echo htmlspecialchars($pago['cliente_nombre']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Monto:</td>
                                <td class="text-success fw-bold fs-5">$<?php echo number_format($pago['monto'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Método de Pago:</td>
                                <td>
                                    <?php
                                    $iconos = [
                                        'transferencia' => 'fas fa-university text-primary',
                                        'efectivo' => 'fas fa-money-bill text-success',
                                        'paypal' => 'fab fa-paypal text-info',
                                        'tarjeta' => 'fas fa-credit-card text-warning'
                                    ];
                                    $icono = $iconos[$pago['metodo_pago']] ?? 'fas fa-money-bill text-secondary';
                                    ?>
                                    <i class="<?php echo $icono; ?> me-2"></i>
                                    <?php echo ucfirst($pago['metodo_pago']); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Fecha de Pago:</td>
                                <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Fecha de Vencimiento:</td>
                                <td>
                                    <?php 
                                    $fechaVenc = date('d/m/Y', strtotime($pago['fecha_vencimiento']));
                                    $esVencido = strtotime($pago['fecha_vencimiento']) < time() && $pago['estado'] !== 'pagado';
                                    ?>
                                    <span class="<?php echo $esVencido ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo $fechaVenc; ?>
                                        <?php if ($esVencido): ?>
                                            <i class="fas fa-exclamation-triangle ms-1" title="Vencido"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Fecha de Registro:</td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pago['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if (!empty($pago['observaciones'])): ?>
                    <div class="mt-3">
                        <h6 class="fw-bold">Observaciones:</h6>
                        <div class="alert alert-light">
                            <i class="fas fa-sticky-note me-2"></i>
                            <?php echo nl2br(htmlspecialchars($pago['observaciones'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historial de Estados -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>
                <h5 class="mb-0 d-inline">Historial de Estados</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Pago Registrado</h6>
                            <p class="timeline-text">El pago fue registrado en el sistema</p>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('d/m/Y H:i', strtotime($pago['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                    
                    <?php if ($pago['estado'] === 'pagado'): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Pago Confirmado</h6>
                            <p class="timeline-text">El pago fue confirmado y procesado</p>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?>
                            </small>
                        </div>
                    </div>
                    <?php elseif ($pago['estado'] === 'vencido'): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-danger"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Pago Vencido</h6>
                            <p class="timeline-text">El pago ha vencido y requiere atención</p>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?>
                            </small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Acciones Rápidas -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($pago['estado'] !== 'pagado'): ?>
                        <button type="button" class="btn btn-success" onclick="marcarComoPagado(<?php echo $pago['id']; ?>)">
                            <i class="fas fa-check me-1"></i>
                            Marcar como Pagado
                        </button>
                    <?php endif; ?>
                    
                    <a href="<?php echo url('pagos/' . $pago['id'] . '/print'); ?>" class="btn btn-outline-primary" target="_blank">
                        <i class="fas fa-print me-1"></i>
                        Imprimir Factura
                    </a>
                    
                    <button type="button" class="btn btn-outline-info" onclick="enviarRecordatorio(<?php echo $pago['id']; ?>)">
                        <i class="fas fa-envelope me-1"></i>
                        Enviar Recordatorio
                    </button>
                    
                    <hr>
                    
                    <a href="<?php echo url('clientes/' . $pago['cliente_id']); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-user me-1"></i>
                        Ver Cliente
                    </a>
                </div>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>
                    Cliente
                </h5>
            </div>
            <div class="card-body">
                <h6><?php echo htmlspecialchars($pago['cliente_nombre']); ?></h6>
                <p class="text-muted small mb-3">Cliente asociado a este pago</p>
                
                <a href="<?php echo url('clientes/' . $pago['cliente_id']); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye me-1"></i>
                    Ver Perfil Completo
                </a>
            </div>
        </div>
    </div>
</div>

<!-- CSS para Timeline -->
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin: 0 0 5px 0;
    color: #333;
}

.timeline-text {
    margin: 0 0 5px 0;
    color: #666;
}
</style>

<!-- Scripts -->
<script>
function marcarComoPagado(pagoId) {
    if (confirm('¿Estás seguro de que quieres marcar este pago como pagado?')) {
        // Aquí iría la llamada AJAX al servidor
        fetch(`/pagos/${pagoId}/marcar-pagado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al actualizar el estado del pago');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
}

function enviarRecordatorio(pagoId) {
    if (confirm('¿Enviar recordatorio de pago al cliente?')) {
        // Aquí iría la llamada AJAX para enviar el recordatorio
        fetch(`/pagos/${pagoId}/recordatorio`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Recordatorio enviado exitosamente');
            } else {
                alert('Error al enviar el recordatorio');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
}
</script>

<?php include 'views/layouts/footer.php'; ?>
