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

            $base = $r['ultima_fecha_venc_pagada'] ?? null;
            if (empty($base)) {
                $base = $r['fecha_contratacion'] ?? null;
            }

            $r['fecha_vencimiento_calc'] = $this->calcularVencimientoPorCorte($base, $diaCorte);
            return $r;
        }, $rows);

        $data = [
            'title' => 'Reporte de Suscripciones - ' . APP_NAME,
            'rows' => $rows,
        ];

        $this->loadView('reportes/suscripciones', $data);
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
