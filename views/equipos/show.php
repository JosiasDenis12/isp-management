<?php include 'views/layouts/header.php'; ?>

<?php
/**
 * Formatea fechas de forma segura.
 * Evita warnings/deprecations cuando SQLite devuelve NULL,
 * valores vacíos o fechas inválidas.
 */
$formatearFecha = function ($fecha, $formato = 'd/m/Y H:i', $textoVacio = 'No disponible') {
    if ($fecha === null || $fecha === '' || $fecha === '0000-00-00' || $fecha === '0000-00-00 00:00:00') {
        return $textoVacio;
    }

    $timestamp = strtotime((string)$fecha);

    if ($timestamp === false) {
        return $textoVacio;
    }

    return date($formato, $timestamp);
};

/**
 * Escape seguro para valores que puedan venir como NULL.
 */
$esc = function ($valor, $fallback = '') {
    if ($valor === null || $valor === '') {
        return $fallback;
    }

    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-wifi me-2"></i>
        <?php echo $esc(($equipo['marca'] ?? '') . ' ' . ($equipo['modelo'] ?? ''), 'Equipo'); ?>
    </h1>

    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo url('equipos'); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Volver a Equipos
            </a>

            <a href="<?php echo url('equipos/' . (int)$equipo['id'] . '/visitas'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-tools me-1"></i>
                Visitas
            </a>
        </div>

        <div class="btn-group">
            <a
                href="<?php echo url(
                    'equipos/create?cliente_id=' .
                    (int)($equipo['cliente_id'] ?? 0) .
                    '&fecha_instalacion=' .
                    urlencode($equipo['fecha_instalacion'] ?? date('Y-m-d'))
                ); ?>"
                class="btn btn-primary"
            >
                <i class="fas fa-plus me-1"></i>
                Registrar otro
            </a>

            <a
                href="<?php echo url('equipos/' . (int)$equipo['id'] . '/edit'); ?>"
                class="btn btn-outline-primary"
            >
                <i class="fas fa-pen me-1"></i>
                Editar
            </a>

            <button
                type="button"
                class="btn btn-outline-danger"
                data-bs-toggle="modal"
                data-bs-target="#eliminarEquipoModal"
            >
                <i class="fas fa-trash me-1"></i>
                Eliminar
            </button>

            <button
                type="button"
                class="btn btn-outline-warning"
                data-bs-toggle="modal"
                data-bs-target="#programarMantenimientoModal"
            >
                <i class="fas fa-calendar-plus me-1"></i>
                Programar Mantenimiento
            </button>
        </div>
    </div>
</div>


<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>¡Éxito!</strong>
        <?php echo $esc(urldecode($_GET['success'])); ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Close"
        ></button>
    </div>
<?php endif; ?>


<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Error:</strong>
        <?php echo $esc(urldecode($_GET['error'])); ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Close"
        ></button>
    </div>
<?php endif; ?>


<div class="row">

    <!-- INFORMACIÓN PRINCIPAL -->
    <div class="col-md-8">

        <div class="card mb-4">

            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-wifi me-2"></i>
                    <strong>Información del Equipo</strong>
                </div>

                <span class="badge bg-success">
                    <?php echo $esc(
                        ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $equipo['estado_tecnico'] ?? 'Sin estado'
                            )
                        )
                    ); ?>
                </span>
            </div>


            <div class="card-body">

                <div class="row">

                    <!-- COLUMNA IZQUIERDA -->
                    <div class="col-md-6">

                        <table class="table table-borderless">

                            <tr>
                                <td><strong>Cliente:</strong></td>
                                <td>
                                    <?php echo $esc(
                                        $equipo['cliente_nombre'] ?? null,
                                        '<span class="text-muted">No especificado</span>'
                                    ); ?>
                                </td>
                            </tr>


                            <tr>
                                <td><strong>Instalación:</strong></td>
                                <td>
                                    <?php
                                    if (!empty($equipo['instalacion_id'])) {
                                        echo 'Instalación #' . (int)$equipo['instalacion_id'];
                                    } else {
                                        echo '<span class="text-muted">Registro individual</span>';
                                    }
                                    ?>
                                </td>
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

                                    $tipoEquipo = $equipo['tipo_equipo'] ?? 'otro';

                                    $icono =
                                        $iconos[$tipoEquipo]
                                        ?? 'fas fa-question-circle';
                                    ?>

                                    <i class="<?php echo $icono; ?> me-2"></i>

                                    <?php
                                    echo $esc(
                                        ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $tipoEquipo
                                            )
                                        )
                                    );
                                    ?>
                                </td>
                            </tr>


                            <tr>
                                <td><strong>Marca:</strong></td>

                                <td>
                                    <?php echo $esc(
                                        $equipo['marca'] ?? null,
                                        '<span class="text-muted">No especificada</span>'
                                    ); ?>
                                </td>
                            </tr>


                            <tr>
                                <td><strong>Modelo:</strong></td>

                                <td>
                                    <?php echo $esc(
                                        $equipo['modelo'] ?? null,
                                        '<span class="text-muted">No especificado</span>'
                                    ); ?>
                                </td>
                            </tr>

                        </table>

                    </div>


                    <!-- COLUMNA DERECHA -->
                    <div class="col-md-6">

                        <table class="table table-borderless">

                            <tr>
                                <td><strong>Número de Serie:</strong></td>

                                <td>
                                    <?php
                                    if (!empty($equipo['numero_serie'])) {
                                        echo $esc($equipo['numero_serie']);
                                    } else {
                                        echo '<span class="text-muted">No especificado</span>';
                                    }
                                    ?>
                                </td>
                            </tr>


                            <tr>
                                <td><strong>Fecha de Instalación:</strong></td>

                                <td>
                                    <?php
                                    echo $formatearFecha(
                                        $equipo['fecha_instalacion'] ?? null,
                                        'd/m/Y',
                                        'No especificada'
                                    );
                                    ?>
                                </td>
                            </tr>


                            <tr>
                                <td><strong>Registrado:</strong></td>

                                <td>
                                    <?php
                                    echo $formatearFecha(
                                        $equipo['created_at'] ?? null,
                                        'd/m/Y H:i',
                                        'No disponible'
                                    );
                                    ?>
                                </td>
                            </tr>


                            <tr>
                                <td><strong>Última Actualización:</strong></td>

                                <td>
                                    <?php
                                    echo $formatearFecha(
                                        $equipo['updated_at'] ?? null,
                                        'd/m/Y H:i',
                                        'Sin actualizaciones'
                                    );
                                    ?>
                                </td>
                            </tr>

                        </table>

                    </div>

                </div>


                <?php if (!empty($equipo['observaciones_tecnico'])): ?>

                    <hr>

                    <div>
                        <strong>Observaciones Técnicas:</strong>

                        <p class="mt-2 mb-0">
                            <?php
                            echo nl2br(
                                $esc($equipo['observaciones_tecnico'])
                            );
                            ?>
                        </p>
                    </div>

                <?php endif; ?>


                <!-- INFORMACIÓN DE RED -->
                <?php
                $tieneInformacionRed =
                    !empty($equipo['mac_address']) ||
                    !empty($equipo['direccion_ip']) ||
                    !empty($equipo['password_acceso']) ||
                    !empty($equipo['ssid']) ||
                    !empty($equipo['usuario_acceso']);
                ?>

                <?php if ($tieneInformacionRed): ?>

                    <hr>

                    <div class="row">

                        <div class="col-12">

                            <h6 class="mb-3">
                                <i class="fas fa-network-wired me-2"></i>
                                Información de Red
                            </h6>


                            <div class="table-responsive">

                                <table class="table table-borderless">

                                    <?php if (!empty($equipo['mac_address'])): ?>
                                        <tr>
                                            <td width="200">
                                                <strong>MAC Address:</strong>
                                            </td>

                                            <td>
                                                <?php echo $esc($equipo['mac_address']); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>


                                    <?php if (!empty($equipo['direccion_ip'])): ?>
                                        <tr>
                                            <td>
                                                <strong>Dirección IP:</strong>
                                            </td>

                                            <td>
                                                <?php echo $esc($equipo['direccion_ip']); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>


                                    <?php if (!empty($equipo['password_acceso'])): ?>
                                        <tr>
                                            <td>
                                                <strong>Contraseña:</strong>
                                            </td>

                                            <td>
                                                <code>
                                                    <?php echo $esc($equipo['password_acceso']); ?>
                                                </code>
                                            </td>
                                        </tr>
                                    <?php endif; ?>


                                    <?php if (!empty($equipo['ssid'])): ?>
                                        <tr>
                                            <td>
                                                <strong>SSID:</strong>
                                            </td>

                                            <td>
                                                <?php echo $esc($equipo['ssid']); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>


                                    <?php if (!empty($equipo['usuario_acceso'])): ?>
                                        <tr>
                                            <td>
                                                <strong>Usuario:</strong>
                                            </td>

                                            <td>
                                                <?php echo $esc($equipo['usuario_acceso']); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>


                                    <tr>
                                        <td>
                                            <strong>Acceso:</strong>
                                        </td>

                                        <td>
                                            <span
                                                class="badge <?php echo !empty($equipo['acceso_habilitado']) ? 'bg-success' : 'bg-secondary'; ?>"
                                            >
                                                <?php
                                                echo !empty($equipo['acceso_habilitado'])
                                                    ? 'Activado'
                                                    : 'Desactivado';
                                                ?>
                                            </span>
                                        </td>
                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- HISTORIAL DE MANTENIMIENTO -->
        <div class="card">

            <div class="card-header">
                <i class="fas fa-history me-2"></i>
                <strong>Historial de Mantenimiento</strong>
            </div>


            <div class="card-body">

                <div class="timeline">

                    <!-- EQUIPO REGISTRADO -->
                    <div class="timeline-item">

                        <div class="timeline-marker bg-success"></div>

                        <div class="timeline-content">

                            <h6 class="timeline-title">
                                Equipo Registrado
                            </h6>

                            <p class="timeline-description">
                                El equipo fue registrado en el sistema
                            </p>

                            <small class="text-muted">
                                <?php
                                echo $formatearFecha(
                                    $equipo['created_at'] ?? null,
                                    'd/m/Y H:i',
                                    'Fecha no disponible'
                                );
                                ?>
                            </small>

                        </div>

                    </div>


                    <!-- INSTALACIÓN -->
                    <?php if (!empty($equipo['fecha_instalacion'])): ?>

                        <div class="timeline-item">

                            <div class="timeline-marker bg-info"></div>

                            <div class="timeline-content">

                                <h6 class="timeline-title">
                                    Instalación
                                </h6>

                                <p class="timeline-description">
                                    Equipo instalado en las instalaciones del cliente
                                </p>

                                <small class="text-muted">
                                    <?php
                                    echo $formatearFecha(
                                        $equipo['fecha_instalacion'],
                                        'd/m/Y',
                                        'Fecha no disponible'
                                    );
                                    ?>
                                </small>

                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- PRÓXIMO MANTENIMIENTO -->
                    <div class="timeline-item">

                        <div class="timeline-marker bg-warning"></div>

                        <div class="timeline-content">

                            <h6 class="timeline-title">
                                Próximo Mantenimiento
                            </h6>


                            <?php if (!empty($proximoMantenimiento)): ?>

                                <p class="timeline-description">

                                    Mantenimiento programado con

                                    <strong>
                                        <?php
                                        echo $esc(
                                            $proximoMantenimiento['tecnico_nombre'] ?? null,
                                            'Técnico no especificado'
                                        );
                                        ?>
                                    </strong>

                                </p>

                                <small class="text-muted">

                                    <?php
                                    echo $formatearFecha(
                                        $proximoMantenimiento['fecha_visita'] ?? null,
                                        'd/m/Y H:i',
                                        'Fecha no disponible'
                                    );
                                    ?>

                                </small>

                            <?php else: ?>

                                <p class="timeline-description">
                                    No hay mantenimientos programados
                                </p>

                                <small class="text-muted">
                                    Use “Programar Mantenimiento”
                                </small>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- TABLA DE MANTENIMIENTOS -->
                <?php if (!empty($mantenimientos)): ?>

                    <hr>

                    <h6 class="mb-3">
                        <i class="fas fa-wrench me-2"></i>
                        Mantenimientos Registrados
                    </h6>


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

                                        <td>
                                            <?php
                                            echo $formatearFecha(
                                                $m['fecha_visita'] ?? null,
                                                'd/m/Y H:i',
                                                'No disponible'
                                            );
                                            ?>
                                        </td>


                                        <td>
                                            <?php
                                            echo $esc(
                                                $m['tecnico_nombre'] ?? null,
                                                'No especificado'
                                            );
                                            ?>
                                        </td>


                                        <td>

                                            <?php
                                            $estado =
                                                $m['estado']
                                                ?? '';

                                            $estadoClass =
                                                'bg-secondary';

                                            if ($estado === 'programada') {
                                                $estadoClass = 'bg-info';
                                            }

                                            if ($estado === 'pendiente') {
                                                $estadoClass = 'bg-secondary';
                                            }

                                            if ($estado === 'completada') {
                                                $estadoClass = 'bg-success';
                                            }

                                            if ($estado === 'cancelada') {
                                                $estadoClass = 'bg-danger';
                                            }

                                            if ($estado === 'reprogramada') {
                                                $estadoClass =
                                                    'bg-warning text-dark';
                                            }
                                            ?>

                                            <span class="badge <?php echo $estadoClass; ?>">
                                                <?php
                                                echo $esc(
                                                    ucfirst(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $estado
                                                        )
                                                    ),
                                                    'Sin estado'
                                                );
                                                ?>
                                            </span>

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


    <!-- COLUMNA DERECHA -->
    <div class="col-md-4">

        <!-- ESTADO DEL EQUIPO -->
        <div class="card mb-4">

            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i>
                <strong>Estado del Equipo</strong>
            </div>


            <div class="card-body">

                <div class="text-center">

                    <div class="status-circle <?php echo $esc($equipo['estado_tecnico'] ?? ''); ?> mb-3">

                        <?php
                        $iconosEstado = [
                            'operativo' => 'fas fa-check',
                            'necesita_revision' => 'fas fa-exclamation-triangle',
                            'fuera_de_servicio' => 'fas fa-times',
                            'en_mantenimiento' => 'fas fa-wrench'
                        ];

                        $estadoEquipo =
                            $equipo['estado_tecnico']
                            ?? '';

                        $iconoEstado =
                            $iconosEstado[$estadoEquipo]
                            ?? 'fas fa-question';
                        ?>

                        <i class="<?php echo $iconoEstado; ?> fa-2x"></i>

                    </div>


                    <h5>
                        <?php
                        echo $esc(
                            ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $estadoEquipo
                                )
                            ),
                            'Sin estado'
                        );
                        ?>
                    </h5>


                    <p class="text-muted mb-0">

                        <?php
                        $mensajesEstado = [
                            'operativo' =>
                                'El equipo está funcionando correctamente',

                            'necesita_revision' =>
                                'El equipo requiere revisión técnica',

                            'fuera_de_servicio' =>
                                'El equipo se encuentra fuera de servicio',

                            'en_mantenimiento' =>
                                'El equipo se encuentra en mantenimiento'
                        ];

                        echo $esc(
                            $mensajesEstado[$estadoEquipo]
                            ?? 'Estado no especificado'
                        );
                        ?>

                    </p>

                </div>

            </div>

        </div>


        <!-- ACCIONES RÁPIDAS -->
        <div class="card mb-4">

            <div class="card-header">
                <i class="fas fa-bolt me-2"></i>
                <strong>Acciones Rápidas</strong>
            </div>


            <div class="card-body">

                <div class="d-grid gap-2">

                    <a
                        href="<?php echo url('equipos/' . (int)$equipo['id'] . '/edit'); ?>"
                        class="btn btn-outline-primary"
                    >
                        <i class="fas fa-exchange-alt me-2"></i>
                        Cambiar Estado
                    </a>


                    <a
                        href="<?php echo url('equipos/' . (int)$equipo['id'] . '/visitas'); ?>"
                        class="btn btn-outline-info"
                    >
                        <i class="fas fa-tools me-2"></i>
                        Ver Visitas Técnicas
                    </a>


                    <button
                        type="button"
                        class="btn btn-outline-warning"
                        data-bs-toggle="modal"
                        data-bs-target="#programarMantenimientoModal"
                    >
                        <i class="fas fa-calendar-plus me-2"></i>
                        Programar Mantenimiento
                    </button>


                    <a
                        href="<?php echo url('reportes'); ?>"
                        class="btn btn-outline-success"
                    >
                        <i class="fas fa-file-alt me-2"></i>
                        Generar Reporte
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>


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
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px rgba(0,0,0,.1);
}

.timeline-content {
    background: #f8f9fa;
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

.status-circle {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-circle.operativo {
    background: #198754;
    color: #fff;
}

.status-circle.necesita_revision {
    background: #ffc107;
    color: #212529;
}

.status-circle.fuera_de_servicio {
    background: #dc3545;
    color: #fff;
}

.status-circle.en_mantenimiento {
    background: #0dcaf0;
    color: #fff;
}

</style>


<!-- CONFIRMACIÓN DE ELIMINACIÓN -->
<div
    class="modal fade"
    id="eliminarEquipoModal"
    tabindex="-1"
    aria-labelledby="eliminarEquipoLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header border-0 pb-0">

                <h5
                    class="modal-title"
                    id="eliminarEquipoLabel"
                >
                    <i class="fas fa-triangle-exclamation text-danger me-2"></i>
                    Eliminar equipo
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>


            <div class="modal-body pt-3">

                <p class="mb-1">

                    ¿Seguro que deseas eliminar

                    <strong>
                        <?php
                        echo $esc(
                            ($equipo['marca'] ?? '') .
                            ' ' .
                            ($equipo['modelo'] ?? ''),
                            'este equipo'
                        );
                        ?>
                    </strong>?

                </p>

                <p class="small text-muted mb-0">
                    Esta acción eliminará el equipo del inventario y no se puede deshacer.
                </p>

            </div>


            <div class="modal-footer border-0 pt-0">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>


                <form
                    method="POST"
                    action="<?php echo url('equipos/' . (int)$equipo['id'] . '/delete'); ?>"
                >

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        <i class="fas fa-trash me-1"></i>
                        Eliminar equipo
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>


<!-- MODAL PROGRAMAR MANTENIMIENTO -->
<div
    class="modal fade"
    id="programarMantenimientoModal"
    tabindex="-1"
    aria-labelledby="programarMantenimientoLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="programarMantenimientoLabel"
                >
                    <i class="fas fa-calendar-plus me-2"></i>
                    Programar Mantenimiento
                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>


            <form
                method="POST"
                action="<?php echo url('equipos/' . (int)$equipo['id'] . '/programar-mantenimiento'); ?>"
            >

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label
                                for="fecha_visita"
                                class="form-label"
                            >
                                Fecha y Hora *
                            </label>

                            <input
                                type="datetime-local"
                                class="form-control"
                                id="fecha_visita"
                                name="fecha_visita"
                                required
                            >

                        </div>


                        <div class="col-md-6 mb-3">

                            <label
                                for="tecnico_nombre"
                                class="form-label"
                            >
                                Técnico *
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="tecnico_nombre"
                                name="tecnico_nombre"
                                maxlength="255"
                                placeholder="Nombre del técnico que asistirá"
                                required
                            >

                        </div>

                    </div>


                    <div class="mb-3">

                        <label
                            for="observaciones"
                            class="form-label"
                        >
                            Observaciones
                        </label>

                        <textarea
                            class="form-control"
                            id="observaciones"
                            name="observaciones"
                            rows="4"
                            placeholder="Detalle del mantenimiento programado..."
                        ></textarea>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cerrar
                    </button>


                    <button
                        type="submit"
                        class="btn btn-warning"
                    >
                        <i class="fas fa-save me-1"></i>
                        Programar
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const fechaInput =
        document.getElementById('fecha_visita');

    if (!fechaInput) {
        return;
    }

    const now = new Date();

    now.setMinutes(
        now.getMinutes() -
        now.getTimezoneOffset()
    );

    fechaInput.min =
        now.toISOString().slice(0, 16);

});
</script>


<?php include 'views/layouts/footer.php'; ?>