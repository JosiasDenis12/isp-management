<?php

require_once 'models/Cliente.php';

class ClienteController
{
    public function index()
    {
        $clienteModel = new Cliente();

        $clientes = $clienteModel->getAllConEstadoSuscripcion();

        $data = [
            'title' => 'Gestión de Clientes - ' . APP_NAME,
            'clientes' => $clientes
        ];

        $this->loadView('clientes/index', $data);
    }

    public function create()
    {
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $required_fields = [
                'nombre',
                'direccion',
                'telefono',
                'estado',
                'tipo_conexion',
                'fecha_contratacion',
                'dia_corte',
                'plan_mensual',
                'megas_contratados'
            ];

            $missing_fields = [];

            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    $missing_fields[] = $field;
                }
            }

            if (!empty($missing_fields)) {

                $error = 'Los siguientes campos son obligatorios: '
                    . implode(', ', $missing_fields);

            } else {

                $clienteModel = new Cliente();

                try {

                    $clienteModel->nombre =
                        trim($_POST['nombre']);

                    $clienteModel->direccion =
                        trim($_POST['direccion']);

                    $clienteModel->telefono =
                        trim($_POST['telefono']);

                    $clienteModel->email =
                        trim($_POST['email'] ?? '');

                    $clienteModel->estado =
                        $_POST['estado'];

                    $clienteModel->tipo_conexion =
                        $_POST['tipo_conexion'];

                    $clienteModel->fecha_contratacion =
                        $_POST['fecha_contratacion'];

                    $clienteModel->dia_corte =
                        (int) ($_POST['dia_corte'] ?? 0);

                    $clienteModel->plan_mensual =
                        $_POST['plan_mensual'];

                    $clienteModel->megas_contratados =
                        (int) ($_POST['megas_contratados'] ?? 0);

                    if (
                        $clienteModel->dia_corte < 0 ||
                        $clienteModel->dia_corte > 31
                    ) {
                        throw new Exception(
                            'El día de corte debe estar entre 1 y 31'
                        );
                    }

                    if ($clienteModel->megas_contratados < 1) {
                        throw new Exception(
                            'Los megas contratados deben ser mayores a 0'
                        );
                    }

                    if ($clienteModel->create()) {

                        header(
                            'Location: '
                            . url('clientes')
                            . '?success='
                            . urlencode(
                                'Cliente creado exitosamente'
                            )
                        );

                        exit;

                    } else {

                        $error =
                            'Error al crear el cliente. '
                            . 'Por favor, intente nuevamente.';
                    }

                } catch (Throwable $e) {

                    $error =
                        'Error al procesar los datos: '
                        . $e->getMessage();
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

    public function show($id)
    {
        $clienteModel = new Cliente();

        $cliente = $clienteModel->getById($id);

        if (!$cliente) {

            header('HTTP/1.0 404 Not Found');

            $this->loadView('errors/404');

            return;
        }

        require_once 'models/Pago.php';

        $pagoModel = new Pago();

        $historialPagos =
            $pagoModel->getHistorialCliente($id);

        $estadisticasPagos =
            $pagoModel->getEstadisticasCliente($id);

        $data = [
            'title' =>
                'Cliente: '
                . $cliente['nombre']
                . ' - '
                . APP_NAME,

            'cliente' => $cliente,

            'historialPagos' =>
                $historialPagos,

            'estadisticasPagos' =>
                $estadisticasPagos
        ];

        $this->loadView('clientes/show', $data);
    }

    public function edit($id)
    {
        $clienteModel = new Cliente();

        $cliente =
            $clienteModel->getById($id);

        if (!$cliente) {

            header('HTTP/1.0 404 Not Found');

            $this->loadView('errors/404');

            return;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $required_fields = [
                'nombre',
                'direccion',
                'telefono',
                'estado',
                'tipo_conexion',
                'fecha_contratacion',
                'dia_corte',
                'plan_mensual',
                'megas_contratados'
            ];

            $missing_fields = [];

            foreach ($required_fields as $field) {

                if (empty($_POST[$field])) {
                    $missing_fields[] = $field;
                }
            }

            if (!empty($missing_fields)) {

                $error =
                    'Los siguientes campos son obligatorios: '
                    . implode(', ', $missing_fields);

            } else {

                try {

                    $clienteModel->id =
                        (int) $id;

                    $clienteModel->nombre =
                        trim($_POST['nombre']);

                    $clienteModel->direccion =
                        trim($_POST['direccion']);

                    $clienteModel->telefono =
                        trim($_POST['telefono']);

                    $clienteModel->email =
                        trim($_POST['email'] ?? '');

                    $clienteModel->estado =
                        $_POST['estado'];

                    $clienteModel->tipo_conexion =
                        $_POST['tipo_conexion'];

                    $clienteModel->fecha_contratacion =
                        $_POST['fecha_contratacion'];

                    $clienteModel->dia_corte =
                        (int) ($_POST['dia_corte'] ?? 0);

                    $clienteModel->plan_mensual =
                        $_POST['plan_mensual'];

                    $clienteModel->megas_contratados =
                        (int) ($_POST['megas_contratados'] ?? 0);

                    if (
                        $clienteModel->dia_corte < 0 ||
                        $clienteModel->dia_corte > 31
                    ) {
                        throw new Exception(
                            'El día de corte debe estar entre 1 y 31'
                        );
                    }

                    if ($clienteModel->megas_contratados < 1) {
                        throw new Exception(
                            'Los megas contratados deben ser mayores a 0'
                        );
                    }

                    if ($clienteModel->update()) {

                        header(
                            'Location: '
                            . url('clientes/' . $id)
                            . '?success='
                            . urlencode(
                                'Cliente actualizado exitosamente'
                            )
                        );

                        exit;

                    } else {

                        $error =
                            'Error al actualizar el cliente. '
                            . 'Por favor, intente nuevamente.';
                    }

                } catch (Throwable $e) {

                    $error =
                        'Error al procesar los datos: '
                        . $e->getMessage();
                }
            }
        }

        $data = [
            'title' =>
                'Editar Cliente: '
                . $cliente['nombre']
                . ' - '
                . APP_NAME,

            'cliente' => $cliente,

            'error' => $error
        ];

        $this->loadView('clientes/edit', $data);
    }

    /*
     * =====================================================
     * ELIMINAR CLIENTE
     * =====================================================
     */
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            if ($this->isAjaxRequest()) {

                $this->sendJson([
                    'success' => false,
                    'message' => 'Método no permitido'
                ], 405);

                return;
            }

            header(
                'Location: ' . url('clientes')
            );

            exit;
        }

        $clienteId = (int) $id;

        if ($clienteId <= 0) {

            if ($this->isAjaxRequest()) {

                $this->sendJson([
                    'success' => false,
                    'message' => 'Cliente inválido'
                ], 400);

                return;
            }

            header(
                'Location: '
                . url('clientes')
                . '?error='
                . urlencode('Cliente inválido')
            );

            exit;
        }

        try {

            $clienteModel = new Cliente();

            $resultado =
                $clienteModel->deleteWithDependencies(
                    $clienteId
                );

            if ($this->isAjaxRequest()) {

                $this->sendJson([
                    'success' => true,

                    'message' =>
                        'Cliente y registros asociados '
                        . 'eliminados exitosamente',

                    'data' => $resultado
                ]);

                return;
            }

            header(
                'Location: '
                . url('clientes')
                . '?success='
                . urlencode(
                    'Cliente y registros asociados '
                    . 'eliminados exitosamente'
                )
            );

            exit;

        } catch (InvalidArgumentException $e) {

            if ($this->isAjaxRequest()) {

                $this->sendJson([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);

                return;
            }

            header(
                'Location: '
                . url('clientes')
                . '?error='
                . urlencode($e->getMessage())
            );

            exit;

        } catch (RuntimeException $e) {

            /*
             * IMPORTANTE:
             * Mostramos el error REAL.
             * No ocultamos el mensaje de SQLite.
             */

            error_log(
                'ClienteController@delete RuntimeException '
                . '(cliente '
                . $clienteId
                . '): '
                . $e->getMessage()
            );

            if ($this->isAjaxRequest()) {

                $this->sendJson([
                    'success' => false,

                    'message' =>
                        'ERROR: '
                        . $e->getMessage()
                ], 500);

                return;
            }

            header(
                'Location: '
                . url('clientes')
                . '?error='
                . urlencode(
                    'ERROR: '
                    . $e->getMessage()
                )
            );

            exit;

        } catch (Throwable $e) {

            /*
             * AQUÍ DEBE APARECER EL ERROR REAL DE SQLITE
             */

            error_log(
                'ClienteController@delete ERROR '
                . '(cliente '
                . $clienteId
                . '): '
                . $e->getMessage()
            );

            $mensaje =
                'ERROR SQLITE: '
                . $e->getMessage();

            if ($this->isAjaxRequest()) {

                $this->sendJson([
                    'success' => false,
                    'message' => $mensaje
                ], 500);

                return;
            }

            header(
                'Location: '
                . url('clientes')
                . '?error='
                . urlencode($mensaje)
            );

            exit;
        }
    }

    /*
     * =====================================================
     * RESUMEN ANTES DE ELIMINAR
     * =====================================================
     */
    public function deleteSummary($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

            $this->sendJson([
                'success' => false,
                'message' => 'Método no permitido'
            ], 405);

            return;
        }

        $clienteId = (int) $id;

        if ($clienteId <= 0) {

            $this->sendJson([
                'success' => false,
                'message' => 'Cliente inválido'
            ], 400);

            return;
        }

        try {

            $clienteModel = new Cliente();

            $resumen =
                $clienteModel->getDeleteImpact(
                    $clienteId
                );

            $this->sendJson([
                'success' => true,
                'message' => 'Resumen generado',
                'data' => $resumen
            ]);

        } catch (InvalidArgumentException $e) {

            $this->sendJson([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (RuntimeException $e) {

            $this->sendJson([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);

        } catch (Throwable $e) {

            error_log(
                'ClienteController@deleteSummary error '
                . '(cliente '
                . $clienteId
                . '): '
                . $e->getMessage()
            );

            $this->sendJson([
                'success' => false,

                'message' =>
                    'ERROR SQLITE: '
                    . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus($id)
    {
        if (!isset($_GET['estado'])) {

            header(
                'Location: '
                . url('clientes/' . $id)
            );

            exit;
        }

        $nuevoEstado =
            $_GET['estado'];

        $estadosValidos = [
            'activo',
            'suspendido',
            'pendiente'
        ];

        if (
            !in_array(
                $nuevoEstado,
                $estadosValidos,
                true
            )
        ) {

            header(
                'Location: '
                . url('clientes/' . $id)
                . '?error='
                . urlencode(
                    'Estado no válido'
                )
            );

            exit;
        }

        $clienteModel = new Cliente();

        $cliente =
            $clienteModel->getById($id);

        if (!$cliente) {

            header(
                'HTTP/1.0 404 Not Found'
            );

            $this->loadView('errors/404');

            return;
        }

        try {

            $clienteModel->id =
                (int) $id;

            $clienteModel->nombre =
                $cliente['nombre'];

            $clienteModel->direccion =
                $cliente['direccion'];

            $clienteModel->telefono =
                $cliente['telefono'];

            $clienteModel->email =
                $cliente['email'];

            $clienteModel->estado =
                $nuevoEstado;

            $clienteModel->tipo_conexion =
                $cliente['tipo_conexion'];

            $clienteModel->fecha_contratacion =
                $cliente['fecha_contratacion'];

            $clienteModel->dia_corte =
                (int) (
                    $cliente['dia_corte']
                    ?? 5
                );

            $clienteModel->plan_mensual =
                $cliente['plan_mensual'];

            $clienteModel->megas_contratados =
                $cliente['megas_contratados']
                ?? null;

            if ($clienteModel->update()) {

                $mensaje =
                    'Estado del cliente cambiado a: '
                    . ucfirst($nuevoEstado);

                header(
                    'Location: '
                    . url('clientes/' . $id)
                    . '?success='
                    . urlencode($mensaje)
                );

            } else {

                header(
                    'Location: '
                    . url('clientes/' . $id)
                    . '?error='
                    . urlencode(
                        'Error al cambiar el estado'
                    )
                );
            }

        } catch (Throwable $e) {

            header(
                'Location: '
                . url('clientes/' . $id)
                . '?error='
                . urlencode(
                    'Error: '
                    . $e->getMessage()
                )
            );
        }

        exit;
    }

    /*
     * =====================================================
     * CARGAR VISTA
     * =====================================================
     */
    private function loadView(
        $view,
        $data = []
    ) {
        extract($data);

        require_once "views/{$view}.php";
    }

    /*
     * =====================================================
     * RESPUESTA JSON
     * =====================================================
     */
    private function sendJson(
        array $payload,
        int $statusCode = 200
    ): void {

        http_response_code($statusCode);

        header(
            'Content-Type: application/json; charset=utf-8'
        );

        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    /*
     * =====================================================
     * DETECTAR AJAX / JSON
     * =====================================================
     */
    private function isAjaxRequest(): bool
    {
        $requestedWith =
            strtolower(
                (string) (
                    $_SERVER[
                        'HTTP_X_REQUESTED_WITH'
                    ] ?? ''
                )
            );

        $accept =
            strtolower(
                (string) (
                    $_SERVER[
                        'HTTP_ACCEPT'
                    ] ?? ''
                )
            );

        return
            $requestedWith === 'xmlhttprequest'
            ||
            strpos(
                $accept,
                'application/json'
            ) !== false;
    }
}