<?php
// Soporta impresión de factura completa o ticket compacto.
$type = isset($_GET['type']) ? $_GET['type'] : 'invoice';

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
            body { font-family: monospace, Arial; font-size: 12px; color: #000; margin:0; padding:10px; }
            .ticket { width: 280px; max-width:280px; margin:0 auto; padding:10px; border:1px dashed #000; }
            .center { text-align:center; }
            .bold { font-weight:bold; }
            .small { font-size:11px; }
            .divider { border-top:1px dashed #000; margin:8px 0; }
            .amount { font-size:16px; font-weight:bold; }
            .print-actions { text-align:center; margin-bottom:8px; }
            .no-print { display:block; }
            @media print { .no-print { display:none; } }
        </style>
    </head>
    <body>
        <div class="print-actions no-print">
            <button onclick="window.print()" style="padding:6px 10px;">🖨️ Imprimir Ticket</button>
        </div>
        <div class="ticket">
            <div class="center bold">ISP Management</div>
            <div class="center small">Servicios de Internet</div>
            <div class="center small">Tel: +1 (555) 123-4567</div>
            <div class="divider"></div>
            <div class="center">TICKET / NOTA DE COMPRA</div>
            <div class="divider"></div>
            <div><span class="bold">Factura:</span> <?php echo htmlspecialchars($pago['numero_factura']); ?></div>
            <div><span class="bold">Cliente:</span> <?php echo htmlspecialchars($pago['cliente_nombre']); ?></div>
            <div><span class="small">Fecha:</span> <?php echo date('d/m/Y H:i', strtotime($pago['created_at'])); ?></div>
            <div class="divider"></div>
            <div>
                <div>Servicio</div>
                <div style="float:right;">$<?php echo number_format($pago['monto'], 2); ?></div>
                <div style="clear:both;"></div>
            </div>
            <div class="divider"></div>
            <div class="center amount">TOTAL $<?php echo number_format($pago['monto'], 2); ?></div>
            <div class="divider"></div>
            <div class="small">Método: <?php echo ucfirst($pago['metodo_pago']); ?></div>
            <?php if (!empty($pago['observaciones'])): ?>
                <div class="divider"></div>
                <div class="small">Obs: <?php echo nl2br(htmlspecialchars($pago['observaciones'])); ?></div>
            <?php endif; ?>
            <div class="divider"></div>
            <div class="center small">¡Gracias por su pago!</div>
        </div>
        <script>
            // Puedes descomentar la siguiente línea si quieres que la ventana imprima automáticamente
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
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 1px solid #ddd;
        }
        
        .invoice-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #007bff;
        }
        
        .company-info {
            float: left;
            width: 50%;
        }
        
        .invoice-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .company-tagline {
            font-style: italic;
            color: #666;
            margin-bottom: 10px;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .invoice-number {
            font-size: 18px;
            color: #007bff;
            font-weight: bold;
        }
        
        .invoice-details {
            padding: 20px;
        }
        
        .billing-info {
            margin-bottom: 30px;
        }
        
        .billing-info::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .bill-to, .payment-info {
            width: 48%;
            float: left;
        }
        
        .payment-info {
            float: right;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .invoice-table th,
        .invoice-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .invoice-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .amount {
            font-weight: bold;
            color: #28a745;
        }
        
        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #007bff;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-label {
            font-weight: bold;
        }
        
        .total-amount {
            font-weight: bold;
            color: #28a745;
            font-size: 18px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pagado {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pendiente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-vencido {
            background: #f8d7da;
            color: #721c24;
        }
        
        .notes {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #eee;
            background: #f8f9fa;
            color: #666;
            font-size: 11px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .invoice-container {
                border: none;
                box-shadow: none;
            }
            
            .no-print {
                display: none;
            }
        }
        
        .print-actions {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button class="btn" onclick="window.print()">
            🖨️ Imprimir Factura
        </button>
        <a href="<?php echo url('pagos/' . $pago['id']); ?>" class="btn btn-secondary">
            ← Volver
        </a>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header clearfix">
            <div class="company-info">
                <div class="company-name">ISP Management</div>
                <div class="company-tagline">Servicios de Internet y Telecomunicaciones</div>
                <div>
                    📍 Dirección de la Empresa<br>
                    📞 +1 (555) 123-4567<br>
                    ✉️ contacto@ispmanagement.com<br>
                    🌐 www.ispmanagement.com
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">FACTURA</div>
                <div class="invoice-number"><?php echo htmlspecialchars($pago['numero_factura']); ?></div>
                <div style="margin-top: 10px;">
                    <strong>Fecha de Emisión:</strong><br>
                    <?php echo date('d/m/Y', strtotime($pago['created_at'])); ?>
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <!-- Billing Information -->
            <div class="billing-info">
                <div class="bill-to">
                    <div class="section-title">Facturado a:</div>
                    <strong><?php echo htmlspecialchars($pago['cliente_nombre']); ?></strong><br>
                    Cliente ID: <?php echo $pago['cliente_id']; ?><br>
                    <!-- Aquí podrías agregar más información del cliente si está disponible -->
                </div>
                <div class="payment-info">
                    <div class="section-title">Información de Pago:</div>
                    <strong>Estado:</strong> 
                    <span class="status-badge status-<?php echo $pago['estado']; ?>">
                        <?php echo ucfirst($pago['estado']); ?>
                    </span><br>
                    <strong>Método:</strong> <?php echo ucfirst($pago['metodo_pago']); ?><br>
                    <strong>Fecha de Pago:</strong> <?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?><br>
                    <strong>Vencimiento:</strong> <?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?>
                </div>
            </div>

            <!-- Service Details Table -->
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-right">Precio Unitario</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Servicio de Internet</strong><br>
                            <small>Periodo: <?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?> - <?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?></small>
                        </td>
                        <td class="text-center">1</td>
                        <td class="text-right amount">$<?php echo number_format($pago['monto'], 2); ?></td>
                        <td class="text-right amount">$<?php echo number_format($pago['monto'], 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="total-section">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span>$<?php echo number_format($pago['monto'], 2); ?></span>
                </div>
                <div class="total-row">
                    <span class="total-label">IVA (0%):</span>
                    <span>$0.00</span>
                </div>
                <div class="total-row" style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
                    <span class="total-label" style="font-size: 16px;">TOTAL:</span>
                    <span class="total-amount">$<?php echo number_format($pago['monto'], 2); ?></span>
                </div>
            </div>

            <!-- Notes -->
            <?php if (!empty($pago['observaciones'])): ?>
            <div class="notes">
                <div class="section-title">Observaciones:</div>
                <?php echo nl2br(htmlspecialchars($pago['observaciones'])); ?>
            </div>
            <?php endif; ?>

            <!-- Payment Instructions -->
            <?php if ($pago['estado'] !== 'pagado'): ?>
            <div class="notes">
                <div class="section-title">Instrucciones de Pago:</div>
                <p><strong>Métodos de pago aceptados:</strong></p>
                <ul>
                    <li>💳 Transferencia bancaria: Cuenta 1234567890 - Banco Nacional</li>
                    <li>💰 Efectivo: En nuestras oficinas durante horario comercial</li>
                    <li>🏦 PayPal: pagos@ispmanagement.com</li>
                    <li>💳 Tarjeta de crédito/débito: A través de nuestro portal web</li>
                </ul>
                <p><strong>Fecha límite de pago:</strong> <?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Esta es una factura generada automáticamente por el sistema ISP Management.<br>
                Para consultas sobre esta factura, contacte a nuestro departamento de facturación.<br>
                <strong>¡Gracias por elegir nuestros servicios!</strong>
            </p>
            <p style="margin-top: 15px;">
                Factura generada el: <?php echo date('d/m/Y H:i:s'); ?>
            </p>
        </div>
    </div>

    <script>
        // Auto-print cuando se carga la página (opcional)
        // window.onload = function() { window.print(); }
        
        // Función para imprimir
        function printInvoice() {
            window.print();
        }
        
        // Cerrar ventana después de imprimir (si se abrió en una nueva ventana)
        window.onafterprint = function() {
            // window.close(); // Descomenta si quieres cerrar automáticamente
        }
    </script>
</body>
</html>
