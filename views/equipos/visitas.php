<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tools me-2"></i>
        Visitas Técnicas
        <small class="text-muted">- <?php echo htmlspecialchars($equipo['marca'] . ' ' . $equipo['modelo']); ?></small>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo url('equipos/' . $equipo['id']); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Volver al Equipo
            </a>
            <a href="<?php echo url('equipos'); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i>
                Lista de Equipos
            </a>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaVisitaModal">
                <i class="fas fa-plus me-1"></i>
                Nueva Visita
            </button>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo htmlspecialchars($_GET['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($_GET['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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
                <strong>Tipo:</strong><br>
                <span class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $equipo['tipo_equipo'])); ?></span>
            </div>
            <div class="col-md-3">
                <strong>Estado:</strong><br>
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
            <div class="col-md-3">
                <strong>Número de Serie:</strong><br>
                <span class="text-muted"><?php echo $equipo['numero_serie'] ?: 'No especificado'; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas de Visitas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clipboard-list fa-2x text-primary mb-2"></i>
                <h4 class="mb-0"><?php echo count($visitas); ?></h4>
                <small class="text-muted">Total Visitas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h4 class="mb-0"><?php echo count(array_filter($visitas, function($v) { return ($v['estado'] ?? '') === 'completada'; })); ?></h4>
                <small class="text-muted">Completadas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h4 class="mb-0"><?php echo count(array_filter($visitas, function($v) { return ($v['estado'] ?? '') === 'programada'; })); ?></h4>
                <small class="text-muted">Programadas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-calendar-alt fa-2x text-info mb-2"></i>
                <h4 class="mb-0"><?php echo count(array_filter($visitas, function($v) { return ($v['estado'] ?? '') === 'cancelada'; })); ?></h4>
                <small class="text-muted">Canceladas</small>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Visitas -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-history me-2"></i>
            Historial de Visitas
        </h5>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary active" onclick="filtrarVisitas('todas', this)">Todas</button>
            <button type="button" class="btn btn-outline-secondary" onclick="filtrarVisitas('completadas', this)">Completadas</button>
            <button type="button" class="btn btn-outline-secondary" onclick="filtrarVisitas('programadas', this)">Programadas</button>
            <button type="button" class="btn btn-outline-secondary" onclick="filtrarVisitas('canceladas', this)">Canceladas</button>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($visitas)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay visitas técnicas registradas</h5>
                <p class="text-muted">Comience registrando la primera visita técnica para este equipo.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaVisitaModal">
                    <i class="fas fa-plus me-1"></i>
                    Registrar Primera Visita
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Técnico</th>
                            <th>Tipo de Visita</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visitas as $visita): ?>
                            <tr class="visita-row" data-estado="<?php echo htmlspecialchars($visita['estado'] ?? ''); ?>">
                                <td>
                                    <strong><?php echo date('d/m/Y', strtotime($visita['fecha_visita'])); ?></strong><br>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($visita['fecha_visita'])); ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center">
                                            <?php echo strtoupper(substr(($visita['tecnico_nombre'] ?? '') ?: 'NA', 0, 2)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo !empty($visita['tecnico_nombre']) ? htmlspecialchars($visita['tecnico_nombre']) : 'No asignado'; ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $iconos = [
                                        'mantenimiento' => 'fas fa-wrench text-info',
                                        'reparacion' => 'fas fa-screwdriver text-warning',
                                        'instalacion' => 'fas fa-plug text-success',
                                        'revision' => 'fas fa-search text-primary'
                                    ];
                                    $icono = $iconos[$visita['tipo_visita']] ?? 'fas fa-tools text-secondary';
                                    ?>
                                    <i class="<?php echo $icono; ?> me-2"></i>
                                    <?php echo htmlspecialchars(ucfirst($visita['tipo_visita'] ?? '')); ?>
                                </td>
                                <td>
                                    <?php
                                    $estadoClass = '';
                                    switch($visita['estado']) {
                                        case 'completada':
                                            $estadoClass = 'bg-success';
                                            break;
                                        case 'programada':
                                            $estadoClass = 'bg-info';
                                            break;
                                        case 'cancelada':
                                            $estadoClass = 'bg-danger';
                                            break;
                                        default:
                                            $estadoClass = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $estadoClass; ?>"><?php echo htmlspecialchars(ucfirst($visita['estado'] ?? '')); ?></span>
                                </td>
                                <td>
                                    <div class="observaciones-cell">
                                        <?php 
                                        $observaciones = htmlspecialchars($visita['observaciones'] ?? '');
                                        if (strlen($observaciones) > 100) {
                                            echo substr($observaciones, 0, 100) . '...';
                                        } else {
                                            echo $observaciones;
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a class="btn btn-outline-primary btn-sm" href="<?php echo url('equipos/' . $equipo['id'] . '/visitas/' . $visita['id']); ?>" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a class="btn btn-outline-secondary btn-sm" href="<?php echo url('equipos/' . $equipo['id'] . '/visitas/' . $visita['id'] . '/edit'); ?>" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="<?php echo url('equipos/' . $equipo['id'] . '/visitas/' . $visita['id'] . '/cancel'); ?>" onsubmit="return confirm('¿Deseas cancelar esta visita?');" style="display:inline;">
                                            <button type="submit" class="btn btn-outline-warning btn-sm" title="Cancelar" <?php echo (($visita['estado'] ?? '') === 'cancelada') ? 'disabled' : ''; ?>>
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?php echo url('equipos/' . $equipo['id'] . '/visitas/' . $visita['id'] . '/delete'); ?>" onsubmit="return confirm('¿Está seguro de que desea eliminar esta visita?');" style="display:inline;">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

<!-- Modal Nueva Visita -->
<div class="modal fade" id="nuevaVisitaModal" tabindex="-1" aria-labelledby="nuevaVisitaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevaVisitaModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    Nueva Visita Técnica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="nuevaVisitaForm" method="POST" action="<?php echo url('equipos/' . $equipo['id'] . '/visitas'); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_visita" class="form-label">Fecha y Hora *</label>
                            <input type="datetime-local" class="form-control" id="fecha_visita" name="fecha_visita" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tecnico_nombre" class="form-label">Técnico Asignado *</label>
                            <input type="text" class="form-control" id="tecnico_nombre" name="tecnico_nombre" maxlength="255" placeholder="Nombre del técnico que asistirá" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_visita" class="form-label">Tipo de Visita *</label>
                            <select class="form-select" id="tipo_visita" name="tipo_visita" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="mantenimiento">Mantenimiento Preventivo</option>
                                <option value="reparacion">Reparación</option>
                                <option value="instalacion">Instalación</option>
                                <option value="revision">Revisión Técnica</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estado_visita" class="form-label">Estado *</label>
                            <select class="form-select" id="estado_visita" name="estado_visita" required>
                                <option value="programada">Programada</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observaciones_visita" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones_visita" name="observaciones_visita" rows="4" 
                                  placeholder="Descripción de los trabajos a realizar o realizados..."></textarea>
                    </div>
                    
                    <input type="hidden" name="equipo_id" value="<?php echo $equipo['id']; ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Guardar Visita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 35px;
    height: 35px;
    font-size: 12px;
    font-weight: bold;
}

.observaciones-cell {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.visita-row {
    transition: all 0.3s ease;
}

.visita-row:hover {
    background-color: rgba(0, 123, 255, 0.05);
}
</style>

<script>
// Filtrar visitas por estado
function filtrarVisitas(tipo, boton) {
    const filas = document.querySelectorAll('.visita-row');
    const botones = document.querySelectorAll('.btn-group button');
    
    // Remover clase active de todos los botones
    botones.forEach(btn => btn.classList.remove('active'));
    
    // Agregar clase active al botón clickeado
    if (boton) boton.classList.add('active');
    
    filas.forEach(fila => {
        const estado = fila.getAttribute('data-estado');
        
        if (tipo === 'todas') {
            fila.style.display = '';
            return;
        }

        const match = (
            (tipo === 'completadas' && estado === 'completada') ||
            (tipo === 'programadas' && estado === 'programada') ||
            (tipo === 'canceladas' && estado === 'cancelada')
        );

        fila.style.display = match ? '' : 'none';
    });
}

// Establecer fecha mínima en el campo de fecha
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('fecha_visita');
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    fechaInput.min = now.toISOString().slice(0, 16);
});
</script>

<?php include 'views/layouts/footer.php'; ?>
