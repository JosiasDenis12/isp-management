<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-router me-2"></i>
        Equipos Técnicos
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo url('equipos/create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            Registrar Equipo
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
$totalEquipos = count($equipos);
$operativos = array_filter($equipos, function($e) { return $e['estado_tecnico'] === 'operativo'; });
$necesitanRevision = array_filter($equipos, function($e) { return $e['estado_tecnico'] === 'necesita_revision'; });
$dañados = array_filter($equipos, function($e) { return $e['estado_tecnico'] === 'dañado'; });
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body text-center">
                <h4><?php echo $totalEquipos; ?></h4>
                <p class="mb-0">Total Equipos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body text-center">
                <h4><?php echo count($operativos); ?></h4>
                <p class="mb-0">Operativos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body text-center">
                <h4><?php echo count($necesitanRevision); ?></h4>
                <p class="mb-0">Necesitan Revisión</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body text-center">
                <h4><?php echo count($dañados); ?></h4>
                <p class="mb-0">Dañados</p>
            </div>
        </div>
    </div>
</div>

<!-- Lista de equipos -->
<?php if (empty($equipos)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-router fa-4x text-muted mb-3"></i>
            <h3>No hay equipos registrados</h3>
            <p class="text-muted">Comienza registrando el primer equipo</p>
            <a href="/equipos/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Registrar Equipo
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($equipos as $equipo): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-wifi me-1"></i>
                            <?php echo htmlspecialchars($equipo['tipo_equipo']); ?>
                        </h6>
                        <?php
                        $badgeClass = '';
                        $icon = '';
                        switch ($equipo['estado_tecnico']) {
                            case 'operativo':
                                $badgeClass = 'bg-success';
                                $icon = 'fas fa-check';
                                break;
                            case 'necesita_revision':
                                $badgeClass = 'bg-warning';
                                $icon = 'fas fa-exclamation-triangle';
                                break;
                            case 'dañado':
                                $badgeClass = 'bg-danger';
                                $icon = 'fas fa-times';
                                break;
                        }
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>">
                            <i class="<?php echo $icon; ?> me-1"></i>
                            <?php echo ucwords(str_replace('_', ' ', $equipo['estado_tecnico'])); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary"><?php echo htmlspecialchars($equipo['cliente_nombre']); ?></h6>
                        
                        <div class="row mb-2">
                            <div class="col-6">
                                <small class="text-muted">Marca:</small><br>
                                <strong><?php echo htmlspecialchars($equipo['marca']); ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Modelo:</small><br>
                                <strong><?php echo htmlspecialchars($equipo['modelo']); ?></strong>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Número de serie:</small><br>
                            <code class="small"><?php echo htmlspecialchars($equipo['numero_serie']); ?></code>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Fecha instalación:</small><br>
                            <?php echo $equipo['fecha_instalacion'] ? date('d/m/Y', strtotime($equipo['fecha_instalacion'])) : 'No registrada'; ?>
                        </div>
                        
                        <?php if ($equipo['observaciones_tecnico']): ?>
                            <div class="mb-2">
                                <small class="text-muted">Observaciones:</small><br>
                                <div class="alert alert-info py-1 px-2 small">
                                    <?php echo htmlspecialchars($equipo['observaciones_tecnico']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group w-100" role="group">
                            <a href="<?php echo url('equipos/' . $equipo['id']); ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>
                                Ver
                            </a>
                            <a href="<?php echo url('equipos/' . $equipo['id'] . '/visitas'); ?>" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-tools me-1"></i>
                                Visitas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>
