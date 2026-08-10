<?php
require_once 'models/Cliente.php';

class ClienteController {
    
    public function index() {
        $clienteModel = new Cliente();
        $clientes = $clienteModel->getAllConEstadoSuscripcion();
        
        $data = [
            'title' => 'Gestión de Clientes - ' . APP_NAME,
            'clientes' => $clientes
        ];
        
        $this->loadView('clientes/index', $data);
    }
    
    public function create() {
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar campos obligatorios
            $required_fields = ['nombre', 'direccion', 'telefono', 'estado', 'tipo_conexion', 'fecha_contratacion', 'dia_corte', 'plan_mensual', 'megas_contratados'];
            $missing_fields = [];
            
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    $missing_fields[] = $field;
                }
            }
            
            if (!empty($missing_fields)) {
                $error = 'Los siguientes campos son obligatorios: ' . implode(', ', $missing_fields);
            } else {
                $clienteModel = new Cliente();
                
                try {
                    $clienteModel->nombre = trim($_POST['nombre']);
                    $clienteModel->direccion = trim($_POST['direccion']);
                    $clienteModel->telefono = trim($_POST['telefono']);
                    $clienteModel->email = trim($_POST['email'] ?? '');
                    $clienteModel->estado = $_POST['estado'];
                    $clienteModel->tipo_conexion = $_POST['tipo_conexion'];
                    $clienteModel->fecha_contratacion = $_POST['fecha_contratacion'];
                    $clienteModel->dia_corte = (int)($_POST['dia_corte'] ?? 0);
                    $clienteModel->plan_mensual = $_POST['plan_mensual'];
                    $clienteModel->megas_contratados = (int)($_POST['megas_contratados'] ?? 0);

                    if ($clienteModel->dia_corte < 0 || $clienteModel->dia_corte > 31) {
                        $error = 'El día de corte debe estar entre 1 y 31';
                        throw new Exception($error);
                    }
                    
                    if ($clienteModel->megas_contratados < 1) {
                        $error = 'Los megas contratados deben ser mayores a 0';
                        throw new Exception($error);
                    }
                    
                    if ($clienteModel->create()) {
                        // Redirigir con mensaje de éxito
                        $redirectUrl = url('clientes') . '?success=' . urlencode('Cliente creado exitosamente');
                        header('Location: ' . $redirectUrl);
                        exit;
                    } else {
                        $error = 'Error al crear el cliente. Por favor, intente nuevamente.';
                    }
                } catch (Exception $e) {
                    if ($error === null) {
                        $error = 'Error al procesar los datos: ' . $e->getMessage();
                    }
                }
            }
        }
        
        $data = [
            'title' => 'Nuevo Cliente - ' . APP_NAME,
            'error' => $error,
            'success' => $success
        ];
        
        $this->loadView('clientes/create', $data);
    }
    
    public function show($id) {
        $clienteModel = new Cliente();
        $cliente = $clienteModel->getById($id);
        
        if (!$cliente) {
            header('HTTP/1.0 404 Not Found');
            $this->loadView('errors/404');
            return;
        }
        
        // Obtener información de pagos del cliente
        require_once 'models/Pago.php';
        $pagoModel = new Pago();
        $historialPagos = $pagoModel->getHistorialCliente($id);
        $estadisticasPagos = $pagoModel->getEstadisticasCliente($id);
        
        $data = [
            'title' => 'Cliente: ' . $cliente['nombre'] . ' - ' . APP_NAME,
            'cliente' => $cliente,
            'historialPagos' => $historialPagos,
            'estadisticasPagos' => $estadisticasPagos
        ];
        
        $this->loadView('clientes/show', $data);
    }
    
    public function edit($id) {
        $clienteModel = new Cliente();
        $cliente = $clienteModel->getById($id);
        
        if (!$cliente) {
            header('HTTP/1.0 404 Not Found');
            $this->loadView('errors/404');
            return;
        }
        
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar campos obligatorios
            $required_fields = ['nombre', 'direccion', 'telefono', 'estado', 'tipo_conexion', 'fecha_contratacion', 'dia_corte', 'plan_mensual', 'megas_contratados'];
            $missing_fields = [];
            
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    $missing_fields[] = $field;
                }
            }
            
            if (!empty($missing_fields)) {
                $error = 'Los siguientes campos son obligatorios: ' . implode(', ', $missing_fields);
            } else {
                try {
                    $clienteModel->id = $id;
                    $clienteModel->nombre = trim($_POST['nombre']);
                    $clienteModel->direccion = trim($_POST['direccion']);
                    $clienteModel->telefono = trim($_POST['telefono']);
                    $clienteModel->email = trim($_POST['email'] ?? '');
                    $clienteModel->estado = $_POST['estado'];
                    $clienteModel->tipo_conexion = $_POST['tipo_conexion'];
                    $clienteModel->fecha_contratacion = $_POST['fecha_contratacion'];
                    $clienteModel->dia_corte = (int)($_POST['dia_corte'] ?? 0);
                    $clienteModel->plan_mensual = $_POST['plan_mensual'];
                    $clienteModel->megas_contratados = (int)($_POST['megas_contratados'] ?? 0);

                    if ($clienteModel->dia_corte < 0 || $clienteModel->dia_corte > 31) {
                        $error = 'El día de corte debe estar entre 1 y 31';
                        throw new Exception($error);
                    }
                    
                    if ($clienteModel->megas_contratados < 1) {
                        $error = 'Los megas contratados deben ser mayores a 0';
                        throw new Exception($error);
                    }
                    
                    if ($clienteModel->update()) {
                        header('Location: ' . url('clientes/' . $id) . '?success=' . urlencode('Cliente actualizado exitosamente'));
                        exit;
                    } else {
                        $error = 'Error al actualizar el cliente. Por favor, intente nuevamente.';
                    }
                } catch (Exception $e) {
                    if ($error === null) {
                        $error = 'Error al procesar los datos: ' . $e->getMessage();
                    }
                }
            }
        }
        
        $data = [
            'title' => 'Editar Cliente: ' . $cliente['nombre'] . ' - ' . APP_NAME,
            'cliente' => $cliente,
            'error' => $error
        ];
        
        $this->loadView('clientes/edit', $data);
    }
    
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjaxRequest()) {
                $this->sendJson(['success' => false, 'message' => 'Método no permitido'], 405);
                return;
            }
            header('Location: ' . url('clientes'));
            exit;
        }

        $clienteId = (int)$id;
        if ($clienteId <= 0) {
            if ($this->isAjaxRequest()) {
                $this->sendJson(['success' => false, 'message' => 'Cliente inválido'], 400);
                return;
            }
            header('Location: ' . url('clientes') . '?error=' . urlencode('Cliente inválido'));
            exit;
        }

        $clienteModel = new Cliente();

        try {
            $resultado = $clienteModel->deleteWithDependencies($clienteId);

            if ($this->isAjaxRequest()) {
                $this->sendJson([
                    'success' => true,
                    'message' => 'Cliente y registros asociados eliminados exitosamente',
                    'data' => $resultado,
                ]);
                return;
            }

            header('Location: ' . url('clientes') . '?success=' . urlencode('Cliente y registros asociados eliminados exitosamente'));
        } catch (InvalidArgumentException $e) {
            if ($this->isAjaxRequest()) {
                $this->sendJson(['success' => false, 'message' => $e->getMessage()], 400);
                return;
            }
            header('Location: ' . url('clientes') . '?error=' . urlencode($e->getMessage()));
        } catch (RuntimeException $e) {
            if ($this->isAjaxRequest()) {
                $this->sendJson(['success' => false, 'message' => $e->getMessage()], 404);
                return;
            }
            header('Location: ' . url('clientes') . '?error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            error_log('ClienteController@delete error (cliente ' . $clienteId . '): ' . $e->getMessage());

            $mensaje = 'No fue posible eliminar el cliente. No se realizaron cambios en la base de datos. Intenta nuevamente.';

            if ($this->isAjaxRequest()) {
                $this->sendJson(['success' => false, 'message' => $mensaje], 500);
                return;
            }

            header('Location: ' . url('clientes') . '?error=' . urlencode($mensaje));
        }

        exit;
    }

    public function deleteSummary($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJson(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }

        $clienteId = (int)$id;
        if ($clienteId <= 0) {
            $this->sendJson(['success' => false, 'message' => 'Cliente inválido'], 400);
            return;
        }

        try {
            $resumen = (new Cliente())->getDeleteImpact($clienteId);
            $this->sendJson([
                'success' => true,
                'message' => 'Resumen generado',
                'data' => $resumen,
            ]);
        } catch (InvalidArgumentException $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Throwable $e) {
            error_log('ClienteController@deleteSummary error (cliente ' . $clienteId . '): ' . $e->getMessage());
            $this->sendJson(['success' => false, 'message' => 'No fue posible obtener el resumen de eliminación'], 500);
        }
    }
    
    public function updateStatus($id) {
        if (!isset($_GET['estado'])) {
            header('Location: ' . url('clientes/' . $id));
            exit;
        }
        
        $nuevoEstado = $_GET['estado'];
        $estadosValidos = ['activo', 'suspendido', 'pendiente'];
        
        if (!in_array($nuevoEstado, $estadosValidos)) {
            header('Location: ' . url('clientes/' . $id) . '?error=' . urlencode('Estado no válido'));
            exit;
        }
        
        $clienteModel = new Cliente();
        $cliente = $clienteModel->getById($id);
        
        if (!$cliente) {
            header('HTTP/1.0 404 Not Found');
            $this->loadView('errors/404');
            return;
        }
        
        try {
            $clienteModel->id = $id;
            $clienteModel->nombre = $cliente['nombre'];
            $clienteModel->direccion = $cliente['direccion'];
            $clienteModel->telefono = $cliente['telefono'];
            $clienteModel->email = $cliente['email'];
            $clienteModel->estado = $nuevoEstado;
            $clienteModel->tipo_conexion = $cliente['tipo_conexion'];
            $clienteModel->fecha_contratacion = $cliente['fecha_contratacion'];
            $clienteModel->dia_corte = (int)($cliente['dia_corte'] ?? 5);
            $clienteModel->plan_mensual = $cliente['plan_mensual'];
            $clienteModel->megas_contratados = $cliente['megas_contratados'] ?? null;
            
            if ($clienteModel->update()) {
                $mensaje = 'Estado del cliente cambiado a: ' . ucfirst($nuevoEstado);
                header('Location: ' . url('clientes/' . $id) . '?success=' . urlencode($mensaje));
            } else {
                header('Location: ' . url('clientes/' . $id) . '?error=' . urlencode('Error al cambiar el estado'));
            }
        } catch (Exception $e) {
            header('Location: ' . url('clientes/' . $id) . '?error=' . urlencode('Error: ' . $e->getMessage()));
        }
        
        exit;
    }

    private function loadView($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }

    private function sendJson(array $payload, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    private function isAjaxRequest(): bool {
        return strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
            || strpos((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false;
    }
}
?>
