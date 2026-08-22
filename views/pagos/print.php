<?php
// Soporta impresión de factura completa o ticket compacto.
$type = isset($_GET['type']) ? $_GET['type'] : 'invoice';
$esPagoEfectivo = strtolower(trim((string)($pago['metodo_pago'] ?? ''))) === 'efectivo';
$montoRecibido = isset($pago['monto_recibido']) && is_numeric($pago['monto_recibido'])
    ? (float)$pago['monto_recibido']
    : null;
$mostrarDesgloseEfectivo = $esPagoEfectivo && $montoRecibido !== null && $montoRecibido >= (float)$pago['monto'];

// Logo de Sky Network en SVG (nube + wifi), coincide con la identidad de marca.
// Se arma con dos sub-SVG anidados (arcos wifi + nube) para garantizar que
// las curvas se rendericen correctamente en cualquier navegador/impresora.
function skyNetworkLogoSVG($color = '#0B1F3A', $size = 60) {
    $h = round($size * 0.86);
    return '<svg width="' . $size . '" height="' . $h . '" viewBox="0 0 100 86" xmlns="http://www.w3.org/2000/svg">
        <svg x="26" y="0" width="48" height="26" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2.4" stroke-linecap="round">
            <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
            <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
            <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
            <circle cx="12" cy="20" r="1.4" fill="' . $color . '" stroke="none"/>
        </svg>
        <svg x="4" y="24" width="92" height="62" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>
        </svg>
    </svg>';
}

if ($type === 'ticket') {
    // Layout compacto para ticket/nota de compra.
    // IMPORTANTE: el bloque .ticket y sus estilos (incluido @media print) se
    // mantienen EXACTAMENTE igual al original -> el formato/dimensiones de
    // impresión térmica (58mm) no cambian. Solo se rediseña el "shell" de
    // vista previa en pantalla (encabezado, barra de acciones, fondo, modal).
    $backUrl = function_exists('url') ? url('pagos/' . $pago['id']) : '#';
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ticket <?php echo htmlspecialchars($pago['numero_factura']); ?></title>
        <style>
            /* ============================================================
               1) ESTILOS DEL TICKET (NO SE MODIFICAN) — verdad de impresión
               ============================================================ */
            :root { --ink: #000; --line: #000; }
            @page { size: 58mm auto; margin: 0 !important; }
            * { box-sizing: border-box; }
            html { width: 58mm; margin: 0; padding: 0; background: #fff; }
            body {
                width: 58mm;
                margin: 0 auto;
                padding: 5mm;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 10pt;
                font-weight: 500;
                line-height: 1.28;
                color: #000;
                background: #f4f4f4;
            }
            .ticket { width: 48mm; max-width: 100%; min-width: 0; margin: 0 auto; padding: 0; background: #fff; }
            .center { text-align: center; }
            .divider { border: none; border-top: .25mm dashed var(--line); margin: 3mm 0; }
            .brand-mark { margin: 0 0 1.2mm; line-height: 0; }
            .brand-name { font-family: Georgia, 'Times New Roman', serif; font-weight: 900; letter-spacing: .7mm; font-size: 15pt; line-height: 1.08; color: #000; }
            .brand-tagline { font-size: 9pt; font-weight: 700; letter-spacing: .2mm; margin-top: 1.2mm; }
            .doc-title { display: inline-block; max-width: 100%; border: .35mm solid #000; padding: 1mm 1.2mm; font-size: 7.2pt; line-height: 1; font-weight: 900; letter-spacing: .12mm; white-space: nowrap; }
            .meta-row { display: grid; grid-template-columns: minmax(0, 42%) minmax(0, 58%); gap: 0; margin: 1.4mm 0; align-items: baseline; }
            .meta-label { font-size: 8.5pt; font-weight: 800; }
            .meta-value { min-width: 0; text-align: right; font-size: 9pt; font-weight: 700; overflow-wrap: anywhere; }
            .customer { margin-top: 2.5mm; }
            .customer-label, .section-label { display: block; font-size: 8pt; font-weight: 900; letter-spacing: .2mm; text-transform: uppercase; }
            .customer-name { display: block; margin-top: .7mm; font-size: 12pt; font-weight: 900; line-height: 1.12; overflow-wrap: anywhere; }
            .item-title { font-size: 10pt; font-weight: 900; }
            .item-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 2mm; align-items: end; }
            .item-price { font-size: 10pt; font-weight: 900; text-align: right; overflow-wrap: anywhere; }
            .total-box { border-top: .6mm solid #000; border-bottom: .6mm solid #000; padding: 2mm 0; }
            .total-row { display: grid; grid-template-columns: minmax(0, 42%) minmax(0, 58%); align-items: baseline; gap: 0; }
            .total-label { font-size: 10pt; font-weight: 900; letter-spacing: .3mm; }
            .amount { min-width: 0; max-width: 100%; font-size: 16pt; line-height: 1; font-weight: 900; text-align: right; overflow-wrap: anywhere; }
            .cash-breakdown { padding: 2mm 0 0; }
            .cash-row { display: grid; grid-template-columns: minmax(0, 42%) minmax(0, 58%); align-items: baseline; margin-top: 1.2mm; }
            .cash-label { font-size: 8.5pt; font-weight: 800; letter-spacing: .1mm; }
            .cash-value { min-width: 0; font-size: 10pt; font-weight: 800; text-align: right; overflow-wrap: anywhere; }
            .note { margin: 0; font-size: 8.5pt; font-weight: 600; white-space: pre-wrap; overflow-wrap: anywhere; }
            .thank-you { font-size: 9pt; font-weight: 800; letter-spacing: .1mm; }
            .small { font-size: 8.5pt; font-weight: 600; color: #000; overflow-wrap: anywhere; }
            /* ===============================
   CIERRE DEL TICKET
   Compatible con ancho útil POS58
================================ */

            .thank-you {
                width: 100%;
                text-align: center;
                font-size: 9px;
                font-weight: 700;
                letter-spacing: 0.5px;
                margin: 6px 0 8px;
                line-height: 1.3;
                box-sizing: border-box;
            }

            .attention-hours {
                width: 100%;
                margin: 0;
                padding: 5px 0 2px;
                box-sizing: border-box;
            }

            .attention-header {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 3px;
                margin-bottom: 5px;
                box-sizing: border-box;
            }

            .attention-line {
                flex: 1;
                min-width: 0;
                height: 1px;
                background: #000;
            }

            .attention-title {
                flex-shrink: 1;
                min-width: 0;
                white-space: nowrap;
                font-size: 8px;
                font-weight: 900;
                letter-spacing: 0.5px;
                line-height: 1.2;
            }

            .attention-row {
                width: 100%;
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                gap: 4px;
                padding: 3px 0;
                box-sizing: border-box;

                font-size: 9px;
                line-height: 1.4;
            }

            .attention-row .day {
                min-width: 0;
                font-weight: 700;
            }

            .attention-row .time {
                flex-shrink: 0;
                white-space: nowrap;
                text-align: right;
                font-weight: 800;
            }

            @media print {

    @page {
        size: 58mm auto;
        margin: 0;
    }

    html,
    body {
        width: 58mm !important;
        min-width: 58mm !important;
        max-width: 58mm !important;

        margin: 0 !important;
        padding: 0 !important;

        background: #fff !important;
        overflow: hidden !important;

        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box !important;

        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    .no-print {
        display: none !important;
    }

    .ticket-stage {
        width: 58mm !important;
        margin: 0 !important;
        padding: 0 !important;

        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
    }

    .ticket {
        width: 48mm !important;
        min-width: 48mm !important;
        max-width: 48mm !important;

        /* Pequeña zona de seguridad física */
        margin-left: 1mm !important;
        margin-right: 0 !important;

        padding: 0 !important;

        box-sizing: border-box !important;

        box-shadow: none !important;
        border: none !important;

        /* IMPORTANTE: no desplazar hacia la izquierda */
        transform: none !important;
    }
}

            /* ============================================================
               2) SHELL DE VISTA PREVIA (solo pantalla, no imprime)
               ============================================================ */
            @media screen {
                body {
                    width: 100%;
                    padding: 0;
                    background: #f4f5f7;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Arial, sans-serif;
                }
                .page-shell { max-width: 1100px; margin: 0 auto; padding: 32px 24px 64px; }

                .page-header {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 20px;
                    flex-wrap: wrap;
                    margin-bottom: 28px;
                }
                .header-left { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
                .btn-back {
                    display: inline-flex; align-items: center; gap: 6px;
                    background: #fff; color: #3730a3; text-decoration: none;
                    font-size: 13px; font-weight: 700;
                    padding: 9px 16px; border-radius: 999px;
                    border: 1px solid #e5e7eb;
                    box-shadow: 0 1px 2px rgba(16,24,40,.04);
                    transition: background .15s ease, box-shadow .15s ease;
                }
                .btn-back:hover { background: #f5f6ff; box-shadow: 0 2px 6px rgba(16,24,40,.08); }
                .btn-back svg { width: 15px; height: 15px; }

                .header-titles h1 { margin: 0; font-size: 21px; font-weight: 800; color: #0f172a; letter-spacing: -.2px; }
                .header-titles p { margin: 3px 0 0; font-size: 12.5px; color: #6b7280; }

                .btn-print-main {
                    display: inline-flex; align-items: center; gap: 8px;
                    background: #0B1F3A; color: #fff; border: none;
                    padding: 11px 20px; border-radius: 9px;
                    font-size: 13px; font-weight: 700; letter-spacing: .2px;
                    cursor: pointer; box-shadow: 0 4px 12px rgba(11,31,58,.18);
                    transition: background .15s ease, transform .1s ease;
                }
                .btn-print-main:hover { background: #16294a; }
                .btn-print-main:active { transform: translateY(1px); }
                .btn-print-main svg { width: 16px; height: 16px; }

                .page-body { display: flex; align-items: flex-start; gap: 32px; }

                .action-rail { display: flex; flex-direction: column; gap: 14px; width: 96px; flex-shrink: 0; }
                .rail-btn {
                    display: flex; flex-direction: column; align-items: center; gap: 8px;
                    background: #fff; border: 1px solid #e9eaef; border-radius: 14px;
                    padding: 14px 6px 12px; cursor: pointer;
                    box-shadow: 0 1px 2px rgba(16,24,40,.04);
                    transition: box-shadow .15s ease, border-color .15s ease, transform .1s ease;
                    font-family: inherit;
                }
                .rail-btn:hover { border-color: #c7d2fe; box-shadow: 0 4px 14px rgba(79,70,229,.12); }
                .rail-btn:active { transform: translateY(1px); }
                .rail-icon {
                    width: 42px; height: 42px; border-radius: 11px;
                    background: #EEF0FF; color: #4F46E5;
                    display: flex; align-items: center; justify-content: center;
                }
                .rail-icon svg { width: 19px; height: 19px; }
                .rail-label { font-size: 11px; font-weight: 700; color: #374151; text-align: center; line-height: 1.25; }

                .preview-area { flex: 1; display: flex; justify-content: center; padding-top: 4px; }
                .ticket-stage {
                    background: #fff;
                    border-radius: 14px;
                    padding: 22px 20px 26px;
                    box-shadow: 0 12px 36px rgba(15,23,42,.09), 0 2px 8px rgba(15,23,42,.05);
                    border: 1px solid #eef0f3;
                }

                /* Modal enviar por email */
                .modal-overlay {
                    position: fixed; inset: 0; background: rgba(15,23,42,.45);
                    display: flex; align-items: center; justify-content: center;
                    z-index: 50; padding: 16px;
                }
                .modal-overlay.hidden { display: none; }
                .modal-box {
                    background: #fff; border-radius: 16px; width: 100%; max-width: 340px;
                    padding: 22px; box-shadow: 0 24px 60px rgba(0,0,0,.25);
                }
                .modal-box h3 { margin: 0 0 4px; font-size: 15px; color: #0f172a; }
                .modal-box p.hint { margin: 0 0 14px; font-size: 12px; color: #6b7280; }
                .modal-box input[type="email"] {
                    width: 100%; padding: 10px 12px; border-radius: 8px;
                    border: 1px solid #d1d5db; font-size: 13px; outline: none;
                    box-sizing: border-box;
                }
                .modal-box input[type="email"]:focus { border-color: #4F46E5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
                .modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; }
                .btn-secondary, .btn-primary {
                    border: none; border-radius: 8px; padding: 9px 16px;
                    font-size: 12.5px; font-weight: 700; cursor: pointer;
                }
                .btn-secondary { background: #f3f4f6; color: #374151; }
                .btn-secondary:hover { background: #e5e7eb; }
                .btn-primary { background: #4F46E5; color: #fff; }
                .btn-primary:hover { background: #4338ca; }

                /* Toast */
                .toast {
                    position: fixed; left: 50%; bottom: 28px; transform: translate(-50%, 12px);
                    background: #0f172a; color: #fff; font-size: 13px; font-weight: 600;
                    padding: 11px 18px; border-radius: 999px; box-shadow: 0 10px 30px rgba(0,0,0,.25);
                    opacity: 0; pointer-events: none; transition: opacity .2s ease, transform .2s ease;
                    z-index: 60;
                }
                .toast.show { opacity: 1; transform: translate(-50%, 0); }

                @media (max-width: 640px) {
                    .page-body { flex-direction: column; }
                    .action-rail { flex-direction: row; width: 100%; overflow-x: auto; }
                    .rail-btn { flex: 1; min-width: 78px; }
                    .preview-area { width: 100%; }
                }
            }
        </style>
    </head>
    <body>
        <div class="page-shell">
            <header class="page-header no-print">
                <div class="header-left">
                    <a href="<?php echo htmlspecialchars($backUrl); ?>" class="btn-back">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        Volver
                    </a>
                    <div class="header-titles">
                        <h1>Ticket <?php echo htmlspecialchars($pago['numero_factura']); ?></h1>
                        <p>Vista previa del ticket / nota de compra</p>
                    </div>
                </div>
                <button class="btn-print-main" onclick="printTicket()" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Imprimir Ticket
                </button>
            </header>

            <div class="page-body">
                <aside class="action-rail no-print">
                    <button class="rail-btn" onclick="printTicket()" type="button">
                        <span class="rail-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg></span>
                        <span class="rail-label">Imprimir</span>
                    </button>
                    <button class="rail-btn" onclick="downloadPDF()" type="button">
                        <span class="rail-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></span>
                        <span class="rail-label">Descargar<br>PDF</span>
                    </button>
                    <button class="rail-btn" onclick="openEmailModal()" type="button">
                        <span class="rail-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span>
                        <span class="rail-label">Enviar por<br>Email</span>
                    </button>
                    <button class="rail-btn" onclick="shareTicket()" type="button">
                        <span class="rail-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg></span>
                        <span class="rail-label">Compartir</span>
                    </button>
                </aside>

                <main class="preview-area">
                    <div class="ticket-stage" id="ticketPrintArea">
                        <!-- ===================== TICKET (formato intacto) ===================== -->
                        <div class="ticket">
                            <div class="center brand-mark"><?php echo skyNetworkLogoSVG('#000000', 54); ?></div>
                            <div class="center brand-name">SKY NETWORK</div>
                            <div class="center brand-tagline">La Red del Cielo</div>
                            <div class="divider"></div>
                            <div class="center doc-title">TICKET&nbsp;/&nbsp;NOTA&nbsp;DE&nbsp;COMPRA</div>
                            <div class="divider"></div>
                            <div class="meta-row"><span class="meta-label">Factura</span><span class="meta-value"><?php echo htmlspecialchars($pago['numero_factura']); ?></span></div>
                            <div class="meta-row"><span class="meta-label">Fecha</span><span class="meta-value"><?php echo date('d/m/Y H:i', strtotime($pago['created_at'])); ?></span></div>
                            <div class="customer"><span class="customer-label">Cliente</span><span class="customer-name"><?php echo htmlspecialchars($pago['cliente_nombre']); ?></span></div>
                            <div class="divider"></div>
                            <div class="section-label">Concepto</div>
                            <div class="item-row"><span class="item-title">Servicio de Internet</span><span class="item-price">$<?php echo number_format($pago['monto'], 2); ?></span></div>
                            <div class="divider"></div>
                            <div class="total-box"><div class="total-row"><span class="total-label">TOTAL PAGADO</span><span class="amount">$<?php echo number_format($pago['monto'], 2); ?></span></div></div>
                            <?php if ($mostrarDesgloseEfectivo): ?>
                                <div class="cash-breakdown">
                                    <div class="cash-row"><span class="cash-label">PAGO CON</span><span class="cash-value">$<?php echo number_format($montoRecibido, 2); ?></span></div>
                                    <div class="cash-row"><span class="cash-label">CAMBIO</span><span class="cash-value">$<?php echo number_format($montoRecibido - (float)$pago['monto'], 2); ?></span></div>
                                </div>
                            <?php endif; ?>
                            <div class="divider"></div>
                            <div class="small">Método de pago: <?php echo ucfirst($pago['metodo_pago']); ?></div>
                            <?php if (!empty($pago['observaciones'])): ?>
                                <div class="divider"></div>
                                <span class="section-label">Observaciones</span>
                                <p class="note"><?php echo htmlspecialchars($pago['observaciones']); ?></p>
                            <?php endif; ?>
                            <div class="divider"></div>

                    <div class="thank-you">
                        ¡Gracias por su preferencia!
                    </div>

                    <div class="attention-hours">

                        <div class="attention-header">
                            <span class="attention-line"></span>
                            <span class="attention-title">HORARIO DE ATENCIÓN</span>
                            <span class="attention-line"></span>
                        </div>

                        <div class="attention-row">
                            <span class="day">Lunes - Viernes</span>
                            <span class="time">9:00 AM - 5:00 PM</span>
                        </div>

                        <div class="attention-row">
                            <span class="day">Sábado y Domingo</span>
                            <span class="time">9:00 AM - 2:00 PM</span>
                        </div>

                    </div>
            

        <!-- Modal: enviar por email -->
        <div id="emailModal" class="modal-overlay hidden no-print">
            <div class="modal-box">
                <h3>Enviar ticket por email</h3>
                <p class="hint">Se enviará el ticket <?php echo htmlspecialchars($pago['numero_factura']); ?> a la dirección indicada.</p>
                <input type="email" id="emailInput" placeholder="cliente@correo.com" autocomplete="off">
                <div class="modal-actions">
                    <button class="btn-secondary" type="button" onclick="closeEmailModal()">Cancelar</button>
                    <button class="btn-primary" type="button" onclick="confirmSendEmail()">Enviar</button>
                </div>
            </div>
        </div>

        <div id="toast" class="toast no-print"></div>

        <!-- html2pdf.js: convierte el nodo del ticket a PDF conservando su ancho real -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script>
            const NUMERO_FACTURA = <?php echo json_encode($pago['numero_factura']); ?>;

            function showToast(msg) {
                const t = document.getElementById('toast');
                t.textContent = msg;
                t.classList.add('show');
                clearTimeout(window._toastTimer);
                window._toastTimer = setTimeout(() => t.classList.remove('show'), 2600);
            }

            function printTicket() {
                window.print();
            }

            function downloadPDF() {
                const el = document.querySelector('#ticketPrintArea .ticket');
                if (!el || typeof html2pdf === 'undefined') {
                    showToast('No se pudo cargar el generador de PDF');
                    return;
                }
                showToast('Generando PDF…');
                // Alto dinámico en mm a partir del contenido real del ticket (58mm de ancho fijo).
                const heightMM = Math.max(60, (el.offsetHeight / 3.7795) + 6);
                html2pdf().set({
                    margin: 0,
                    filename: 'Ticket-' + NUMERO_FACTURA + '.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 3, backgroundColor: '#ffffff' },
                    jsPDF: { unit: 'mm', format: [58, heightMM], orientation: 'portrait' }
                }).from(el).save().then(() => {
                    showToast('PDF descargado ✓');
                }).catch(() => {
                    showToast('Ocurrió un error generando el PDF');
                });
            }

            function openEmailModal() {
                document.getElementById('emailModal').classList.remove('hidden');
                setTimeout(() => document.getElementById('emailInput').focus(), 50);
            }
            function closeEmailModal() {
                document.getElementById('emailModal').classList.add('hidden');
            }

            function confirmSendEmail() {
                const input = document.getElementById('emailInput');
                const email = input.value.trim();
                if (!email || !email.includes('@') || !email.includes('.')) {
                    showToast('Ingresa un correo válido');
                    return;
                }
                closeEmailModal();
                showToast('Enviando ticket…');

                // NOTA PARA ANA: crea este endpoint en tu servidor y conéctalo a tu
                // herramienta PHPMailer existente. Debe recibir {numero_factura, email}
                // y responder JSON: {"success": true|false, "message": "..."}
                fetch('enviar-ticket-email.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ numero_factura: NUMERO_FACTURA, email })
                })
                .then(r => r.json())
                .then(data => {
                    showToast(data && data.success ? 'Ticket enviado ✓' : (data && data.message ? data.message : 'No se pudo enviar el ticket'));
                })
                .catch(() => {
                    showToast('Endpoint de envío no configurado aún');
                });

                input.value = '';
            }

            function shareTicket() {
                const shareUrl = window.location.href;
                if (navigator.share) {
                    navigator.share({ title: 'Ticket ' + NUMERO_FACTURA, url: shareUrl }).catch(() => {});
                } else if (navigator.clipboard) {
                    navigator.clipboard.writeText(shareUrl).then(() => showToast('Enlace copiado ✓'));
                } else {
                    showToast('No se pudo compartir en este navegador');
                }
            }

            // Cerrar modal con click fuera o tecla Escape
            document.getElementById('emailModal').addEventListener('click', function (e) {
                if (e.target === this) closeEmailModal();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeEmailModal();
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?php echo htmlspecialchars($pago['numero_factura']); ?></title>
    <style>
        :root {
            --ink: #0B1F3A;      /* azul noche, del logo */
            --ink-soft: #26385c;
            --gold: #B8912F;     /* acento elegante */
            --paper: #ffffff;
            --bg: #eef0f3;
            --line: #e3e5e9;
            --muted: #6b7280;
            --ok-bg: #e7f6ec; --ok-fg: #1c7a3e;
            --warn-bg: #fdf3e0; --warn-fg: #9a6b0a;
            --bad-bg: #fbe9e9; --bad-fg: #a4302f;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 13px;
            line-height: 1.55;
            color: #2b2f36;
            margin: 0;
            padding: 32px 16px;
            background: var(--bg);
        }
        .invoice-container {
            max-width: 850px;
            margin: 0 auto;
            background: var(--paper);
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 6px 24px rgba(11,31,58,0.10);
        }
        .clearfix::after { content:""; display:table; clear:both; }

        /* ---------- Header ---------- */
        .invoice-header {
            background: var(--ink);
            color: #f5f6f8;
            padding: 36px 40px 30px;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 24px;
        }
        .invoice-header::after{
            content:"";
            position:absolute; left:0; right:0; bottom:0;
            height:4px;
            background: linear-gradient(90deg, var(--gold), transparent);
        }
        .company-info { display:flex; align-items:center; gap:16px; }
        .company-logo { flex: 0 0 auto; }
        .company-logo svg { display:block; }
        .company-name {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #ffffff;
            margin-bottom: 3px;
        }
        .company-tagline {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-style: italic;
            font-size: 13px;
            color: var(--gold);
            margin-bottom: 10px;
        }
        .company-contact { font-size: 11.5px; color: #c9d0dc; line-height: 1.7; }
        .invoice-info { text-align: right; padding-top: 4px; flex: 0 0 auto; }
        .invoice-title {
            font-size: 13px;
            letter-spacing: 4px;
            font-weight: 600;
            color: #c9d0dc;
            margin-bottom: 6px;
        }
        .invoice-number {
            font-size: 20px;
            color: #ffffff;
            font-weight: 700;
            letter-spacing: .5px;
        }
        .invoice-info .issue-date {
            margin-top: 14px;
            font-size: 11.5px;
            color: #c9d0dc;
        }
        .invoice-info .issue-date strong { color: #eef0f3; display:block; font-size:11px; letter-spacing:.5px; margin-bottom:2px; }

        /* ---------- Body ---------- */
        .invoice-details { padding: 36px 40px 30px; }

        .billing-info { margin-bottom: 30px; }
        .billing-info::after { content:""; display:table; clear:both; }
        .bill-to, .payment-info { width: 47%; float: left; }
        .payment-info { float: right; }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--gold);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 1px solid var(--line);
            padding-bottom: 8px;
        }
        .bill-to strong, .payment-info strong { color: var(--ink); }
        .kv { margin: 4px 0; color: #444; }
        .kv b { color: var(--ink); font-weight:600; }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 24px;
        }
        .invoice-table th {
            background: #f7f8fa;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--ink-soft);
            padding: 12px 14px;
            border-bottom: 2px solid var(--ink);
            text-align: left;
        }
        .invoice-table td {
            padding: 14px;
            border-bottom: 1px solid var(--line);
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .amount { font-weight: 700; color: var(--ink); }

        .total-section {
            margin-top: 10px;
            padding-top: 16px;
            width: 320px;
            margin-left: auto;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            color: #444;
        }
        .total-row.grand {
            border-top: 2px solid var(--ink);
            margin-top: 6px;
            padding-top: 12px;
        }
        .total-label { font-weight: 600; }
        .total-label.grand-label { font-size: 15px; color: var(--ink); }
        .total-amount { font-weight: 700; color: var(--ink); font-size: 21px; }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .status-pagado { background: var(--ok-bg); color: var(--ok-fg); }
        .status-pendiente { background: var(--warn-bg); color: var(--warn-fg); }
        .status-vencido { background: var(--bad-bg); color: var(--bad-fg); }

        .notes {
            margin-top: 24px;
            padding: 18px 20px;
            background: #f7f8fa;
            border-left: 3px solid var(--gold);
            border-radius: 0 4px 4px 0;
        }
        .notes ul { margin: 8px 0 0; padding-left: 18px; }
        .notes li { margin-bottom: 4px; }

        .footer {
            text-align: center;
            padding: 24px 20px;
            border-top: 1px solid var(--line);
            background: #f7f8fa;
            color: var(--muted);
            font-size: 11px;
        }
        .footer strong { color: var(--ink-soft); }

        @media print {
            body { background: #fff; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; color-adjust: exact; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
            .invoice-container { border: none; box-shadow: none; border-radius: 0; }
            .no-print { display: none; }
        }

        .print-actions { text-align: center; margin: 0 auto 20px; max-width: 850px; }
        .btn {
            display: inline-block;
            padding: 10px 22px;
            margin: 0 5px;
            background: var(--ink);
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 12.5px;
            letter-spacing: .3px;
            transition: background .15s ease;
        }
        .btn:hover { background: var(--ink-soft); }
        .btn-secondary { background: #9aa0ab; }
        .btn-secondary:hover { background: #7d838d; }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button class="btn" onclick="window.print()">🖨️ Imprimir Factura</button>
        <a href="<?php echo url('pagos/' . $pago['id']); ?>" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header clearfix">
            <div class="company-info">
                <div class="company-logo"><?php echo skyNetworkLogoSVG('#ffffff', 54); ?></div>
                <div>
                    <div class="company-name">SKY NETWORK</div>
                    <div class="company-tagline">La Red del Cielo</div>
                    <div class="company-contact">
                        Internet por cableado (UTP)<br>
                    </div>
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">FACTURA</div>
                <div class="invoice-number"><?php echo htmlspecialchars($pago['numero_factura']); ?></div>
                <div class="issue-date">
                    <strong>Fecha de emisión</strong>
                    <?php echo date('d/m/Y', strtotime($pago['created_at'])); ?>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="invoice-details">
            <div class="billing-info">
                <div class="bill-to">
                    <div class="section-title">Facturado a</div>
                    <div class="kv"><strong><?php echo htmlspecialchars($pago['cliente_nombre']); ?></strong></div>
                    <div class="kv">Cliente ID: <b><?php echo $pago['cliente_id']; ?></b></div>
                </div>
                <div class="payment-info">
                    <div class="section-title">Información de pago</div>
                    <div class="kv">Estado:
                        <span class="status-badge status-<?php echo $pago['estado']; ?>">
                            <?php echo ucfirst($pago['estado']); ?>
                        </span>
                    </div>
                    <div class="kv">Método: <b><?php echo ucfirst($pago['metodo_pago']); ?></b></div>
                    <div class="kv">Fecha de pago: <b><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></b></div>
                    <div class="kv">Vencimiento: <b><?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?></b></div>
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-right">Precio unitario</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Servicio de Internet</strong><br>
                            <span style="color:var(--muted); font-size:11.5px;">
                                Periodo: <?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?> — <?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?>
                            </span>
                        </td>
                        <td class="text-center">1</td>
                        <td class="text-right amount">$<?php echo number_format($pago['monto'], 2); ?></td>
                        <td class="text-right amount">$<?php echo number_format($pago['monto'], 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="total-section">
                <div class="total-row">
                    <span class="total-label">Subtotal</span>
                    <span>$<?php echo number_format($pago['monto'], 2); ?></span>
                </div>
                <div class="total-row">
                    <span class="total-label">IVA (0%)</span>
                    <span>$0.00</span>
                </div>
                <div class="total-row grand">
                    <span class="total-label grand-label">TOTAL</span>
                    <span class="total-amount">$<?php echo number_format($pago['monto'], 2); ?></span>
                </div>
            </div>

            <?php if (!empty($pago['observaciones'])): ?>
            <div class="notes">
                <div class="section-title" style="border:none; padding:0; margin-bottom:6px;">Observaciones</div>
                <?php echo nl2br(htmlspecialchars($pago['observaciones'])); ?>
            </div>
            <?php endif; ?>

            <?php if ($pago['estado'] !== 'pagado'): ?>
            <div class="notes">
                <div class="section-title" style="border:none; padding:0; margin-bottom:6px;">Instrucciones de pago</div>
                <p style="margin:0 0 4px;">Métodos de pago aceptados:</p>
                <ul>
                    <li>💳 Transferencia bancaria — Cuenta 1234567890, Banco Nacional</li>
                    <li>💰 Efectivo — en nuestras oficinas durante horario comercial</li>
                    <li>🏦 PayPal — pagos@ispmanagement.com</li>
                    <li>💳 Tarjeta de crédito/débito — a través de nuestro portal web</li>
                </ul>
                <p style="margin:10px 0 0;"><strong>Fecha límite de pago:</strong> <?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Esta factura fue generada automáticamente por el sistema de Sky Network.<br>
                Para consultas sobre esta factura, contacte a nuestro departamento de facturación.<br>
                <strong>¡Gracias por elegir Sky Network!</strong>
            </p>
            <p style="margin-top: 12px;">
                Factura generada el: <?php echo date('d/m/Y H:i:s'); ?>
            </p>
        </div>
    </div>

    <script>
        // window.onload = function() { window.print(); }
        function printInvoice() { window.print(); }
        window.onafterprint = function() {
            // window.close();
        };
    </script>
</body>
</html>