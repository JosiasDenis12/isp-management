<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-edit me-2"></i>
        Editar Visita
        <small class="text-muted">- <?php echo htmlspecialchars($equipo['marca'] . ' ' . $equipo['modelo']); ?></small>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo url('equipos/' . $equipo['id'] . '/visitas'); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Volver a Visitas
            </a>
            <a href="<?php echo url('equipos/' . $equipo['id'] . '/visitas/' . $visita['id']); ?>" class="btn btn-outline-primary">
                <i class="fas fa-eye me-1"></i>
                Ver Detalle
            </a>
        </div>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($_GET['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
$fechaValue = '';
if (!empty($visita['fecha_visita'])) {
    $ts = strtotime($visita['fecha_visita']);
    if ($ts !== false) {
        $fechaValue = date('Y-m-d\TH:i', $ts);
    }
}
?>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Datos de la Visita</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo url('equipos/' . $equipo['id'] . '/visitas/' . $visita['id'] . '/edit'); ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_visita" class="form-label">Fecha y Hora *</label>
                    <input type="datetime-local" class="form-control" id="fecha_visita" name="fecha_visita" required value="<?php echo htmlspecialchars($fechaValue); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tecnico_nombre" class="form-label">Técnico Asignado *</label>
                    <input type="text" class="form-control" id="tecnico_nombre" name="tecnico_nombre" maxlength="255" placeholder="Nombre del técnico que asistirá" required value="<?php echo htmlspecialchars($visita['tecnico_nombre'] ?? ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tipo_visita" class="form-label">Tipo de Visita *</label>
                    <select class="form-select" id="tipo_visita" name="tipo_visita" required>
                        <?php
                        $tipos = [
                            'mantenimiento' => 'Mantenimiento Preventivo',
                            'reparacion' => 'Reparación',
                            'instalacion' => 'Instalación',
                            'revision' => 'Revisión Técnica'
                        ];
                        $tipoActual = $visita['tipo_visita'] ?? '';
                        ?>
                        <option value="">Seleccionar tipo...</option>
                        <?php foreach ($tipos as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo ($tipoActual === $value) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="estado" class="form-label">Estado *</label>
                    <?php $estadoActual = $visita['estado'] ?? 'programada'; ?>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="programada" <?php echo ($estadoActual === 'programada') ? 'selected' : ''; ?>>Programada</option>
                        <option value="completada" <?php echo ($estadoActual === 'completada') ? 'selected' : ''; ?>>Completada</option>
                        <option value="cancelada" <?php echo ($estadoActual === 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control" id="observaciones" name="observaciones" rows="4" placeholder="Descripción de los trabajos a realizar o realizados..."><?php echo htmlspecialchars($visita['observaciones'] ?? ''); ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>

        <hr>

        <form method="POST" action="<?php echo url('equipos/' . $equipo['id'] . '/visitas/' . $visita['id'] . '/cancel'); ?>" onsubmit="return confirm('¿Deseas cancelar esta visita?');" class="mb-2">
            <button type="submit" class="btn btn-outline-warning" <?php echo (($visita['estado'] ?? '') === 'cancelada') ? 'disabled' : ''; ?>>
                <i class="fas fa-ban me-1"></i>
                Cancelar
            </button>
        </form>

        <form method="POST" action="<?php echo url('equipos/' . $equipo['id'] . '/visitas/' . $visita['id'] . '/delete'); ?>" onsubmit="return confirm('¿Seguro que deseas eliminar esta visita?');">
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>
                Eliminar
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('fecha_visita');
    if (!fechaInput.value) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        fechaInput.value = now.toISOString().slice(0, 16);
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>
