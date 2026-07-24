<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-line me-2"></i>
        Reportes y Estadísticas
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo url('dashboard'); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver al Dashboard
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Resumen rápido</h5>
                <p class="card-text">Aquí irán los reportes e indicadores del sistema. Por ahora esta es una página placeholder para la sección de reportes.</p>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6>Reporte de Suscripciones</h6>
                                <p class="small text-muted">Fecha de contratación, plan, pagados, vencimiento, días restantes y WhatsApp.</p>
                                <a href="<?php echo url('reportes/suscripciones'); ?>" class="btn btn-sm btn-primary">Ver</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6>Reporte de Equipos Instalados</h6>
                                <p class="small text-muted">Antenas y módems con MAC, IP, SSID, credenciales y estado de acceso.</p>
                                <a href="<?php echo url('reportes/equipos-instalados'); ?>" class="btn btn-sm btn-primary">Ver</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6>Reporte de Pagos</h6>
                                <p class="small text-muted">Ingresos, pagos pendientes, vencimientos.</p>
                                <a href="<?php echo url('reportes/pagos'); ?>" class="btn btn-sm btn-primary">Ver</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6>Reporte de Equipos y Visitas Técnicas</h6>
                                <p class="small text-muted">Registro de actividades, equipos involucrados, técnicos, clientes y estado de visitas.</p>
                                <a href="<?php echo url('reportes/equipos-visitas'); ?>" class="btn btn-sm btn-primary">Ver</a>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <p class="text-muted small">Si quieres generar reportes PDF avanzados, revisa <code>lib/ReporteEquipoPDF.php</code> y <code>REPORTES_README.md</code>.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
