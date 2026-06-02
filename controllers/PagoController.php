<?php
require_once 'models/Pago.php';
require_once 'models/Cliente.php';

class PagoController {
    
    public function index() {
        $pagoModel = new Pago();
        $pagos = $pagoModel->getAll();
        
        $data = [
            'title' => 'Pagos y Facturación - ' . APP_NAME,
            'pagos' => $pagos
        ];
        
        $this->loadView('pagos/index', $data);
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pagoModel = new Pago();
            
            $pagoModel->cliente_id = $_POST['cliente_id'];
            $pagoModel->monto = $_POST['monto'];
            $pagoModel->fecha_pago = $_POST['fecha_pago'];
            $pagoModel->fecha_vencimiento = $_POST['fecha_vencimiento'];
            $pagoModel->metodo_pago = $_POST['metodo_pago'];
            $pagoModel->estado = $_POST['estado'];
            $pagoModel->numero_factura = $pagoModel->generateFacturaNumber();
            $pagoModel->observaciones = $_POST['observaciones'] ?? '';
            
            if ($pagoModel->create()) {
                header('Location: ' . url('pagos?success=Pago registrado exitosamente'));
                exit;
            } else {
                $error = 'Error al registrar el pago';
            }
        }
        
        $clienteModel = new Cliente();
        $clientes = $clienteModel->getAll();
        
        $pagoModel = new Pago();
        $numero_factura = $pagoModel->generateFacturaNumber();
        
        $data = [
            'title' => 'Registrar Pago - ' . APP_NAME,
            'clientes' => $clientes,
            'numero_factura' => $numero_factura,
            'error' => $error ?? null
        ];
        
        $this->loadView('pagos/create', $data);
    }
    
    public function show($id) {
        $pagoModel = new Pago();
        $pago = $pagoModel->getById($id);
        
        if (!$pago) {
            header('Location: ' . url('pagos?error=Pago no encontrado'));
            exit;
        }
        
        $data = [
            'title' => 'Detalle del Pago - ' . APP_NAME,
            'pago' => $pago
        ];
        
        $this->loadView('pagos/show', $data);
    }
    
    public function print($id) {
        $pagoModel = new Pago();
        $pago = $pagoModel->getById($id);
        
        if (!$pago) {
            header('Location: ' . url('pagos?error=Pago no encontrado'));
            exit;
        }
        
        $data = [
            'pago' => $pago
        ];
        
        $this->loadView('pagos/print', $data);
    }
    
    public function marcarPagado($id) {
        header('Content-Type: application/json');
        
        try {
            $pagoModel = new Pago();
            $pago = $pagoModel->getById($id);
            
            if (!$pago) {
                echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
                return;
            }
            
            // Aquí actualizarías el estado del pago a 'pagado'
            // Por ahora, solo simularemos la respuesta
            echo json_encode(['success' => true, 'message' => 'Pago marcado como pagado']);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    public function enviarRecordatorio($id) {
        header('Content-Type: application/json');
        
        try {
            $pagoModel = new Pago();
            $pago = $pagoModel->getById($id);
            
            if (!$pago) {
                echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
                return;
            }
            
            // Aquí implementarías la lógica para enviar el recordatorio
            // Por ahora, solo simularemos la respuesta
            echo json_encode(['success' => true, 'message' => 'Recordatorio enviado']);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }
}
?>
