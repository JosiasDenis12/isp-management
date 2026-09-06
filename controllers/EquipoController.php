<?php
require_once 'models/Equipo.php';
require_once 'models/Cliente.php';
require_once 'models/VisitaTecnica.php';
require_once 'lib/ReporteEquipoPDF.php';

class EquipoController {
    
    public function index() {
        $equipoModel = new Equipo();
        // Conserva una única consulta para la lista y para los accesos filtrados
        // desde las alertas del dashboard.
        $equipos = $equipoModel->getReporteEquiposInstalados([
            'estado_tecnico' => (string)($_GET['estado_tecnico'] ?? ''),
        ]);
        
        $data = [
            'title' => 'Equipos Técnicos - ' . APP_NAME,
            'equipos' => $equipos
        ];
        
        $this->loadView('equipos/index', $data);
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $equipoModel = new Equipo();

            $modoRegistro = $_POST['modo_registro'] ?? 'individual';
            $clienteId = $_POST['cliente_id'] ?? null;
            $fechaInstalacion = $_POST['fecha_instalacion'] ?? null;

            if ($modoRegistro === 'instalacion_completa') {
                $equipos = [
                    [
                        'tipo_equipo' => 'antena',
                        'marca' => trim((string)($_POST['antena_marca'] ?? '')),
                        'modelo' => trim((string)($_POST['antena_modelo'] ?? '')),
                        'numero_serie' => trim((string)($_POST['antena_numero_serie'] ?? '')),
                        'estado_tecnico' => $_POST['antena_estado_tecnico'] ?? 'operativo',
                        'observaciones_tecnico' => trim((string)($_POST['antena_observaciones'] ?? '')),
                        'mac_address' => trim((string)($_POST['antena_mac_address'] ?? '')),
                        'direccion_ip' => trim((string)($_POST['antena_direccion_ip'] ?? '')),
                        'password_acceso' => trim((string)($_POST['antena_password_acceso'] ?? '')),
                    ],
                    [
                        'tipo_equipo' => 'modem',
                        'marca' => trim((string)($_POST['modem_marca'] ?? '')),
                        'modelo' => trim((string)($_POST['modem_modelo'] ?? '')),
                        'numero_serie' => trim((string)($_POST['modem_numero_serie'] ?? '')),
                        'estado_tecnico' => $_POST['modem_estado_tecnico'] ?? 'operativo',
                        'observaciones_tecnico' => trim((string)($_POST['modem_observaciones'] ?? '')),
                        'mac_address' => trim((string)($_POST['modem_mac_address'] ?? '')),
                        'direccion_ip' => trim((string)($_POST['modem_direccion_ip'] ?? '')),
                        'ssid' => trim((string)($_POST['modem_ssid'] ?? '')),
                        'password_acceso' => trim((string)($_POST['modem_password_acceso'] ?? '')),
                        'usuario_acceso' => trim((string)($_POST['modem_usuario_acceso'] ?? '')),
                        'acceso_habilitado' => isset($_POST['modem_acceso_habilitado']) ? 1 : 0,
                    ],
                ];

                if ($this->validarInstalacionCompleta($clienteId, $fechaInstalacion, $equipos, $error)) {
                    if ($equipoModel->createInstalacionCompleta($clienteId, $fechaInstalacion, trim((string)($_POST['observaciones_tecnico'] ?? '')), $equipos)) {
                        header('Location: ' . url('equipos') . '?success=Instalación registrada con antena y módem');
                        exit;
                    }
                    $error = 'Error al registrar la instalación completa';
                }
            } else {
                $tipoEquipo = $_POST['tipo_equipo'] ?? '';

                $equipoModel->cliente_id = $clienteId;
                $equipoModel->instalacion_id = null;
                $equipoModel->tipo_equipo = $tipoEquipo;
                $equipoModel->marca = $_POST['marca'];
                $equipoModel->modelo = $_POST['modelo'];
                $equipoModel->numero_serie = $_POST['numero_serie'];
                $equipoModel->estado_tecnico = $_POST['estado_tecnico'];
                $equipoModel->fecha_instalacion = $fechaInstalacion;
                $equipoModel->observaciones_tecnico = $_POST['observaciones_tecnico'];
                $equipoModel->mac_address = in_array($tipoEquipo, ['antena', 'modem'], true) ? trim((string)($_POST['mac_address'] ?? '')) : null;
                $equipoModel->direccion_ip = in_array($tipoEquipo, ['antena', 'modem'], true) ? trim((string)($_POST['direccion_ip'] ?? '')) : null;
                $equipoModel->password_acceso = in_array($tipoEquipo, ['antena', 'modem'], true) ? trim((string)($_POST['password_acceso'] ?? '')) : null;
                $equipoModel->ssid = $tipoEquipo === 'modem' ? trim((string)($_POST['ssid'] ?? '')) : null;
                $equipoModel->usuario_acceso = $tipoEquipo === 'modem' ? trim((string)($_POST['usuario_acceso'] ?? '')) : null;
                $equipoModel->acceso_habilitado = ($tipoEquipo === 'modem' && isset($_POST['acceso_habilitado'])) ? 1 : 0;

                if ($this->validarEquipoIndividual($equipoModel, $error)) {
                    if ($equipoModel->create()) {
                        // El alta consecutiva conserva el contexto de la instalación para
                        // que el técnico pueda registrar el siguiente dispositivo sin repetirlo.
                        $contexto = http_build_query([
                            'registrado' => 1,
                            'cliente_id' => (int)$clienteId,
                            'fecha_instalacion' => $fechaInstalacion,
                        ]);
                        header('Location: ' . url('equipos/create') . '?' . $contexto);
                        exit;
                    }
                    $error = 'Error al registrar el equipo';
                }
            }
        }
        
        $clienteModel = new Cliente();
        $clientes = $clienteModel->getAll();
        
        $data = [
            'title' => 'Registrar Equipo - ' . APP_NAME,
            'clientes' => $clientes,
            'error' => $error ?? null
        ];
        
        $this->loadView('equipos/create', $data);
    }

    public function edit($id) {
        $equipoModel = new Equipo();
        $equipo = $equipoModel->getById($id);

        if (!$equipo) {
            header('Location: ' . url('equipos') . '?error=Equipo no encontrado');
            exit;
        }

        $equipo['tipo_equipo'] = $this->normalizarTipoEquipo($equipo['tipo_equipo'] ?? '');

        $clienteModel = new Cliente();
        $clientes = $clienteModel->getAll();
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->hidratarEquipoDesdePost($equipoModel, $_POST, $equipo);

            if ($this->validarEquipoIndividual($equipoModel, $error)) {
                if ($equipoModel->update()) {
                    header('Location: ' . url('equipos/' . $id) . '?success=' . urlencode('Equipo actualizado exitosamente'));
                    exit;
                }

                $error = 'Error al actualizar el equipo';
            }

            $equipo = array_merge($equipo, [
                'cliente_id' => $equipoModel->cliente_id,
                'tipo_equipo' => $equipoModel->tipo_equipo,
                'marca' => $equipoModel->marca,
                'modelo' => $equipoModel->modelo,
                'numero_serie' => $equipoModel->numero_serie,
                'estado_tecnico' => $equipoModel->estado_tecnico,
                'fecha_instalacion' => $equipoModel->fecha_instalacion,
                'observaciones_tecnico' => $equipoModel->observaciones_tecnico,
                'instalacion_id' => $equipoModel->instalacion_id,
                'mac_address' => $equipoModel->mac_address,
                'direccion_ip' => $equipoModel->direccion_ip,
                'password_acceso' => $equipoModel->password_acceso,
                'ssid' => $equipoModel->ssid,
                'usuario_acceso' => $equipoModel->usuario_acceso,
                'acceso_habilitado' => $equipoModel->acceso_habilitado,
            ]);
        }

        $data = [
            'title' => 'Editar Equipo - ' . APP_NAME,
            'equipo' => $equipo,
            'clientes' => $clientes,
            'error' => $error,
        ];

        $this->loadView('equipos/edit', $data);
    }

    private function hidratarEquipoDesdePost($equipoModel, $post, $equipoActual = []) {
        $tipoEquipo = $this->normalizarTipoEquipo($post['tipo_equipo'] ?? '');
        $esEquipoRed = in_array($tipoEquipo, ['antena', 'modem'], true);
        $esModem = $tipoEquipo === 'modem';

        $equipoModel->id = $equipoActual['id'] ?? null;
        $equipoModel->cliente_id = $post['cliente_id'] ?? null;
        $equipoModel->instalacion_id = $equipoActual['instalacion_id'] ?? null;
        $equipoModel->tipo_equipo = $tipoEquipo;
        $equipoModel->marca = trim((string)($post['marca'] ?? ''));
        $equipoModel->modelo = trim((string)($post['modelo'] ?? ''));
        $equipoModel->numero_serie = trim((string)($post['numero_serie'] ?? ''));
        $equipoModel->estado_tecnico = $post['estado_tecnico'] ?? '';
        $equipoModel->fecha_instalacion = $post['fecha_instalacion'] ?? null;
        $equipoModel->observaciones_tecnico = trim((string)($post['observaciones_tecnico'] ?? ''));
        $equipoModel->mac_address = $esEquipoRed ? trim((string)($post['mac_address'] ?? '')) : null;
        $equipoModel->direccion_ip = $esEquipoRed ? trim((string)($post['direccion_ip'] ?? '')) : null;
        $equipoModel->password_acceso = $esEquipoRed ? trim((string)($post['password_acceso'] ?? '')) : null;
        $equipoModel->ssid = $esModem ? trim((string)($post['ssid'] ?? '')) : null;
        $equipoModel->usuario_acceso = $esModem ? trim((string)($post['usuario_acceso'] ?? '')) : null;
        $equipoModel->acceso_habilitado = ($esModem && isset($post['acceso_habilitado'])) ? 1 : 0;
    }

    private function normalizarTipoEquipo($tipo) {
        $tipo = trim((string)$tipo);
        $lower = function_exists('mb_strtolower') ? mb_strtolower($tipo, 'UTF-8') : strtolower($tipo);
        $compact = preg_replace('/[^a-z0-9_]+/', '', $lower);

        if ($compact === '') return '';
        if (strpos($compact, 'antena') !== false) return 'antena';
        if (strpos($compact, 'modem') !== false || strpos($compact, 'mdem') !== false || strpos($compact, 'dem') !== false) return 'modem';
        if (strpos($compact, 'router') !== false) return 'router';
        if (strpos($compact, 'switch') !== false) return 'switch';
        if (strpos($compact, 'access') !== false || strpos($compact, 'ap') === 0) return 'access_point';

        $allowedCompact = ['router', 'modem', 'switch', 'access_point', 'antena', 'otro'];
        if (in_array($compact, $allowedCompact, true)) return $compact;

        if ($lower === '') return '';
        if (strpos($lower, 'antena') !== false) return 'antena';
        if (strpos($lower, 'modem') !== false || strpos($lower, 'módem') !== false || strpos($lower, 'm¢dem') !== false || strpos($lower, 'dem') !== false) return 'modem';
        if (strpos($lower, 'router') !== false) return 'router';
        if (strpos($lower, 'switch') !== false) return 'switch';
        if (strpos($lower, 'access') !== false || strpos($lower, 'ap') === 0) return 'access_point';

        $allowed = ['router', 'modem', 'switch', 'access_point', 'antena', 'otro'];
        return in_array($lower, $allowed, true) ? $lower : 'otro';
    }

    private function validarEquipoIndividual($equipo, &$error) {
        if (empty($equipo->cliente_id) || empty($equipo->tipo_equipo) || empty($equipo->marca) || empty($equipo->modelo) || empty($equipo->estado_tecnico)) {
            $error = 'Cliente, tipo de equipo, marca, modelo y estado son obligatorios';
            return false;
        }

        if (in_array($equipo->tipo_equipo, ['antena', 'modem'], true)) {
            if (empty($equipo->mac_address) || empty($equipo->direccion_ip) || empty($equipo->password_acceso)) {
                $error = 'MAC Address, Dirección IP y Contraseña son obligatorios para antenas y módems';
                return false;
            }
        }

        if ($equipo->tipo_equipo === 'modem' && (empty($equipo->ssid) || empty($equipo->usuario_acceso))) {
            $error = 'SSID y Usuario de acceso son obligatorios para módems';
            return false;
        }

        return true;
    }

    private function validarInstalacionCompleta($clienteId, $fechaInstalacion, $equipos, &$error) {
        if (empty($clienteId) || empty($fechaInstalacion)) {
            $error = 'Cliente y fecha de instalación son obligatorios para una instalación completa';
            return false;
        }

        foreach ($equipos as $equipo) {
            $tipo = $equipo['tipo_equipo'] ?? '';
            if (empty($equipo['marca']) || empty($equipo['modelo']) || empty($equipo['mac_address']) || empty($equipo['direccion_ip']) || empty($equipo['password_acceso'])) {
                $error = 'Marca, modelo, MAC Address, Dirección IP y Contraseña son obligatorios para antena y módem';
                return false;
            }

            if ($tipo === 'modem' && (empty($equipo['ssid']) || empty($equipo['usuario_acceso']))) {
                $error = 'SSID y Usuario de acceso son obligatorios para el módem';
                return false;
            }
        }

        return true;
    }
    
    public function show($id) {
        $equipoModel = new Equipo();
        $equipo = $equipoModel->getById($id);
        
        if (!$equipo) {
            header('Location: ' . url('equipos') . '?error=Equipo no encontrado');
            exit;
        }

        $visitaModel = new VisitaTecnica();
        $visitas = $visitaModel->getByEquipoId($id);
        $mantenimientos = array_values(array_filter($visitas, function($v) {
            return ($v['tipo_visita'] ?? '') === 'mantenimiento';
        }));

        $ahoraTs = time();
        $proximoMantenimiento = null;
        foreach ($mantenimientos as $m) {
            if (($m['estado'] ?? '') !== 'programada') {
                continue;
            }
            $ts = strtotime($m['fecha_visita'] ?? '');
            if ($ts === false) {
                continue;
            }
            if ($ts >= $ahoraTs && ($proximoMantenimiento === null || $ts < strtotime($proximoMantenimiento['fecha_visita']))) {
                $proximoMantenimiento = $m;
            }
        }
        
        $data = [
            'title' => 'Detalles del Equipo - ' . APP_NAME,
            'equipo' => $equipo,
            'mantenimientos' => $mantenimientos,
            'proximoMantenimiento' => $proximoMantenimiento
        ];
        
        $this->loadView('equipos/show', $data);
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('equipos/' . $id));
            exit;
        }

        $equipoModel = new Equipo();
        $equipo = $equipoModel->getById($id);
        if (!$equipo) {
            header('Location: ' . url('equipos') . '?error=' . urlencode('Equipo no encontrado'));
            exit;
        }

        try {
            $equipoModel->id = (int)$id;
            $equipoModel->delete();
            header('Location: ' . url('equipos') . '?success=' . urlencode('Equipo y visitas relacionadas eliminados correctamente'));
            exit;
        } catch (Throwable $e) {
            error_log('EquipoController@delete error (equipo ' . (int)$id . '): ' . $e->getMessage());
        }

        header('Location: ' . url('equipos/' . $id) . '?error=' . urlencode('No fue posible eliminar el equipo. No se realizaron cambios.'));
        exit;
    }

    public function programarMantenimiento($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('equipos/' . $id) . '?error=Método no permitido');
            exit;
        }

        $equipoModel = new Equipo();
        $equipo = $equipoModel->getById($id);

        if (!$equipo) {
            header('Location: ' . url('equipos') . '?error=Equipo no encontrado');
            exit;
        }

        $tecnico = trim((string)($_POST['tecnico_nombre'] ?? ''));
        $fecha = $_POST['fecha_visita'] ?? '';
        $observaciones = trim((string)($_POST['observaciones'] ?? ''));

        if ($tecnico === '' || trim((string)$fecha) === '') {
            header('Location: ' . url('equipos/' . $id) . '?error=Fecha y técnico son obligatorios');
            exit;
        }

        $visitaModel = new VisitaTecnica();

        $existente = $visitaModel->getFutureScheduledMantenimientoByEquipoId($equipo['id']);
        if (!empty($existente)) {
            $ts = strtotime((string)($existente['fecha_visita'] ?? ''));
            $fechaTxt = $ts ? date('d/m/Y H:i', $ts) : (string)($existente['fecha_visita'] ?? '');
            $msg = 'Ya existe un mantenimiento programado futuro para este equipo (' . $fechaTxt . '). ' .
                   'Cancele o edite el mantenimiento existente antes de programar otro.';
            header('Location: ' . url('equipos/' . $id) . '?error=' . urlencode($msg));
            exit;
        }

        $visitaModel->cliente_id = $equipo['cliente_id'];
        $visitaModel->equipo_id = $equipo['id'];
        $visitaModel->fecha_visita = $fecha;
        $visitaModel->tipo_visita = 'mantenimiento';
        $visitaModel->tecnico_nombre = $tecnico;
        $visitaModel->observaciones = $observaciones;
        $visitaModel->estado = 'programada';

        if ($visitaModel->create()) {
            header('Location: ' . url('equipos/' . $id) . '?success=' . urlencode('Mantenimiento programado exitosamente'));
            exit;
        }

        header('Location: ' . url('equipos/' . $id) . '?error=' . urlencode('No se pudo programar el mantenimiento'));
        exit;
    }
    
    public function visitas($id) {
        $equipoModel = new Equipo();
        $equipo = $equipoModel->getById($id);
        
        if (!$equipo) {
            header('Location: ' . url('equipos') . '?error=Equipo no encontrado');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $visitaModel = new VisitaTecnica();

            $visitaModel->cliente_id = $equipo['cliente_id'];
            $visitaModel->equipo_id = $equipo['id'];
            $visitaModel->fecha_visita = $_POST['fecha_visita'] ?? '';
            $visitaModel->tipo_visita = $_POST['tipo_visita'] ?? '';
            $visitaModel->tecnico_nombre = trim((string)($_POST['tecnico_nombre'] ?? ''));
            $visitaModel->observaciones = trim((string)($_POST['observaciones_visita'] ?? ''));
            $visitaModel->estado = $_POST['estado_visita'] ?? 'programada';

            if ($visitaModel->tecnico_nombre === '') {
                header('Location: ' . url('equipos/' . $id . '/visitas') . '?error=El técnico es obligatorio');
                exit;
            }

            if ($visitaModel->create()) {
                header('Location: ' . url('equipos/' . $id . '/visitas') . '?success=Visita técnica registrada exitosamente');
                exit;
            }

            header('Location: ' . url('equipos/' . $id . '/visitas') . '?error=No se pudo registrar la visita técnica');
            exit;
        }
        
        $visitaModel = new VisitaTecnica();
        $visitas = $visitaModel->getByEquipoId($id);
        
        $data = [
            'title' => 'Visitas Técnicas - ' . APP_NAME,
            'equipo' => $equipo,
            'visitas' => $visitas
        ];
        
        $this->loadView('equipos/visitas', $data);
    }

    public function verVisita($equipoId, $visitaId) {
        $equipoModel = new Equipo();
        $equipo = $equipoModel->getById($equipoId);

        if (!$equipo) {
            header('Location: ' . url('equipos') . '?error=Equipo no encontrado');
            exit;
        }

        $visitaModel = new VisitaTecnica();
        $visita = $visitaModel->getById($visitaId);

        if (!$visita || (int)($visita['equipo_id'] ?? 0) !== (int)$equipoId) {
            header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?error=Visita no encontrada');
            exit;
        }

        $data = [
            'title' => 'Detalle de Visita - ' . APP_NAME,
            'equipo' => $equipo,
            'visita' => $visita,
        ];

        $this->loadView('equipos/visita_detalle', $data);
    }

    public function editarVisita($equipoId, $visitaId) {
        $equipoModel = new Equipo();
        $equipo = $equipoModel->getById($equipoId);

        if (!$equipo) {
            header('Location: ' . url('equipos') . '?error=Equipo no encontrado');
            exit;
        }

        $visitaModel = new VisitaTecnica();
        $visita = $visitaModel->getById($visitaId);

        if (!$visita || (int)($visita['equipo_id'] ?? 0) !== (int)$equipoId) {
            header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?error=Visita no encontrada');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $visitaModel->fecha_visita = $_POST['fecha_visita'] ?? '';
            $visitaModel->tipo_visita = $_POST['tipo_visita'] ?? '';
            $visitaModel->tecnico_nombre = trim((string)($_POST['tecnico_nombre'] ?? ''));
            $visitaModel->observaciones = trim((string)($_POST['observaciones'] ?? ''));
            $visitaModel->estado = $_POST['estado'] ?? 'programada';

            if ($visitaModel->tecnico_nombre === '') {
                header('Location: ' . url('equipos/' . $equipoId . '/visitas/' . $visitaId . '/edit') . '?error=El técnico es obligatorio');
                exit;
            }

            if ($visitaModel->update($visitaId)) {
                header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?success=Visita técnica actualizada');
                exit;
            }

            header('Location: ' . url('equipos/' . $equipoId . '/visitas/' . $visitaId . '/edit') . '?error=No se pudo actualizar la visita');
            exit;
        }

        $data = [
            'title' => 'Editar Visita - ' . APP_NAME,
            'equipo' => $equipo,
            'visita' => $visita,
            'error' => $_GET['error'] ?? null,
        ];

        $this->loadView('equipos/visita_edit', $data);
    }

    public function eliminarVisita($equipoId, $visitaId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?error=Método no permitido');
            exit;
        }

        $equipoModel = new Equipo();
        $equipo = $equipoModel->getById($equipoId);

        if (!$equipo) {
            header('Location: ' . url('equipos') . '?error=Equipo no encontrado');
            exit;
        }

        $visitaModel = new VisitaTecnica();
        $visita = $visitaModel->getById($visitaId);

        if (!$visita || (int)($visita['equipo_id'] ?? 0) !== (int)$equipoId) {
            header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?error=Visita no encontrada');
            exit;
        }

        if ($visitaModel->deleteById($visitaId)) {
            header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?success=Visita técnica eliminada');
            exit;
        }

        header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?error=No se pudo eliminar la visita');
        exit;
    }

    public function cancelarVisita($equipoId, $visitaId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?error=Método no permitido');
            exit;
        }

        $equipoModel = new Equipo();
        $equipo = $equipoModel->getById($equipoId);

        if (!$equipo) {
            header('Location: ' . url('equipos') . '?error=Equipo no encontrado');
            exit;
        }

        $visitaModel = new VisitaTecnica();
        $visita = $visitaModel->getById($visitaId);

        if (!$visita || (int)($visita['equipo_id'] ?? 0) !== (int)$equipoId) {
            header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?error=Visita no encontrada');
            exit;
        }

        if (($visita['estado'] ?? '') === 'cancelada') {
            header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?success=La visita ya estaba cancelada');
            exit;
        }

        if ($visitaModel->updateEstado($visitaId, 'cancelada')) {
            header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?success=Visita técnica cancelada');
            exit;
        }

        header('Location: ' . url('equipos/' . $equipoId . '/visitas') . '?error=No se pudo cancelar la visita');
        exit;
    }
    
    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $equipoModel = new Equipo();
            $equipo = $equipoModel->getById($id);
            
            if (!$equipo) {
                header('Location: ' . url('equipos') . '?error=Equipo no encontrado');
                exit;
            }
            
            // Validar el nuevo estado
            $estadosPermitidos = ['operativo', 'necesita_revision', 'dañado', 'fuera_de_servicio', 'en_mantenimiento'];
            $nuevoEstado = $_POST['nuevo_estado'] ?? '';
            
            if (!in_array($nuevoEstado, $estadosPermitidos)) {
                header('Location: ' . url('equipos/' . $id) . '?error=Estado no válido');
                exit;
            }
            
            // Actualizar el estado del equipo
            $equipoModel->id = $id;
            $equipoModel->cliente_id = $equipo['cliente_id'];
            $equipoModel->tipo_equipo = $equipo['tipo_equipo'];
            $equipoModel->marca = $equipo['marca'];
            $equipoModel->modelo = $equipo['modelo'];
            $equipoModel->numero_serie = $equipo['numero_serie'];
            $equipoModel->estado_tecnico = $nuevoEstado;
            $equipoModel->fecha_instalacion = $equipo['fecha_instalacion'];
            $equipoModel->observaciones_tecnico = $_POST['observaciones'] ?? $equipo['observaciones_tecnico'];
            $equipoModel->instalacion_id = $equipo['instalacion_id'] ?? null;
            $equipoModel->mac_address = $equipo['mac_address'] ?? null;
            $equipoModel->direccion_ip = $equipo['direccion_ip'] ?? null;
            $equipoModel->password_acceso = $equipo['password_acceso'] ?? null;
            $equipoModel->ssid = $equipo['ssid'] ?? null;
            $equipoModel->usuario_acceso = $equipo['usuario_acceso'] ?? null;
            $equipoModel->acceso_habilitado = (int)($equipo['acceso_habilitado'] ?? 0);
            
            if ($equipoModel->update()) {
                $estadoTexto = ucfirst(str_replace('_', ' ', $nuevoEstado));
                header('Location: ' . url('equipos/' . $id) . '?success=Estado cambiado a: ' . $estadoTexto);
            } else {
                header('Location: ' . url('equipos/' . $id) . '?error=Error al cambiar el estado');
            }
            exit;
        }
        
        // Si es GET, mostrar el formulario para cambiar estado
        $equipoModel = new Equipo();
        $equipo = $equipoModel->getById($id);
        
        if (!$equipo) {
            header('Location: ' . url('equipos') . '?error=Equipo no encontrado');
            exit;
        }
        
        $data = [
            'title' => 'Cambiar Estado - ' . APP_NAME,
            'equipo' => $equipo
        ];
        
        $this->loadView('equipos/cambiar-estado', $data);
    }
    
    public function generarReporte($id) {
        $equipoModel = new Equipo();
        $equipo = $equipoModel->getById($id);
        
        if (!$equipo) {
            header('Location: ' . url('equipos') . '?error=Equipo no encontrado');
            exit;
        }
        
        // Usar la clase de reporte
        $reporteGenerator = new ReporteEquipoPDF($equipo);
        $reporteGenerator->generar();
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }
}
?>
