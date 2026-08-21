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
    // Layout compacto para ticket/nota de compra
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ticket <?php echo htmlspecialchars($pago['numero_factura']); ?></title>
        <style>
            :root { --ink: #000; --line: #000; }
            @page { size: 58mm auto; margin: 0; }
            * { box-sizing: border-box; }
            html { width: 58mm; background: #fff; }
            body {
                width: 58mm;
                margin: 0;
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
            .print-actions { text-align: center; margin-bottom: 14px; }
            .no-print { display: block; }
            .btn {
                display: inline-block;
                padding: 8px 16px;
                background: #000;
                color: #fff;
                text-decoration: none;
                border: none;
                border-radius: 3px;
                cursor: pointer;
                font-family: Arial, sans-serif;
                font-size: 12px;
                letter-spacing: .5px;
            }
            @media print {
                html, body { width: 58mm; min-width: 58mm; max-width: 58mm; background: #fff; }
                body { padding: 5mm; -webkit-print-color-adjust: exact; print-color-adjust: exact; color-adjust: exact; }
                * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
                .no-print { display: none !important; }
                .ticket { width: 48mm; max-width: 48mm; margin: 0; }
            }
        </style>
    </head>
    <body>
        <div class="print-actions no-print">
            <button onclick="window.print()" class="btn">🖨️ Imprimir Ticket</button>
        </div>
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
            <div class="center small" style="letter-spacing:.5px;">¡Gracias por su preferencia!</div>
        </div>
        <script>
            // window.onload = function() { window.print(); }
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
