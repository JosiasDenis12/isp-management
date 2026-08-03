<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-wifi me-2"></i>
        <?php echo htmlspecialchars($equipo['marca'] . ' ' . $equipo['modelo']); ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo url('equipos'); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Volver a Equipos
            </a>
            <a href="<?php echo url('equipos/' . $equipo['id'] . '/visitas'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-tools me-1"></i>
                Visitas
            </a>
        </div>
        <div class="btn-group">
            <a href="<?php echo url('equipos/create?cliente_id=' . (int)$equipo['cliente_id'] . '&fecha_instalacion=' . urlencode($equipo['fecha_instalacion'] ?? date('Y-m-d'))); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Registrar otro
            </a>
            <a href="<?php echo url('equipos/' . $equipo['id'] . '/edit'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-pen me-1"></i>
                Editar
            </a>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#eliminarEquipoModal">
                <i class="fas fa-trash me-1"></i>
                Eliminar
            </button>
            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#programarMantenimientoModal">
                <i class="fas fa-calendar-plus me-1"></i>
                Programar Mantenimiento
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
        <!-- Información del Equipo -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-wifi me-2"></i>
                    <strong>Información del Equipo</strong>
                </div>
                <div>
                    <?php
                    $badgeClass = '';
                    switch($equipo['estado_tecnico']) {
                        case 'operativo':
                            $badgeClass = 'bg-success';
                            break;
                        case 'necesita_revision':
                            $badgeClass = 'bg-warning';
                            break;
                        case 'fuera_de_servicio':
                            $badgeClass = 'bg-danger';
                            break;
                        case 'en_mantenimiento':
                            $badgeClass = 'bg-info';
                            break;
                        default:
                            $badgeClass = 'bg-secondary';
                    }
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $equipo['estado_tecnico'])); ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Cliente:</strong></td>
                                <td><?php echo htmlspecialchars($equipo['cliente_nombre']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Instalación:</strong></td>
                                <td><?php echo !empty($equipo['instalacion_id']) ? 'Instalación #' . (int)$equipo['instalacion_id'] : '<span class="text-muted">Registro individual</span>'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tipo de Equipo:</strong></td>
                                <td>
                                    <?php
                                    $iconos = [
                                        'router' => 'fas fa-wifi',
                                        'modem' => 'fas fa-ethernet',
                                        'switch' => 'fas fa-network-wired',
                                        'access_point' => 'fas fa-broadcast-tower',
                                        'antena' => 'fas fa-satellite-dish',
                                        'otro' => 'fas fa-question-circle'
                                    ];
                                    $icono = $iconos[$equipo['tipo_equipo']] ?? 'fas fa-question-circle';
                                    ?>
                                    <i class="<?php echo $icono; ?> me-2"></i>
                                    <?php echo ucfirst(str_replace('_', ' ', $equipo['tipo_equipo'])); ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Marca:</strong></td>
                                <td><?php echo htmlspecialchars($equipo['marca']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Modelo:</strong></td>
                                <td><?php echo htmlspecialchars($equipo['modelo']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Número de Serie:</strong></td>
                                <td><?php echo $equipo['numero_serie'] ? htmlspecialchars($equipo['numero_serie']) : '<span class="text-muted">No especificado</span>'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Fecha de Instalación:</strong></td>
                                <td>
                                    <?php 
                                    if ($equipo['fecha_instalacion']) {
                                        echo date('d/m/Y', strtotime($equipo['fecha_instalacion']));
                                    } else {
                                        echo '<span class="text-muted">No especificada</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Registrado:</strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($equipo['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Última Actualización:</strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($equipo['updated_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if ($equipo['observaciones_tecnico']): ?>
                <hr>
                <div>
                    <strong>Observaciones Técnicas:</strong>
                    <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($equipo['observaciones_tecnico'])); ?></p>
                </div>
                <?php endif; ?>

                <?php if (in_array(($equipo['tipo_equipo'] ?? ''), ['antena', 'modem'], true)): ?>
                <hr>
                <div>
                    <strong>Datos de red y acceso:</strong>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td><strong>MAC Address:</strong></td>
                                    <td><?php echo !empty($equipo['mac_address']) ? htmlspecialchars($equipo['mac_address']) : '<span class="text-muted">No especificado</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Dirección IP:</strong></td>
                                    <td><?php echo !empty($equipo['direccion_ip']) ? htmlspecialchars($equipo['direccion_ip']) : '<span class="text-muted">No especificada</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Contraseña:</strong></td>
                                    <td><?php echo !empty($equipo['password_acceso']) ? htmlspecialchars($equipo['password_acceso']) : '<span class="text-muted">No especificada</span>'; ?></td>
                                </tr>
                            </table>
                        </div>
                        <?php if (($equipo['tipo_equipo'] ?? '') === 'modem'): ?>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td><strong>SSID:</strong></td>
                                    <td><?php echo !empty($equipo['ssid']) ? htmlspecialchars($equipo['ssid']) : '<span class="text-muted">No especificado</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Usuario:</strong></td>
                                    <td><?php echo !empty($equipo['usuario_acceso']) ? htmlspecialchars($equipo['usuario_acceso']) : '<span class="text-muted">No especificado</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Acceso:</strong></td>
                                    <td>
                                        <span class="badge <?php echo !empty($equipo['acceso_habilitado']) ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo !empty($equipo['acceso_habilitado']) ? 'Activado' : 'Desactivado'; ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historial de Mantenimiento -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>
                <strong>Historial de Mantenimiento</strong>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Equipo Registrado</h6>
                            <p class="timeline-description">El equipo fue registrado en el sistema</p>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($equipo['created_at'])); ?></small>
                        </div>
                    </div>
                    
                    <?php if ($equipo['fecha_instalacion']): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Instalación</h6>
                            <p class="timeline-description">Equipo instalado en las instalaciones del cliente</p>
                            <small class="text-muted"><?php echo date('d/m/Y', strtotime($equipo['fecha_instalacion'])); ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Próximo Mantenimiento</h6>
                            <?php if (!empty($proximoMantenimiento)): ?>
                                <p class="timeline-description">
                                    Mantenimiento programado con <strong><?php echo htmlspecialchars($proximoMantenimiento['tecnico_nombre'] ?? ''); ?></strong>
                                </p>
                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($proximoMantenimiento['fecha_visita'])); ?></small>
                            <?php else: ?>
                                <p class="timeline-description">No hay mantenimientos programados</p>
                                <small class="text-muted">Use “Programar Mantenimiento”</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($mantenimientos)): ?>
                    <hr>
                    <h6 class="mb-3"><i class="fas fa-wrench me-2"></i>Mantenimientos Registrados</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Técnico</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($mantenimientos, 0, 10) as $m): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($m['fecha_visita'])); ?></td>
                                        <td><?php echo htmlspecialchars($m['tecnico_nombre'] ?? ''); ?></td>
                                        <td>
                                            <?php
                                            $estado = $m['estado'] ?? '';
                                            $estadoClass = 'bg-secondary';
                                            if ($estado === 'programada') $estadoClass = 'bg-info';
                                            if ($estado === 'pendiente') $estadoClass = 'bg-secondary';
                                            if ($estado === 'completada') $estadoClass = 'bg-success';
                                            if ($estado === 'cancelada') $estadoClass = 'bg-danger';
                                            if ($estado === 'reprogramada') $estadoClass = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?php echo $estadoClass; ?>"><?php echo htmlspecialchars(ucfirst($estado)); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Estado del Equipo -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i>
                <strong>Estado del Equipo</strong>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="status-circle <?php echo $equipo['estado_tecnico']; ?> mb-3">
                        <?php
                        $iconos = [
                            'operativo' => 'fas fa-check',
                            'necesita_revision' => 'fas fa-exclamation-triangle',
                            'fuera_de_servicio' => 'fas fa-times',
                            'en_mantenimiento' => 'fas fa-wrench'
                        ];
                        $icono = $iconos[$equipo['estado_tecnico']] ?? 'fas fa-question';
                        ?>
                        <i class="<?php echo $icono; ?> fa-2x"></i>
                    </div>
                    <h5><?php echo ucfirst(str_replace('_', ' ', $equipo['estado_tecnico'])); ?></h5>
                    
                    <?php if ($equipo['estado_tecnico'] == 'operativo'): ?>
                        <p class="text-success">El equipo está funcionando correctamente</p>
                    <?php elseif ($equipo['estado_tecnico'] == 'necesita_revision'): ?>
                        <p class="text-warning">El equipo requiere revisión técnica</p>
                    <?php elseif ($equipo['estado_tecnico'] == 'fuera_de_servicio'): ?>
                        <p class="text-danger">El equipo no está operativo</p>
                    <?php elseif ($equipo['estado_tecnico'] == 'en_mantenimiento'): ?>
                        <p class="text-info">El equipo está en proceso de mantenimiento</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i>
                <strong>Acciones Rápidas</strong>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo url('equipos/' . $equipo['id'] . '/cambiar-estado'); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-exchange-alt me-1"></i>
                        Cambiar Estado
                    </a>
                    <a href="<?php echo url('equipos/' . $equipo['id'] . '/visitas'); ?>" class="btn btn-outline-info">
                        <i class="fas fa-tools me-1"></i>
                        Ver Visitas Técnicas
                    </a>
                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#programarMantenimientoModal">
                        <i class="fas fa-calendar-plus me-1"></i>
                        Programar Mantenimiento
                    </button>
                    <a href="<?php echo url('equipos/' . $equipo['id'] . '/reporte'); ?>" class="btn btn-outline-success">
                        <i class="fas fa-file-pdf me-1"></i>
                        Generar Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.status-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.status-circle.operativo {
    background-color: #28a745;
}

.status-circle.necesita_revision {
    background-color: #ffc107;
}

.status-circle.fuera_de_servicio {
    background-color: #dc3545;
}

.status-circle.en_mantenimiento {
    background-color: #17a2b8;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-description {
    margin-bottom: 5px;
    color: #6c757d;
}
</style>

<!-- Confirmación de eliminación -->
<div class="modal fade" id="eliminarEquipoModal" tabindex="-1" aria-labelledby="eliminarEquipoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="eliminarEquipoLabel"><i class="fas fa-triangle-exclamation text-danger me-2"></i>Eliminar equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="mb-1">¿Seguro que deseas eliminar <strong><?php echo htmlspecialchars($equipo['marca'] . ' ' . $equipo['modelo']); ?></strong>?</p>
                <p class="small text-muted mb-0">Esta acción eliminará el equipo del inventario y no se puede deshacer.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?php echo url('equipos/' . $equipo['id'] . '/delete'); ?>">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Eliminar equipo</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Programar Mantenimiento -->
<div class="modal fade" id="programarMantenimientoModal" tabindex="-1" aria-labelledby="programarMantenimientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="programarMantenimientoLabel">
                    <i class="fas fa-calendar-plus me-2"></i>
                    Programar Mantenimiento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo url('equipos/' . $equipo['id'] . '/programar-mantenimiento'); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_visita" class="form-label">Fecha y Hora *</label>
                            <input type="datetime-local" class="form-control" id="fecha_visita" name="fecha_visita" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tecnico_nombre" class="form-label">Técnico *</label>
                            <input type="text" class="form-control" id="tecnico_nombre" name="tecnico_nombre" maxlength="255" placeholder="Nombre del técnico que asistirá" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="4" placeholder="Detalle del mantenimiento programado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i>
                        Programar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('fecha_visita');
    if (!fechaInput) return;
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    fechaInput.min = now.toISOString().slice(0, 16);
});
</script>

<?php include 'views/layouts/footer.php'; ?>
