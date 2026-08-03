<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user me-2"></i>
        Cliente: <?php echo htmlspecialchars($cliente['nombre']); ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo url('clientes'); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Volver a Clientes
            </a>
            <a href="<?php echo url('clientes/' . $cliente['id'] . '/edit'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i>
                Editar
            </a>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="fas fa-trash me-1"></i>
                Eliminar
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
        <!-- Información Personal -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-user-circle me-2"></i>
                <h5 class="mb-0">Información Personal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Nombre:</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($cliente['nombre']); ?></dd>
                            
                            <dt class="col-sm-4">Teléfono:</dt>
                            <dd class="col-sm-8">
                                <a href="tel:<?php echo htmlspecialchars($cliente['telefono']); ?>" class="text-decoration-none">
                                    <i class="fas fa-phone me-1"></i>
                                    <?php echo htmlspecialchars($cliente['telefono']); ?>
                                </a>
                            </dd>
                            
                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">
                                <?php if (!empty($cliente['email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($cliente['email']); ?>" class="text-decoration-none">
                                        <i class="fas fa-envelope me-1"></i>
                                        <?php echo htmlspecialchars($cliente['email']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No especificado</span>
                                <?php endif; ?>
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Estado:</dt>
                            <dd class="col-sm-8">
                                <?php
                                $estadoClass = '';
                                $estadoIcon = '';
                                switch($cliente['estado']) {
                                    case 'activo':
                                        $estadoClass = 'success';
                                        $estadoIcon = 'check-circle';
                                        break;
                                    case 'suspendido':
                                        $estadoClass = 'danger';
                                        $estadoIcon = 'times-circle';
                                        break;
                                    case 'pendiente':
                                        $estadoClass = 'warning';
                                        $estadoIcon = 'clock';
                                        break;
                                }
                                ?>
                                <span class="badge bg-<?php echo $estadoClass; ?>">
                                    <i class="fas fa-<?php echo $estadoIcon; ?> me-1"></i>
                                    <?php echo ucfirst($cliente['estado']); ?>
                                </span>
                            </dd>
                            
                            <dt class="col-sm-4">Dirección:</dt>
                            <dd class="col-sm-8">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo nl2br(htmlspecialchars($cliente['direccion'])); ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Servicio -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-wifi me-2"></i>
                <h5 class="mb-0">Información del Servicio</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Tipo de Conexión:</dt>
                            <dd class="col-sm-7">
                                <?php
                                $tipoIcon = '';
                                $tipoTexto = '';
                                switch($cliente['tipo_conexion']) {
                                    case 'fibra_optica':
                                        $tipoIcon = 'bolt';
                                        $tipoTexto = 'Fibra Óptica';
                                        break;
                                    case 'inalambrica':
                                        $tipoIcon = 'broadcast-tower';
                                        $tipoTexto = 'Inalámbrica';
                                        break;
                                    case 'cableado_utp':
                                        $tipoIcon = 'ethernet';
                                        $tipoTexto = 'Cableado (UTP)';
                                        break;
                                }
                                ?>
                                <span class="badge bg-info">
                                    <i class="fas fa-<?php echo $tipoIcon; ?> me-1"></i>
                                    <?php echo $tipoTexto; ?>
                                </span>
                            </dd>
                            
                            <dt class="col-sm-5">Plan Mensual:</dt>
                            <dd class="col-sm-7">
                                <span class="h5 text-success">
                                    $<?php echo number_format($cliente['plan_mensual'], 2); ?>
                                </span>
                                <small class="text-muted">/ mes</small>
                            </dd>

                            <dt class="col-sm-5">Megas Contratados:</dt>
                            <dd class="col-sm-7">
                                <i class="fas fa-tachometer-alt me-1"></i>
                                <?php echo !empty($cliente['megas_contratados']) ? htmlspecialchars($cliente['megas_contratados']) . ' Mbps' : '<span class="text-muted">No especificado</span>'; ?>
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Fecha Contratación:</dt>
                            <dd class="col-sm-7">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('d/m/Y', strtotime($cliente['fecha_contratacion'])); ?>
                            </dd>

                            <dt class="col-sm-5">Día de Corte:</dt>
                            <dd class="col-sm-7">
                                <i class="fas fa-calendar-day me-1"></i>
                                <?php echo htmlspecialchars($cliente['dia_corte'] ?? '—'); ?>
                            </dd>
                            
                            <dt class="col-sm-5">Cliente desde:</dt>
                            <dd class="col-sm-7">
                                <?php
                                $fechaContrato = new DateTime($cliente['fecha_contratacion']);
                                $fechaActual = new DateTime();
                                $diferencia = $fechaActual->diff($fechaContrato);
                                
                                if ($diferencia->y > 0) {
                                    echo $diferencia->y . ' año' . ($diferencia->y > 1 ? 's' : '');
                                    if ($diferencia->m > 0) {
                                        echo ', ' . $diferencia->m . ' mes' . ($diferencia->m > 1 ? 'es' : '');
                                    }
                                } elseif ($diferencia->m > 0) {
                                    echo $diferencia->m . ' mes' . ($diferencia->m > 1 ? 'es' : '');
                                } elseif ($diferencia->d > 0) {
                                    echo $diferencia->d . ' día' . ($diferencia->d > 1 ? 's' : '');
                                } else {
                                    echo 'Hoy';
                                }
                                ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Pagos -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-money-bill-wave me-2"></i>
                    <h5 class="mb-0 d-inline">Historial de Pagos</h5>
                    <span class="badge bg-primary ms-2"><?php echo count($historialPagos); ?> registros</span>
                </div>
                <a href="<?php echo url('pagos/create?cliente=' . $cliente['id']); ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Registrar Pago
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($historialPagos)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Este cliente aún no tiene pagos registrados.
                        <a href="<?php echo url('pagos/create?cliente=' . $cliente['id']); ?>" class="alert-link">Registrar el primer pago</a>
                    </div>
                <?php else: ?>
                    <!-- Resumen de estadísticas -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-success mb-1">$<?php echo number_format($estadisticasPagos['total_pagado'] ?? 0); ?></h6>
                                <small class="text-muted">Total Pagado</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-warning mb-1">$<?php echo number_format($estadisticasPagos['total_pendiente'] ?? 0); ?></h6>
                                <small class="text-muted">Total Pendiente</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-info mb-1"><?php echo $estadisticasPagos['pagos_realizados'] ?? 0; ?></h6>
                                <small class="text-muted">Pagos Realizados</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-danger mb-1"><?php echo ($estadisticasPagos['pagos_pendientes'] ?? 0) + ($estadisticasPagos['pagos_vencidos'] ?? 0); ?></h6>
                                <small class="text-muted">Pagos Pendientes</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Tabla de historial -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Factura</th>
                                    <th>Monto</th>
                                    <th>Fecha Pago</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                    <th>Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historialPagos as $pago): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($pago['numero_factura'] ?? 'N/A'); ?></strong>
                                        </td>
                                        <td>
                                            <strong>$<?php echo number_format($pago['monto']); ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($pago['fecha_pago']): ?>
                                                <?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $metodos = [
                                                'transferencia' => 'Transferencia',
                                                'efectivo' => 'Efectivo',
                                                'paypal' => 'PayPal',
                                                'tarjeta' => 'Tarjeta'
                                            ];
                                            echo $metodos[$pago['metodo_pago']] ?? $pago['metodo_pago'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = '';
                                            $estadoTexto = '';
                                            switch($pago['estado']) {
                                                case 'pagado':
                                                    $badgeClass = 'bg-success';
                                                    $estadoTexto = 'Pagado';
                                                    break;
                                                case 'pendiente':
                                                    $badgeClass = 'bg-warning';
                                                    $estadoTexto = 'Pendiente';
                                                    break;
                                                case 'vencido':
                                                    $badgeClass = 'bg-danger';
                                                    $estadoTexto = 'Vencido';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $estadoTexto; ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($pago['estado'] === 'pagado') {
                                                echo '<span class="text-muted">-</span>';
                                            } else {
                                                $dias = (new DateTime())->diff(new DateTime($pago['fecha_vencimiento']))->days;
                                                $esVencido = new DateTime() > new DateTime($pago['fecha_vencimiento']);
                                                
                                                if ($esVencido) {
                                                    echo '<span class="badge bg-danger">' . $dias . ' días vencido</span>';
                                                } else {
                                                    echo '<span class="badge bg-info">' . $dias . ' días restantes</span>';
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($historialPagos) > 10): ?>
                        <div class="text-center mt-3">
                            <small class="text-muted">Mostrando los últimos registros</small>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Resumen -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Resumen
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>ID del Cliente:</span>
                    <span class="fw-bold">#<?php echo $cliente['id']; ?></span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Ingreso Mensual:</span>
                    <span class="fw-bold text-success">$<?php echo number_format($cliente['plan_mensual'], 2); ?></span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Ingreso Anual:</span>
                    <span class="fw-bold text-info">$<?php echo number_format($cliente['plan_mensual'] * 12, 2); ?></span>
                </div>
                
                <hr>
                
                <!-- Información de pagos -->
                <?php if (!empty($estadisticasPagos)): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Total Pagado:</span>
                    <span class="fw-bold text-success">$<?php echo number_format($estadisticasPagos['total_pagado'] ?? 0); ?></span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Total Pendiente:</span>
                    <span class="fw-bold text-warning">$<?php echo number_format($estadisticasPagos['total_pendiente'] ?? 0); ?></span>
                </div>
                
                <?php if ($estadisticasPagos['ultimo_pago']): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Último Pago:</span>
                    <span class="text-muted">
                        <?php echo date('d/m/Y', strtotime($estadisticasPagos['ultimo_pago'])); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($estadisticasPagos['proximo_vencimiento']): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Próximo Vencimiento:</span>
                    <span class="text-muted">
                        <?php 
                        $fechaVenc = new DateTime($estadisticasPagos['proximo_vencimiento']);
                        $hoy = new DateTime();
                        $diasRestantes = $hoy->diff($fechaVenc)->days;
                        $esVencido = $hoy > $fechaVenc;
                        
                        echo $fechaVenc->format('d/m/Y');
                        if ($esVencido) {
                            echo ' <small class="text-danger">(Vencido)</small>';
                        } else {
                            echo ' <small class="text-info">(' . $diasRestantes . ' días)</small>';
                        }
                        ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <hr>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span>Registrado:</span>
                    <span class="text-muted">
                        <?php echo date('d/m/Y H:i', strtotime($cliente['created_at'])); ?>
                    </span>
                </div>
                
                <?php if ($cliente['updated_at'] !== $cliente['created_at']): ?>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span>Actualizado:</span>
                    <span class="text-muted">
                        <?php echo date('d/m/Y H:i', strtotime($cliente['updated_at'])); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Acciones Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo url('pagos/create?cliente=' . $cliente['id']); ?>" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-money-bill-wave me-1"></i>
                        Registrar Pago
                    </a>
                    
                    <a href="<?php echo url('equipos/create?cliente=' . $cliente['id']); ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-router me-1"></i>
                        Asignar Equipo
                    </a>
                    
                    <button class="btn btn-outline-warning btn-sm" onclick="cambiarEstado('<?php echo $cliente['estado']; ?>')">
                        <i class="fas fa-exchange-alt me-1"></i>
                        Cambiar Estado
                    </button>
                    
                    <a href="tel:<?php echo htmlspecialchars($cliente['telefono']); ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-phone me-1"></i>
                        Llamar Cliente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar el cliente <strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Advertencia:</strong> Esta acción no se puede deshacer. Se eliminarán todos los datos relacionados con este cliente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?php echo url('clientes/' . $cliente['id'] . '/delete'); ?>" class="d-inline">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Eliminar Cliente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function cambiarEstado(estadoActual) {
    let nuevoEstado;
    let mensaje;
    
    switch(estadoActual) {
        case 'activo':
            nuevoEstado = 'suspendido';
            mensaje = '¿Desea suspender este cliente?';
            break;
        case 'suspendido':
            nuevoEstado = 'activo';
            mensaje = '¿Desea reactivar este cliente?';
            break;
        case 'pendiente':
            nuevoEstado = 'activo';
            mensaje = '¿Desea activar este cliente?';
            break;
        default:
            nuevoEstado = 'activo';
            mensaje = '¿Desea cambiar el estado de este cliente?';
    }
    
    if (confirm(mensaje)) {
        // Aquí podrías hacer una petición AJAX o redirigir a una ruta de actualización
        window.location.href = `<?php echo url('clientes/' . $cliente['id'] . '/update-status'); ?>?estado=${nuevoEstado}`;
    }
}
</script>

<?php include 'views/layouts/footer.php'; ?>
