<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-clipboard-check me-2"></i>
        Detalle de Visita
        <small class="text-muted">- <?php echo htmlspecialchars($equipo['marca'] . ' ' . $equipo['modelo']); ?></small>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo url('equipos/' . $equipo['id'] . '/visitas'); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Volver a Visitas
            </a>
            <a href="<?php echo url('equipos/' . $equipo['id'] . '/visitas/' . $visita['id'] . '/edit'); ?>" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>
                Editar
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-wifi me-2"></i>Equipo</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <strong>Cliente:</strong><br>
                <span class="text-muted"><?php echo htmlspecialchars($equipo['cliente_nombre']); ?></span>
            </div>
            <div class="col-md-4">
                <strong>Tipo:</strong><br>
                <span class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $equipo['tipo_equipo'])); ?></span>
            </div>
            <div class="col-md-4">
                <strong>Número de Serie:</strong><br>
                <span class="text-muted"><?php echo htmlspecialchars($equipo['numero_serie'] ?: 'No especificado'); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Visita</h6>
    </div>
    <div class="card-body">
        <?php
        $estadoClass = 'bg-secondary';
        switch ($visita['estado'] ?? '') {
            case 'completada':
                $estadoClass = 'bg-success';
                break;
            case 'programada':
                $estadoClass = 'bg-info';
                break;
            case 'cancelada':
                $estadoClass = 'bg-danger';
                break;
        }
        ?>

        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Fecha:</strong><br>
                <span class="text-muted"><?php echo date('d/m/Y H:i', strtotime($visita['fecha_visita'])); ?></span>
            </div>
            <div class="col-md-4">
                <strong>Técnico:</strong><br>
                <span class="text-muted"><?php echo htmlspecialchars($visita['tecnico_nombre'] ?? ''); ?></span>
            </div>
            <div class="col-md-4">
                <strong>Estado:</strong><br>
                <span class="badge <?php echo $estadoClass; ?>"><?php echo htmlspecialchars(ucfirst($visita['estado'] ?? '')); ?></span>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Tipo de visita:</strong><br>
                <span class="text-muted"><?php echo htmlspecialchars(ucfirst($visita['tipo_visita'] ?? '')); ?></span>
            </div>
            <div class="col-md-8">
                <strong>Observaciones:</strong><br>
                <span class="text-muted"><?php echo nl2br(htmlspecialchars($visita['observaciones'] ?? '')); ?></span>
            </div>
        </div>

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

<?php include 'views/layouts/footer.php'; ?>
