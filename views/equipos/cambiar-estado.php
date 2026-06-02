<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-exchange-alt me-2"></i>
        Cambiar Estado del Equipo
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo url('equipos/' . $equipo['id']); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver al Equipo
        </a>
    </div>
</div>

<!-- Información del Equipo --> 
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-wifi me-2"></i>
            Información del Equipo
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>Cliente:</strong><br>
                <span class="text-muted"><?php echo htmlspecialchars($equipo['cliente_nombre']); ?></span>
            </div>
            <div class="col-md-3">
                <strong>Equipo:</strong><br>
                <span class="text-muted"><?php echo htmlspecialchars($equipo['marca'] . ' ' . $equipo['modelo']); ?></span>
            </div>
            <div class="col-md-3">
                <strong>Tipo:</strong><br>
                <span class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $equipo['tipo_equipo'])); ?></span>
            </div>
            <div class="col-md-3">
                <strong>Estado Actual:</strong><br>
                <?php
                $badgeClass = '';
                switch($equipo['estado_tecnico']) {
                    case 'operativo':
                        $badgeClass = 'bg-success';
                        break;
                    case 'necesita_revision':
                        $badgeClass = 'bg-warning';
                        break;
                    case 'dañado':
                        $badgeClass = 'bg-danger';
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
    </div>
</div>

<!-- Formulario para Cambiar Estado -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Cambiar Estado Técnico
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo url('equipos/' . $equipo['id'] . '/cambiar-estado'); ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nuevo_estado" class="form-label">Nuevo Estado *</label>
                            <select class="form-select" id="nuevo_estado" name="nuevo_estado" required onchange="actualizarDescripcion()">
                                <option value="">Seleccionar nuevo estado...</option>
                                <option value="operativo" <?php echo ($equipo['estado_tecnico'] == 'operativo') ? 'disabled' : ''; ?>>
                                    Operativo
                                </option>
                                <option value="necesita_revision" <?php echo ($equipo['estado_tecnico'] == 'necesita_revision') ? 'disabled' : ''; ?>>
                                    Necesita Revisión
                                </option>
                                <option value="dañado" <?php echo ($equipo['estado_tecnico'] == 'dañado') ? 'disabled' : ''; ?>>
                                    Dañado
                                </option>
                                <option value="fuera_de_servicio" <?php echo ($equipo['estado_tecnico'] == 'fuera_de_servicio') ? 'disabled' : ''; ?>>
                                    Fuera de Servicio
                                </option>
                                <option value="en_mantenimiento" <?php echo ($equipo['estado_tecnico'] == 'en_mantenimiento') ? 'disabled' : ''; ?>>
                                    En Mantenimiento
                                </option>
                            </select>
                            <div class="form-text">
                                El estado actual no aparece como opción seleccionable.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vista Previa del Estado</label>
                            <div id="estado-preview" class="p-3 border rounded bg-light">
                                <span class="badge bg-secondary">Seleccione un estado</span>
                                <div class="mt-2 small text-muted" id="estado-descripcion">
                                    Seleccione un nuevo estado para ver la descripción.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones del Cambio</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="4" 
                                  placeholder="Descripción del motivo del cambio de estado, trabajos realizados, etc."></textarea>
                        <div class="form-text">
                            Estas observaciones se agregarán a las observaciones técnicas existentes.
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btn-guardar" disabled onclick="return confirmarCambio()">
                            <i class="fas fa-save me-1"></i>
                            Cambiar Estado
                        </button>
                        <a href="<?php echo url('equipos/' . $equipo['id']); ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Información de Estados -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Estados Disponibles
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge bg-success me-2">Operativo</span>
                    <div class="small text-muted mt-1">
                        El equipo está funcionando correctamente sin problemas detectados.
                    </div>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-warning me-2">Necesita Revisión</span>
                    <div class="small text-muted mt-1">
                        El equipo presenta problemas menores que requieren atención técnica.
                    </div>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-danger me-2">Dañado</span>
                    <div class="small text-muted mt-1">
                        El equipo está dañado y requiere reparación inmediata.
                    </div>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-danger me-2">Fuera de Servicio</span>
                    <div class="small text-muted mt-1">
                        El equipo no está funcionando y requiere reparación o reemplazo.
                    </div>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-info me-2">En Mantenimiento</span>
                    <div class="small text-muted mt-1">
                        El equipo está siendo reparado o recibiendo mantenimiento.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historial de Cambios -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Últimos Cambios
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline-simple">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-<?php echo str_replace('bg-', '', $badgeClass); ?>"></div>
                        <div class="timeline-content">
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($equipo['updated_at'])); ?></small>
                            <div>Estado: <?php echo ucfirst(str_replace('_', ' ', $equipo['estado_tecnico'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker bg-secondary"></div>
                        <div class="timeline-content">
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($equipo['created_at'])); ?></small>
                            <div>Equipo registrado</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-simple {
    position: relative;
    padding-left: 30px;
}

.timeline-simple::before {
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
    margin-bottom: 20px;
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
</style>

<script>
// Configuración de estados
const estados = {
    'operativo': {
        badge: 'bg-success',
        descripcion: 'El equipo funcionará correctamente y estará disponible para el cliente.'
    },
    'necesita_revision': {
        badge: 'bg-warning',
        descripcion: 'El equipo será marcado para revisión técnica prioritaria.'
    },
    'dañado': {
        badge: 'bg-danger',
        descripcion: 'El equipo está dañado y requiere reparación antes de volver a funcionar.'
    },
    'fuera_de_servicio': {
        badge: 'bg-danger',
        descripcion: 'El equipo no estará disponible hasta que sea reparado o reemplazado.'
    },
    'en_mantenimiento': {
        badge: 'bg-info',
        descripcion: 'El equipo estará temporalmente fuera de servicio durante el mantenimiento.'
    }
};

function actualizarDescripcion() {
    const select = document.getElementById('nuevo_estado');
    const preview = document.getElementById('estado-preview');
    const btnGuardar = document.getElementById('btn-guardar');
    
    if (select.value && estados[select.value]) {
        const estado = estados[select.value];
        const estadoTexto = select.options[select.selectedIndex].text;
        
        preview.innerHTML = `
            <span class="badge ${estado.badge}">${estadoTexto}</span>
            <div class="mt-2 small text-muted">${estado.descripcion}</div>
        `;
        
        // Habilitar botón
        btnGuardar.disabled = false;
    } else {
        preview.innerHTML = `
            <span class="badge bg-secondary">Seleccione un estado</span>
            <div class="mt-2 small text-muted">Seleccione un nuevo estado para ver la descripción.</div>
        `;
        
        // Deshabilitar botón
        btnGuardar.disabled = true;
    }
}

// Función de confirmación al enviar
function confirmarCambio() {
    const estadoActual = '<?php echo ucfirst(str_replace('_', ' ', $equipo['estado_tecnico'])); ?>';
    const nuevoEstado = document.querySelector('#nuevo_estado option:checked').text;
    
    return confirm(`¿Desea cambiar el estado del equipo de "${estadoActual}" a "${nuevoEstado}"?`);
}
</script>

<?php include 'views/layouts/footer.php'; ?>
