<?php
require_once 'models/Pago.php';
require_once 'models/Cliente.php';

class PagoController {
    
    public function index() {
        $pagoModel = new Pago();
        $pagos = $pagoModel->getAll();
        $proximosVencimientos = $pagoModel->getProximosVencimientos();
        $kpis = $pagoModel->getKpis();
        $clientes = (new Cliente())->getAll();
        
        $data = [
            'title' => 'Pagos y Facturación - ' . APP_NAME,
            'pagos' => $pagos,
            'proximosVencimientos' => $proximosVencimientos,
            'kpis' => $kpis,
            'clientes' => $clientes,
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

    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJson(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        $pagoId = (int)$id;
        if ($pagoId <= 0) {
            $this->sendJson(['success' => false, 'message' => 'ID de pago inválido'], 400);
            return;
        }

        $pagoModel = new Pago();
        $pagoActual = $pagoModel->getById($pagoId);
        if (!$pagoActual) {
            $this->sendJson(['success' => false, 'message' => 'Pago no encontrado'], 404);
            return;
        }

        $clienteId = (int)($_POST['cliente_id'] ?? 0);
        $monto = trim((string)($_POST['monto'] ?? ''));
        $fechaPago = trim((string)($_POST['fecha_pago'] ?? ''));
        $metodoPago = trim((string)($_POST['metodo_pago'] ?? ''));
        $estado = trim((string)($_POST['estado'] ?? ''));
        $numeroFactura = trim((string)($_POST['numero_factura'] ?? ($pagoActual['numero_factura'] ?? '')));
        $observaciones = trim((string)($_POST['observaciones'] ?? ''));

        $errores = [];
        if ($clienteId <= 0) $errores[] = 'El cliente es obligatorio';
        if ($monto === '' || !is_numeric($monto) || (float)$monto <= 0) $errores[] = 'El monto debe ser mayor a cero';
        if ($fechaPago === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaPago) || strtotime($fechaPago) === false) $errores[] = 'La fecha de pago no es válida';
        if (!in_array($metodoPago, ['transferencia', 'efectivo', 'paypal', 'tarjeta'], true)) $errores[] = 'El método de pago no es válido';
        if (!in_array($estado, ['pagado', 'pendiente', 'vencido'], true)) $errores[] = 'El estado no es válido';

        if ($errores) {
            $this->sendJson(['success' => false, 'message' => implode('. ', $errores)], 422);
            return;
        }

        try {
            $pagoModel->id = $pagoId;
            $pagoModel->cliente_id = $clienteId;
            $pagoModel->monto = $monto;
            $pagoModel->fecha_pago = $fechaPago;
            $pagoModel->metodo_pago = $metodoPago;
            $pagoModel->estado = $estado;
            $pagoModel->numero_factura = $numeroFactura !== '' ? $numeroFactura : ($pagoActual['numero_factura'] ?? '');
            $pagoModel->observaciones = $observaciones;

            if ($pagoModel->update()) {
                $updated = $pagoModel->getById($pagoId);
                $this->sendJson([
                    'success' => true,
                    'message' => 'Pago actualizado exitosamente',
                    'data' => $updated,
                ]);
                return;
            }

            $this->sendJson(['success' => false, 'message' => 'No se pudo actualizar el pago'], 500);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 409);
        } catch (PDOException $e) {
            error_log('PagoController@edit database error (pago ' . $pagoId . '): ' . $e->getMessage());
            $this->sendJson(['success' => false, 'message' => 'Error de base de datos al actualizar el pago'], 500);
        } catch (Throwable $e) {
            error_log('PagoController@edit unexpected error (pago ' . $pagoId . '): ' . $e->getMessage());
            $this->sendJson(['success' => false, 'message' => 'Error inesperado al actualizar el pago'], 500);
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJson(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        $pagoId = (int)$id;
        if ($pagoId <= 0) {
            $this->sendJson(['success' => false, 'message' => 'ID de pago inválido'], 400);
            return;
        }

        try {
            $pagoModel = new Pago();
            $pago = $pagoModel->getById($pagoId);
            if (!$pago) {
                $this->sendJson(['success' => false, 'message' => 'Pago no encontrado'], 404);
                return;
            }

            if ($pagoModel->delete($pagoId)) {
                $this->sendJson([
                    'success' => true,
                    'message' => 'Pago eliminado exitosamente',
                    'data' => ['pago_id' => $pagoId],
                ]);
                return;
            }

            $this->sendJson(['success' => false, 'message' => 'No se pudo eliminar el pago'], 500);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 409);
        } catch (PDOException $e) {
            error_log('PagoController@delete database error (pago ' . $pagoId . '): ' . $e->getMessage());
            $this->sendJson(['success' => false, 'message' => 'Error de base de datos al eliminar el pago'], 500);
        } catch (Throwable $e) {
            error_log('PagoController@delete unexpected error (pago ' . $pagoId . '): ' . $e->getMessage());
            $this->sendJson(['success' => false, 'message' => 'Error inesperado al eliminar el pago'], 500);
        }
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

    private function sendJson(array $payload, int $statusCode = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($payload);
    }
}
?>
