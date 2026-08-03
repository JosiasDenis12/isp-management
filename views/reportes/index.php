<?php include 'views/layouts/header.php'; ?>

<style>
    .reportes-page {
        max-width: 100%;
    }

    .reportes-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 0 0 .5rem;
    }

    .reportes-hero-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .reportes-hero-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 1.55rem;
        color: #5b49f7;
        background: linear-gradient(135deg, rgba(91, 73, 247, 0.12), rgba(91, 73, 247, 0.05));
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(91, 73, 247, 0.08);
        flex: 0 0 auto;
    }

    .reportes-hero-title {
        font-size: 1.9rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.08;
        color: #111827;
        margin: 0;
    }

    .reportes-hero-subtitle {
        margin-top: .35rem;
        color: rgba(17, 24, 39, 0.58);
        font-size: .98rem;
        line-height: 1.35;
    }

    .reportes-back-btn {
        height: 36px;
        padding-inline: 1rem;
        border-radius: 12px;
        border-color: rgba(15, 23, 42, 0.10);
        background: rgba(255, 255, 255, 0.94);
        color: rgba(15, 23, 42, 0.72);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .reportes-back-btn:hover {
        background: #fff;
        border-color: rgba(15, 23, 42, 0.14);
        color: #111827;
    }

    .reportes-surface {
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-right: 1px solid rgba(15, 23, 42, 0.12);
        border-radius: 1.25rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .reportes-surface-inner {
        padding: 1.25rem;
    }

    .reportes-summary {
        display: flex;
        align-items: flex-start;
        gap: .9rem;
        margin-bottom: 1rem;
    }

    .reportes-summary-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        color: #5b49f7;
        background: linear-gradient(135deg, rgba(91, 73, 247, 0.12), rgba(91, 73, 247, 0.05));
        border: 1px solid rgba(91, 73, 247, 0.08);
    }

    .reportes-summary-title {
        font-size: 1rem;
        font-weight: 800;
        color: #111827;
        margin: 0 0 .15rem;
    }

    .reportes-summary-text {
        margin: 0;
        color: rgba(17, 24, 39, 0.62);
        font-size: .96rem;
        line-height: 1.45;
    }

    .report-card {
        height: 100%;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-right: 1px solid rgba(15, 23, 42, 0.12);
        border-radius: 1.1rem;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
    }

    .report-card-body {
        padding: 1.25rem 1.15rem 1.1rem;
        display: flex;
        gap: .9rem;
        align-items: flex-start;
        height: 100%;
    }

    .report-card-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 1.35rem;
        flex: 0 0 auto;
    }

    .report-card-doodle {
        position: relative;
        width: 56px;
        height: 56px;
        border-radius: 16px;
        overflow: hidden;
        display: grid;
        place-items: center;
    }

    .report-card-doodle-wave {
        position: absolute;
        border-top: 2.5px solid currentColor;
        border-left: 2.5px solid transparent;
        border-right: 2.5px solid transparent;
        border-bottom: 0;
        border-radius: 999px 999px 0 0;
        opacity: .95;
    }

    .report-card-doodle-wave.wave-1 {
        width: 22px;
        height: 11px;
        top: 10px;
    }

    .report-card-doodle-wave.wave-2 {
        width: 30px;
        height: 16px;
        top: 6px;
        opacity: .75;
    }

    .report-card-doodle-wave.wave-3 {
        width: 38px;
        height: 20px;
        top: 2px;
        opacity: .42;
    }

    .report-card-doodle-router {
        position: absolute;
        bottom: 11px;
        width: 26px;
        height: 10px;
        border-radius: 999px;
        background: currentColor;
        box-shadow: 0 1px 0 rgba(255,255,255,.35) inset;
    }

    .report-card-doodle-router::before,
    .report-card-doodle-router::after {
        content: "";
        position: absolute;
        top: -6px;
        width: 2px;
        height: 9px;
        border-radius: 999px;
        background: currentColor;
    }

    .report-card-doodle-router::before {
        left: 5px;
        transform: rotate(-18deg);
        transform-origin: bottom center;
    }

    .report-card-doodle-router::after {
        right: 5px;
        transform: rotate(18deg);
        transform-origin: bottom center;
    }

    .report-card-doodle-router i {
        position: absolute;
        left: 50%;
        bottom: 12px;
        transform: translateX(-50%);
        font-size: .58rem;
        color: #ffffff;
    }

    .report-card-title {
        font-size: 1.03rem;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.02em;
        color: #111827;
        margin: 0;
    }

    .report-card-text {
        margin: .45rem 0 1rem;
        color: rgba(17, 24, 39, 0.66);
        font-size: .92rem;
        line-height: 1.5;
    }

    .report-card-link {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        min-width: 96px;
        height: 30px;
        padding: 0 .9rem;
        border-radius: 10px;
        border: 1px solid currentColor;
        background: rgba(255, 255, 255, 0.9);
        font-size: .85rem;
        font-weight: 500;
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        box-shadow: 0 1px 1px rgba(15, 23, 42, 0.03);
    }

    .report-card-link:hover {
        transform: translateY(-1px);
        background: #fff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
        text-decoration: none;
    }

    .report-card-link i {
        font-size: .88rem;
    }

    .report-card-body-purple .report-card-icon {
        color: #5b49f7;
        background: linear-gradient(135deg, rgba(91, 73, 247, 0.16), rgba(91, 73, 247, 0.05));
        border: 1px solid rgba(91, 73, 247, 0.08);
    }

    .report-card-body-purple .report-card-link {
        color: #5b49f7;
        border-color: rgba(91, 73, 247, 0.22);
        background: rgba(91, 73, 247, 0.04);
    }

    .report-card-body-blue .report-card-icon {
        color: #3b82f6;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.16), rgba(59, 130, 246, 0.05));
        border: 1px solid rgba(59, 130, 246, 0.08);
    }

    .report-card-body-blue .report-card-link {
        color: #3b82f6;
        border-color: rgba(59, 130, 246, 0.22);
        background: rgba(59, 130, 246, 0.04);
    }

    .report-card-body-green .report-card-icon {
        color: #22c55e;
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.16), rgba(34, 197, 94, 0.05));
        border: 1px solid rgba(34, 197, 94, 0.08);
    }

    .report-card-body-green .report-card-link {
        color: #22c55e;
        border-color: rgba(34, 197, 94, 0.22);
        background: rgba(34, 197, 94, 0.04);
    }

    .report-card-body-orange .report-card-icon {
        color: #fb923c;
        background: linear-gradient(135deg, rgba(251, 146, 60, 0.18), rgba(251, 146, 60, 0.06));
        border: 1px solid rgba(251, 146, 60, 0.08);
    }

    .report-card-body-orange .report-card-link {
        color: #fb923c;
        border-color: rgba(251, 146, 60, 0.26);
        background: rgba(251, 146, 60, 0.05);
    }

    .reportes-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .95rem 1.15rem;
    }

    .reportes-footer-text {
        margin: 0;
        color: rgba(17, 24, 39, 0.62);
        font-size: .94rem;
        line-height: 1.5;
    }

    .reportes-footer-pill {
        color: #ef4444;
        font-weight: 700;
        text-decoration: none;
    }

    .reportes-footer-pill:hover {
        text-decoration: underline;
    }

    .reportes-doc-btn {
        white-space: nowrap;
        border-radius: 12px;
        border-color: rgba(91, 73, 247, 0.16);
        color: #5b49f7;
        background: rgba(91, 73, 247, 0.04);
    }

    .reportes-doc-btn:hover {
        color: #4c3dd8;
        background: rgba(91, 73, 247, 0.08);
    }

    @media (max-width: 991.98px) {
        .reportes-hero {
            flex-direction: column;
        }

        .reportes-footer {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .reportes-surface-inner {
            padding: 1rem;
        }

        .reportes-hero-title {
            font-size: 1.55rem;
        }

        .report-card-body {
            padding: 1rem;
        }
    }
</style>

<?php
    $reporteCards = [
        [
            'title' => 'Reporte de Suscripciones',
            'text' => 'Detalles de suscripciones, planes pagados, vencimientos, días restantes y WhatsApp.',
            'icon' => 'fa-user',
            'tone' => 'purple',
            'url' => url('reportes/suscripciones'),
            'button' => 'Ver reporte',
            'arrow' => 'fa-arrow-right',
        ],
        [
            'title' => 'Reporte de Equipos Instalados',
            'text' => 'Antenas y módems con MAC, IP, SSID, credenciales y estados de conexión.',
            'icon' => 'fa-router',
            'tone' => 'blue',
            'illustration' => true,
            'url' => url('reportes/equipos-instalados'),
            'button' => 'Ver reporte',
            'arrow' => 'fa-arrow-right',
        ],
        [
            'title' => 'Reporte de Pagos',
            'text' => 'Ingresos, pagos pendientes, vencimientos y métodos de pago.',
            'icon' => 'fa-dollar-sign',
            'tone' => 'green',
            'url' => url('reportes/pagos'),
            'button' => 'Ver reporte',
            'arrow' => 'fa-arrow-right',
        ],
        [
            'title' => 'Reporte de Equipos y Visitas Técnicas',
            'text' => 'Registro de actividades, equipos involucrados, técnicos, estados y visitas realizadas.',
            'icon' => 'fa-wrench',
            'tone' => 'orange',
            'url' => url('reportes/equipos-visitas'),
            'button' => 'Ver reporte',
            'arrow' => 'fa-arrow-right',
        ],
    ];
?>

<div class="reportes-page">
    <div class="reportes-hero mb-3">
        <div class="reportes-hero-left">
            <div class="reportes-hero-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <h1 class="reportes-hero-title page-title">Reportes y Estadísticas</h1>
                <div class="reportes-hero-subtitle page-subtitle">Consulta y analiza los indicadores clave de tu sistema.</div>
            </div>
        </div>

        <a href="<?php echo url('dashboard'); ?>" class="btn btn-outline-secondary reportes-back-btn">
            <i class="fas fa-arrow-left me-2"></i>
            Volver al Dashboard
        </a>
    </div>

    <div class="reportes-surface mb-4">
        <div class="reportes-surface-inner">
            <div class="reportes-summary">
                <div class="reportes-summary-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <h2 class="reportes-summary-title">Resumen rápido</h2>
                    <p class="reportes-summary-text">Aquí encontrarás los reportes e indicadores más importantes del sistema. Selecciona un reporte para ver los detalles.</p>
                </div>
            </div>

            <div class="row g-3">
                <?php foreach ($reporteCards as $card): ?>
                    <div class="col-12 col-md-4">
                        <div class="report-card">
                            <div class="report-card-body report-card-body-<?php echo htmlspecialchars($card['tone']); ?>">
                                <?php if (!empty($card['illustration'])): ?>
                                    <div class="report-card-icon">
                                        <div class="report-card-doodle">
                                            <span class="report-card-doodle-wave wave-3"></span>
                                            <span class="report-card-doodle-wave wave-2"></span>
                                            <span class="report-card-doodle-wave wave-1"></span>
                                            <span class="report-card-doodle-router">
                                                <i class="fas fa-wifi"></i>
                                            </span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="report-card-icon">
                                        <i class="fas <?php echo htmlspecialchars($card['icon']); ?>"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <h3 class="report-card-title"><?php echo htmlspecialchars($card['title']); ?></h3>
                                    <p class="report-card-text"><?php echo htmlspecialchars($card['text']); ?></p>
                                    <a href="<?php echo $card['url']; ?>" class="report-card-link">
                                        <span><?php echo htmlspecialchars($card['button']); ?></span>
                                        <i class="fas <?php echo htmlspecialchars($card['arrow']); ?>"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="reportes-surface">
        <div class="reportes-footer">
            <p class="reportes-footer-text">
                Si deseas generar reportes PDF avanzados, revisa la documentación disponible en:
                <span class="reportes-footer-pill">lib/ReporteEquipoPDF.php</span>
                y
                <span class="reportes-footer-pill">REPORTES_README.md</span>
            </p>
            <a href="<?php echo url('reportes'); ?>" class="btn btn-outline-secondary reportes-doc-btn">
                Ver documentación <i class="fas fa-up-right-from-square ms-2"></i>
            </a>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
