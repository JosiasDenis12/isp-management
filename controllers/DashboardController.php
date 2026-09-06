<?php
require_once 'models/Cliente.php';
require_once 'models/Pago.php';
require_once 'models/Equipo.php';
require_once 'lib/DashboardService.php';

class DashboardController {
    
    public function index() {
        try {
            // Verificar conexión a la base de datos
            $database = new Database();
            if (!$database->testConnection()) {
                throw new Exception("No se puede conectar a la base de datos");
            }
            
            $dashboard = (new DashboardService())->getDashboard((string)($_GET['periodo'] ?? '7d'));
            
            $data = [
                'title' => 'Dashboard - ' . APP_NAME,
                'dashboard' => $dashboard,
                'error' => null
            ];
            
        } catch (Exception $e) {
            $data = [
                'title' => 'Dashboard - ' . APP_NAME,
                'dashboard' => ['period'=>'7d','stats'=>[],'series'=>[],'alerts'=>[],'recentActivity'=>[],'calendar'=>['month'=>date('Y-m'),'events'=>[]]],
                'error' => $e->getMessage()
            ];
        }
        
        $this->loadView('dashboard/index', $data);
    }

    public function actividad() {
        $data = [
            'title' => 'Actividad - ' . APP_NAME,
            'activities' => (new DashboardService())->getActivity(200),
        ];
        $this->loadView('dashboard/actividad', $data);
    }

    public function calendario() {
        $month = (string)($_GET['mes'] ?? date('Y-m'));
        $data = [
            'title' => 'Calendario - ' . APP_NAME,
            'calendar' => (new DashboardService())->getCalendar($month),
        ];
        $this->loadView('dashboard/calendario', $data);
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }
}
?>
