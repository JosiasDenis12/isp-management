<?php
require_once 'models/Equipo.php';
require_once 'models/Cliente.php';
require_once 'models/VisitaTecnica.php';
require_once 'lib/ReporteEquipoPDF.php';

class EquipoController {
    
    public function index() {
        $equipoModel = new Equipo();
        $equipos = $equipoModel->getAll();
        
        $data = [
            'title' => 'Equipos Técnicos - ' . APP_NAME,
            'equipos' => $equipos
        ];
        
        $this->loadView('equipos/index', $data);
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $equipoModel = new Equipo();
            
            $equipoModel->cliente_id = $_POST['cliente_id'];
            $equipoModel->tipo_equipo = $_POST['tipo_equipo'];
            $equipoModel->marca = $_POST['marca'];
            $equipoModel->modelo = $_POST['modelo'];
            $equipoModel->numero_serie = $_POST['numero_serie'];
            $equipoModel->estado_tecnico = $_POST['estado_tecnico'];
            $equipoModel->fecha_instalacion = $_POST['fecha_instalacion'];
            $equipoModel->observaciones_tecnico = $_POST['observaciones_tecnico'];
            
            if ($equipoModel->create()) {
                header('Location: ' . url('equipos') . '?success=Equipo registrado exitosamente');
                exit;
            } else {
                $error = 'Error al registrar el equipo';
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
            $visitaModel->tecnico_nombre = trim((string)($_POST['tecnico_visita'] ?? ''));
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
