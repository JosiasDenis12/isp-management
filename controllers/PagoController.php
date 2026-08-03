<?php
require_once 'models/Pago.php';
require_once 'models/Cliente.php';

class PagoController {
    
    public function index() {
        $pagoModel = new Pago();
        $pagos = $pagoModel->getAll();
        $proximosVencimientos = $pagoModel->getProximosVencimientos();
        $kpis = $pagoModel->getKpis();
        
        $data = [
            'title' => 'Pagos y Facturación - ' . APP_NAME,
            'pagos' => $pagos,
            'proximosVencimientos' => $proximosVencimientos,
            'kpis' => $kpis
        ];
        
        $this->loadView('pagos/index', $data);
    }
    
    public function kpis() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode(['success' => true, 'data' => (new Pago())->getKpis()]);
        } catch (Throwable $e) {
            http_response_code(500);
            error_log('PagoController@kpis: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'No fue posible actualizar los indicadores']);
        }
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pagoModel = new Pago();
            
            $pagoModel->cliente_id = $_POST['cliente_id'];
            $pagoModel->monto = $_POST['monto'];
            $pagoModel->fecha_pago = $_POST['fecha_pago'];
            $pagoModel->fecha_vencimiento = null; // Se deriva en el modelo.
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
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $pagoId = (int)$id;
        if ($pagoId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de pago inválido']);
            return;
        }

        try {
            $pagoModel = new Pago();
            $resultado = $pagoModel->marcarComoPagado($pagoId);

            $mensaje = 'Pago marcado como pagado';
            if (!empty($resultado['cliente_activado'])) {
                $mensaje .= ' y cliente reactivado';
            }

            echo json_encode([
                'success' => true,
                'message' => $mensaje,
                'data' => $resultado,
            ]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            error_log('PagoController@marcarPagado validation error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            http_response_code(409);
            error_log('PagoController@marcarPagado business error (pago ' . $pagoId . '): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('PagoController@marcarPagado database error (pago ' . $pagoId . '): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al actualizar el pago']);
        } catch (Throwable $e) {
            http_response_code(500);
            error_log('PagoController@marcarPagado unexpected error (pago ' . $pagoId . '): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error inesperado al procesar la solicitud']);
        }
    }
    
    public function enviarRecordatorio($id) {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $pagoId = (int)$id;
        if ($pagoId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de pago inválido']);
            return;
        }

        try {
            $pagoModel = new Pago();
            $recordatorio = $pagoModel->prepararRecordatorio($pagoId);

            echo json_encode([
                'success' => true,
                'message' => 'Recordatorio preparado correctamente',
                'data' => $recordatorio,
            ]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            error_log('PagoController@enviarRecordatorio validation error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            http_response_code(409);
            error_log('PagoController@enviarRecordatorio business error (pago ' . $pagoId . '): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('PagoController@enviarRecordatorio database error (pago ' . $pagoId . '): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al preparar el recordatorio']);
        } catch (Throwable $e) {
            http_response_code(500);
            error_log('PagoController@enviarRecordatorio unexpected error (pago ' . $pagoId . '): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error inesperado al procesar la solicitud']);
        }
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }
}
?>
