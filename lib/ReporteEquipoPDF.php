<?php
/**
 * Clase para generar reportes PDF de equipos
 * Requiere la librería TCPDF (composer require tecnickcom/tcpdf)
 */

class ReporteEquipoPDF {
    private $equipo;
    
    public function __construct($equipo) {
        $this->equipo = $equipo;
    }
    
    public function generar() {
        // Si TCPDF está disponible, usar la versión completa
        if (class_exists('TCPDF')) {
            return $this->generarConTCPDF();
        } else {
            // Versión de respaldo con texto plano
            return $this->generarTextoPlano();
        }
    }
    
    private function generarConTCPDF() {
        // Crear nuevo documento PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Configurar información del documento
        $pdf->SetCreator('ISP Management System');
        $pdf->SetAuthor(APP_NAME);
        $pdf->SetTitle('Reporte Técnico - Equipo #' . $this->equipo['id']);
        $pdf->SetSubject('Reporte de Equipo');
        
        // Configurar márgenes
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Agregar página
        $pdf->AddPage();
        
        // Contenido HTML del reporte
        $html = $this->generarHTML();
        
        // Escribir HTML al PDF
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Salida del PDF
        $nombreArchivo = 'reporte_equipo_' . $this->equipo['id'] . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($nombreArchivo, 'D');
    }
    
    private function generarHTML() {
        $estadoColor = $this->obtenerColorEstado($this->equipo['estado_tecnico']);
        
        $html = '
        <style>
            h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
            h2 { color: #34495e; margin-top: 20px; }
            .info-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            .info-table td { padding: 8px; border: 1px solid #ddd; }
            .info-table .label { background-color: #f8f9fa; font-weight: bold; width: 30%; }
            .estado { padding: 5px 10px; border-radius: 5px; color: white; background-color: ' . $estadoColor . '; }
            .recomendacion { background-color: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 10px 0; }
        </style>
        
        <h1>REPORTE TÉCNICO DEL EQUIPO</h1>
        
        <p><strong>Fecha del reporte:</strong> ' . date('d/m/Y H:i:s') . '</p>
        <p><strong>Generado por:</strong> ' . APP_NAME . '</p>
        
        <h2>INFORMACIÓN GENERAL</h2>
        <table class="info-table">
            <tr>
                <td class="label">ID del Equipo</td>
                <td>' . htmlspecialchars($this->equipo['id']) . '</td>
            </tr>
            <tr>
                <td class="label">Cliente</td>
                <td>' . htmlspecialchars($this->equipo['cliente_nombre']) . '</td>
            </tr>
            <tr>
                <td class="label">Tipo de Equipo</td>
                <td>' . ucfirst(str_replace('_', ' ', $this->equipo['tipo_equipo'])) . '</td>
            </tr>
            <tr>
                <td class="label">Marca</td>
                <td>' . htmlspecialchars($this->equipo['marca']) . '</td>
            </tr>
            <tr>
                <td class="label">Modelo</td>
                <td>' . htmlspecialchars($this->equipo['modelo']) . '</td>
            </tr>
            <tr>
                <td class="label">Número de Serie</td>
                <td>' . ($this->equipo['numero_serie'] ?: 'No especificado') . '</td>
            </tr>
            <tr>
                <td class="label">Estado Técnico</td>
                <td><span class="estado">' . ucfirst(str_replace('_', ' ', $this->equipo['estado_tecnico'])) . '</span></td>
            </tr>
            <tr>
                <td class="label">Fecha de Instalación</td>
                <td>' . ($this->equipo['fecha_instalacion'] ? date('d/m/Y', strtotime($this->equipo['fecha_instalacion'])) : 'No especificada') . '</td>
            </tr>
            <tr>
                <td class="label">Fecha de Registro</td>
                <td>' . date('d/m/Y H:i', strtotime($this->equipo['created_at'])) . '</td>
            </tr>
            <tr>
                <td class="label">Última Actualización</td>
                <td>' . date('d/m/Y H:i', strtotime($this->equipo['updated_at'])) . '</td>
            </tr>
        </table>';
        
        if ($this->equipo['observaciones_tecnico']) {
            $html .= '
            <h2>OBSERVACIONES TÉCNICAS</h2>
            <p>' . nl2br(htmlspecialchars($this->equipo['observaciones_tecnico'])) . '</p>';
        }
        
        $html .= '
        <h2>RECOMENDACIONES</h2>
        <div class="recomendacion">
            ' . $this->obtenerRecomendaciones() . '
        </div>
        
        <h2>PRÓXIMAS ACCIONES</h2>
        <ul>
            <li>Próximo mantenimiento: ' . date('d/m/Y', strtotime('+6 months')) . '</li>
            <li>Revisión de garantía: Pendiente</li>
            <li>Actualización de firmware: Pendiente</li>
        </ul>';
        
        return $html;
    }
    
    private function generarTextoPlano() {
        $contenido = "REPORTE TÉCNICO DEL EQUIPO\n";
        $contenido .= "==============================\n\n";
        $contenido .= "Fecha del reporte: " . date('d/m/Y H:i:s') . "\n\n";
        
        $contenido .= "INFORMACIÓN GENERAL\n";
        $contenido .= "-------------------\n";
        $contenido .= "ID del equipo: " . $this->equipo['id'] . "\n";
        $contenido .= "Cliente: " . $this->equipo['cliente_nombre'] . "\n";
        $contenido .= "Tipo de equipo: " . ucfirst(str_replace('_', ' ', $this->equipo['tipo_equipo'])) . "\n";
        $contenido .= "Marca: " . $this->equipo['marca'] . "\n";
        $contenido .= "Modelo: " . $this->equipo['modelo'] . "\n";
        $contenido .= "Número de serie: " . ($this->equipo['numero_serie'] ?: 'No especificado') . "\n";
        $contenido .= "Estado técnico: " . ucfirst(str_replace('_', ' ', $this->equipo['estado_tecnico'])) . "\n";
        $contenido .= "Fecha de instalación: " . ($this->equipo['fecha_instalacion'] ? date('d/m/Y', strtotime($this->equipo['fecha_instalacion'])) : 'No especificada') . "\n";
        $contenido .= "Fecha de registro: " . date('d/m/Y H:i', strtotime($this->equipo['created_at'])) . "\n";
        $contenido .= "Última actualización: " . date('d/m/Y H:i', strtotime($this->equipo['updated_at'])) . "\n\n";
        
        if ($this->equipo['observaciones_tecnico']) {
            $contenido .= "OBSERVACIONES TÉCNICAS\n";
            $contenido .= "----------------------\n";
            $contenido .= $this->equipo['observaciones_tecnico'] . "\n\n";
        }
        
        $contenido .= "RECOMENDACIONES\n";
        $contenido .= "---------------\n";
        $contenido .= strip_tags($this->obtenerRecomendaciones()) . "\n\n";
        
        $contenido .= "PRÓXIMAS ACCIONES\n";
        $contenido .= "----------------\n";
        $contenido .= "- Próximo mantenimiento: " . date('d/m/Y', strtotime('+6 months')) . "\n";
        $contenido .= "- Revisión de garantía: Pendiente\n";
        $contenido .= "- Actualización de firmware: Pendiente\n\n";
        
        $contenido .= "---\n";
        $contenido .= "Reporte generado automáticamente por " . APP_NAME . "\n";
        $contenido .= "Fecha: " . date('d/m/Y H:i:s') . "\n";
        
        // Configurar headers para descarga
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_equipo_' . $this->equipo['id'] . '_' . date('Y-m-d') . '.txt"');
        
        echo $contenido;
        exit;
    }
    
    private function obtenerColorEstado($estado) {
        switch ($estado) {
            case 'operativo':
                return '#28a745';
            case 'necesita_revision':
                return '#ffc107';
            case 'fuera_de_servicio':
                return '#dc3545';
            case 'en_mantenimiento':
                return '#17a2b8';
            default:
                return '#6c757d';
        }
    }
    
    private function obtenerRecomendaciones() {
        $recomendaciones = '';
        
        switch ($this->equipo['estado_tecnico']) {
            case 'operativo':
                $recomendaciones = '
                    <strong>El equipo está funcionando correctamente:</strong><br>
                    • Realizar mantenimiento preventivo cada 6 meses<br>
                    • Verificar conectores y cables periódicamente<br>
                    • Monitorear temperatura de funcionamiento<br>
                    • Mantener firmware actualizado
                ';
                break;
            case 'necesita_revision':
                $recomendaciones = '
                    <strong>Requiere atención técnica prioritaria:</strong><br>
                    • Programar revisión técnica urgente<br>
                    • Verificar todos los conectores y cables<br>
                    • Actualizar firmware si es necesario<br>
                    • Revisar configuración de red
                ';
                break;
            case 'fuera_de_servicio':
                $recomendaciones = '
                    <strong>Equipo no operativo - Acción inmediata requerida:</strong><br>
                    • Reemplazar equipo inmediatamente<br>
                    • Evaluar causa de la falla<br>
                    • Considerar mejoras en la instalación<br>
                    • Verificar garantía del equipo
                ';
                break;
            case 'en_mantenimiento':
                $recomendaciones = '
                    <strong>Equipo en proceso de mantenimiento:</strong><br>
                    • Completar proceso de mantenimiento<br>
                    • Realizar pruebas de funcionamiento<br>
                    • Documentar trabajos realizados<br>
                    • Actualizar estado al completar
                ';
                break;
        }
        
        return $recomendaciones;
    }
}
?>
