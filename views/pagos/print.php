<?php
// Soporta impresión de factura completa o ticket compacto.
$type = isset($_GET['type']) ? $_GET['type'] : 'invoice';

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
            :root{
                --ink:#0B1F3A;
                --gold:#B8912F;
                --line:#d8d8d8;
                --muted:#6b7280;
            }
            * { box-sizing: border-box; }
            body {
                font-family: 'Courier New', monospace;
                font-size: 12px;
                color: var(--ink);
                margin: 0;
                padding: 20px;
                background: #f4f4f4;
            }
            .ticket {
                width: 300px;
                max-width: 300px;
                margin: 0 auto;
                padding: 18px 16px;
                background: #fff;
                border: 1px solid var(--line);
                box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            }
            .center { text-align: center; }
            .bold { font-weight: 700; }
            .small { font-size: 10.5px; color: var(--muted); }
            .divider {
                border: none;
                border-top: 1px dashed #bbb;
                margin: 10px 0;
            }
            .brand-name {
                font-family: 'Georgia', 'Times New Roman', serif;
                font-weight: 700;
                letter-spacing: 3px;
                font-size: 17px;
                color: var(--ink);
            }
            .brand-tagline {
                font-family: 'Georgia', 'Times New Roman', serif;
                font-style: italic;
                font-size: 11px;
                color: var(--gold);
                margin-top: 2px;
            }
            .doc-title {
                letter-spacing: 2px;
                font-size: 11px;
                color: var(--muted);
                margin: 2px 0;
            }
            .row { display: flex; justify-content: space-between; margin: 3px 0; }
            .amount { font-size: 18px; font-weight: 700; color: var(--ink); }
            .print-actions { text-align: center; margin-bottom: 14px; }
            .no-print { display: block; }
            .btn {
                display: inline-block;
                padding: 8px 16px;
                background: var(--ink);
                color: #fff;
                text-decoration: none;
                border: none;
                border-radius: 3px;
                cursor: pointer;
                font-family: Arial, sans-serif;
                font-size: 12px;
                letter-spacing: .5px;
            }
            @media print { .no-print { display: none; } body{ background:#fff; padding:0; -webkit-print-color-adjust: exact; print-color-adjust: exact; color-adjust: exact; } * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; } .ticket{ border:none; box-shadow:none; } }
        </style>
    </head>
    <body>
        <div class="print-actions no-print">
            <button onclick="window.print()" class="btn">🖨️ Imprimir Ticket</button>
        </div>
        <div class="ticket">
            <div class="center"><?php echo skyNetworkLogoSVG('#0B1F3A', 46); ?></div>
            <div class="center brand-name">SKY NETWORK</div>
            <div class="center brand-tagline">La Red del Cielo</div>
            <div class="divider"></div>
            <div class="center doc-title">TICKET&nbsp;/&nbsp;NOTA&nbsp;DE&nbsp;COMPRA</div>
            <div class="divider"></div>
            <div class="row"><span class="bold">Factura</span><span><?php echo htmlspecialchars($pago['numero_factura']); ?></span></div>
            <div class="row"><span class="bold">Cliente</span><span><?php echo htmlspecialchars($pago['cliente_nombre']); ?></span></div>
            <div class="row small"><span>Fecha</span><span><?php echo date('d/m/Y H:i', strtotime($pago['created_at'])); ?></span></div>
            <div class="divider"></div>
            <div class="row"><span>Servicio de Internet</span><span>$<?php echo number_format($pago['monto'], 2); ?></span></div>
            <div class="divider"></div>
            <div class="row"><span class="bold">TOTAL</span><span class="amount">$<?php echo number_format($pago['monto'], 2); ?></span></div>
            <div class="divider"></div>
            <div class="small">Método de pago: <?php echo ucfirst($pago['metodo_pago']); ?></div>
            <?php if (!empty($pago['observaciones'])): ?>
                <div class="divider"></div>
                <div class="small">Obs: <?php echo nl2br(htmlspecialchars($pago['observaciones'])); ?></div>
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
                        ✉️ contacto@ispmanagement.com &nbsp;·&nbsp; 🌐 www.ispmanagement.com
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