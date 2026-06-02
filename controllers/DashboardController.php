<?php
require_once 'models/Cliente.php';
require_once 'models/Pago.php';
require_once 'models/Equipo.php';

class DashboardController {
    
    public function index() {
        try {
            // Verificar conexión a la base de datos
            $database = new Database();
            if (!$database->testConnection()) {
                throw new Exception("No se puede conectar a la base de datos");
            }
            
            $clienteModel = new Cliente();
            $pagoModel = new Pago();
            $equipoModel = new Equipo();
            
            $clientesStats = $clienteModel->getStats();
            $pagosStats = $pagoModel->getStats();
            $equiposStats = $equipoModel->getStats();
            $proximosVencimientos = $pagoModel->getProximosVencimientos();
            
            // Información adicional de pagos
            $clientesVencidos = $clienteModel->getClientesConPagosVencidos();
            $clientesPorVencer = $clienteModel->getClientesConPagosPorVencer(7);
            $resumenPagos = $clienteModel->getResumenEstadoPagos();
            
            $data = [
                'title' => 'Dashboard - ' . APP_NAME,
                'clientesStats' => $clientesStats,
                'pagosStats' => $pagosStats,
                'equiposStats' => $equiposStats,
                'proximosVencimientos' => $proximosVencimientos,
                'clientesVencidos' => $clientesVencidos,
                'clientesPorVencer' => $clientesPorVencer,
                'resumenPagos' => $resumenPagos,
                'error' => null
            ];
            
        } catch (Exception $e) {
            $data = [
                'title' => 'Dashboard - ' . APP_NAME,
                'clientesStats' => ['total' => 0, 'activos' => 0, 'suspendidos' => 0, 'pendientes' => 0],
                'pagosStats' => ['total_pagos' => 0, 'ingresos_mes' => 0, 'pagos_vencidos' => 0, 'pagos_pendientes' => 0],
                'equiposStats' => ['total_equipos' => 0, 'operativos' => 0, 'necesitan_revision' => 0],
                'proximosVencimientos' => [],
                'clientesVencidos' => [],
                'clientesPorVencer' => [],
                'resumenPagos' => ['clientes_con_pagos_vencidos' => 0, 'monto_total_vencido' => 0],
                'error' => $e->getMessage()
            ];
        }
        
        $this->loadView('dashboard/index', $data);
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }
}
?>
