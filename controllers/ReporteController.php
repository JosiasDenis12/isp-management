<?php
class ReporteController {

    public function index() {
        // Aquí podrías agregar consultas para generar estadísticas reales
        $data = [
            'title' => 'Reportes y Estadísticas - ' . APP_NAME,
        ];

        $this->loadView('reportes/index', $data);
    }

    public function suscripciones() {
        require_once 'models/Cliente.php';

        $clienteModel = new Cliente();
        $rows = $clienteModel->getResumenSuscripciones();

        $rows = array_map(function($r) {
            $diaCorte = (int)($r['dia_corte'] ?? 5);
            if ($diaCorte < 1) $diaCorte = 1;
            if ($diaCorte > 31) $diaCorte = 31;

            $ultimoEstadoPago = (string)($r['ultimo_estado_pago'] ?? '');
            $totalPagos = (int)($r['total_pagos'] ?? 0);
            $ultimaFechaVencimiento = $r['ultima_fecha_vencimiento'] ?? null;

            if ($totalPagos === 0) {
                $r['fecha_vencimiento_calc'] = $this->calcularVencimientoPorCorte($r['fecha_contratacion'] ?? null, $diaCorte);
            } else {
                $r['fecha_vencimiento_calc'] = $ultimaFechaVencimiento ?: $this->calcularVencimientoPorCorte($r['fecha_contratacion'] ?? null, $diaCorte);
            }

            $r['tiene_pagos'] = $totalPagos > 0;
            $r['ultimo_estado_pago'] = $ultimoEstadoPago;
            return $r;
        }, $rows);

        $data = [
            'title' => 'Reporte de Suscripciones - ' . APP_NAME,
            'rows' => $rows,
        ];

        $this->loadView('reportes/suscripciones', $data);
    }

    public function equiposVisitas() {
        require_once 'models/VisitaTecnica.php';

        $filters = [
            'fecha_desde' => $this->cleanDate($_GET['fecha_desde'] ?? ''),
            'fecha_hasta' => $this->cleanDate($_GET['fecha_hasta'] ?? ''),
            'cliente' => trim((string)($_GET['cliente'] ?? '')),
            'tecnico' => trim((string)($_GET['tecnico'] ?? '')),
            'equipo' => trim((string)($_GET['equipo'] ?? '')),
            'estado_visita' => trim((string)($_GET['estado_visita'] ?? '')),
            'estado_equipo' => trim((string)($_GET['estado_equipo'] ?? '')),
            'tipo_visita' => trim((string)($_GET['tipo_visita'] ?? '')),
        ];

        $visitaModel = new VisitaTecnica();
        $rows = $visitaModel->getReporteEquiposVisitas($filters);
        $stats = $visitaModel->getReporteEquiposVisitasStats($filters);

        $data = [
            'title' => 'Reporte de Equipos y Visitas TÃ©cnicas - ' . APP_NAME,
            'rows' => $rows,
            'stats' => $stats,
            'filters' => $filters,
        ];

        $this->loadView('reportes/equipos-visitas', $data);
    }

    public function equiposInstalados() {
        require_once 'models/Equipo.php';

        $filters = [
            'cliente' => trim((string)($_GET['cliente'] ?? '')),
            'tipo_equipo' => trim((string)($_GET['tipo_equipo'] ?? '')),
            'estado_tecnico' => trim((string)($_GET['estado_tecnico'] ?? '')),
            'mac_address' => trim((string)($_GET['mac_address'] ?? '')),
            'direccion_ip' => trim((string)($_GET['direccion_ip'] ?? '')),
            'fecha_desde' => $this->cleanDate($_GET['fecha_desde'] ?? ''),
            'fecha_hasta' => $this->cleanDate($_GET['fecha_hasta'] ?? ''),
            'orden_fecha' => (($_GET['orden_fecha'] ?? 'desc') === 'asc') ? 'asc' : 'desc',
        ];

        $equipoModel = new Equipo();
        $rows = $equipoModel->getReporteEquiposInstalados($filters);

        $stats = [
            'total' => count($rows),
            'antenas' => count(array_filter($rows, function($r) { return ($r['tipo_equipo'] ?? '') === 'antena'; })),
            'modems' => count(array_filter($rows, function($r) { return ($r['tipo_equipo'] ?? '') === 'modem'; })),
            'acceso_activo' => count(array_filter($rows, function($r) { return (int)($r['acceso_habilitado'] ?? 0) === 1; })),
        ];

        $data = [
            'title' => 'Reporte de Equipos Instalados - ' . APP_NAME,
            'rows' => $rows,
            'stats' => $stats,
            'filters' => $filters,
        ];

        $this->loadView('reportes/equipos-instalados', $data);
    }

    private function cleanDate($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        return $dt ? $dt->format('Y-m-d') : '';
    }

    private function calcularVencimientoPorCorte($baseDate, $diaCorte) {
        $baseDate = trim((string)$baseDate);
        if ($baseDate === '') {
            return null;
        }

        $base = DateTimeImmutable::createFromFormat('Y-m-d', substr($baseDate, 0, 10));
        if (!$base) {
            return null;
        }

        $year = (int)$base->format('Y');
        $month = (int)$base->format('m');

        $firstOfMonth = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $lastDay = (int)$firstOfMonth->format('t');
        $day = max(1, min((int)$diaCorte, $lastDay));
        $candidate = $firstOfMonth->setDate($year, $month, $day);

        if ($candidate <= $base) {
            $next = $firstOfMonth->modify('first day of next month');
            $nextYear = (int)$next->format('Y');
            $nextMonth = (int)$next->format('m');
            $nextLast = (int)$next->format('t');
            $nextDay = max(1, min((int)$diaCorte, $nextLast));
            $candidate = $next->setDate($nextYear, $nextMonth, $nextDay);
        }

        return $candidate->format('Y-m-d');
    }

    private function loadView($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }
}

?>
