<?php
require_once 'config/database.php';

class Equipo {
    private $conn;
    private $table_name = "equipos";
    
    public $id;
    public $cliente_id;
    public $tipo_equipo;
    public $marca;
    public $modelo;
    public $numero_serie;
    public $estado_tecnico;
    public $fecha_instalacion;
    public $observaciones_tecnico;
    public $instalacion_id;
    public $mac_address;
    public $direccion_ip;
    public $password_acceso;
    public $ssid;
    public $usuario_acceso;
    public $acceso_habilitado;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        $query = "SELECT e.*, c.nombre as cliente_nombre 
                  FROM " . $this->table_name . " e
                  JOIN clientes c ON e.cliente_id = c.id
                  ORDER BY e.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT e.*, c.nombre as cliente_nombre, i.fecha_instalacion AS instalacion_fecha, i.observaciones AS instalacion_observaciones
                  FROM " . $this->table_name . " e
                  JOIN clientes c ON e.cliente_id = c.id
                  LEFT JOIN instalaciones i ON e.instalacion_id = i.id
                  WHERE e.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (cliente_id, instalacion_id, tipo_equipo, marca, modelo, numero_serie, estado_tecnico, fecha_instalacion, observaciones_tecnico,
                   mac_address, direccion_ip, password_acceso, ssid, usuario_acceso, acceso_habilitado) 
                  VALUES (:cliente_id, :instalacion_id, :tipo_equipo, :marca, :modelo, :numero_serie, :estado_tecnico, :fecha_instalacion, :observaciones_tecnico,
                          :mac_address, :direccion_ip, :password_acceso, :ssid, :usuario_acceso, :acceso_habilitado)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':cliente_id', $this->cliente_id);
        $stmt->bindParam(':instalacion_id', $this->instalacion_id);
        $stmt->bindParam(':tipo_equipo', $this->tipo_equipo);
        $stmt->bindParam(':marca', $this->marca);
        $stmt->bindParam(':modelo', $this->modelo);
        $stmt->bindParam(':numero_serie', $this->numero_serie);
        $stmt->bindParam(':estado_tecnico', $this->estado_tecnico);
        $stmt->bindParam(':fecha_instalacion', $this->fecha_instalacion);
        $stmt->bindParam(':observaciones_tecnico', $this->observaciones_tecnico);
        $stmt->bindParam(':mac_address', $this->mac_address);
        $stmt->bindParam(':direccion_ip', $this->direccion_ip);
        $stmt->bindParam(':password_acceso', $this->password_acceso);
        $stmt->bindParam(':ssid', $this->ssid);
        $stmt->bindParam(':usuario_acceso', $this->usuario_acceso);
        $stmt->bindParam(':acceso_habilitado', $this->acceso_habilitado);
        
        return $stmt->execute();
    }

    public function createInstalacion($cliente_id, $fecha_instalacion, $observaciones = '') {
        $query = "INSERT INTO instalaciones (cliente_id, fecha_instalacion, observaciones)
                  VALUES (:cliente_id, :fecha_instalacion, :observaciones)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->bindParam(':fecha_instalacion', $fecha_instalacion);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->execute();
        return (int)$this->conn->lastInsertId();
    }

    public function createInstalacionCompleta($cliente_id, $fecha_instalacion, $observaciones, $equipos) {
        try {
            $this->conn->beginTransaction();
            $instalacionId = $this->createInstalacion($cliente_id, $fecha_instalacion, $observaciones);

            foreach ($equipos as $equipo) {
                $this->cliente_id = $cliente_id;
                $this->instalacion_id = $instalacionId;
                $this->tipo_equipo = $equipo['tipo_equipo'] ?? '';
                $this->marca = $equipo['marca'] ?? '';
                $this->modelo = $equipo['modelo'] ?? '';
                $this->numero_serie = $equipo['numero_serie'] ?? '';
                $this->estado_tecnico = $equipo['estado_tecnico'] ?? 'operativo';
                $this->fecha_instalacion = $fecha_instalacion;
                $this->observaciones_tecnico = $equipo['observaciones_tecnico'] ?? '';
                $this->mac_address = $equipo['mac_address'] ?? null;
                $this->direccion_ip = $equipo['direccion_ip'] ?? null;
                $this->password_acceso = $equipo['password_acceso'] ?? null;
                $this->ssid = $equipo['ssid'] ?? null;
                $this->usuario_acceso = $equipo['usuario_acceso'] ?? null;
                $this->acceso_habilitado = (int)($equipo['acceso_habilitado'] ?? 0);

                if (!$this->create()) {
                    $this->conn->rollBack();
                    return false;
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }
    
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_equipos,
                    COUNT(CASE WHEN estado_tecnico = 'operativo' THEN 1 END) as operativos,
                    COUNT(CASE WHEN estado_tecnico = 'necesita_revision' THEN 1 END) as necesitan_revision
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET cliente_id = :cliente_id, 
                      tipo_equipo = :tipo_equipo, 
                      marca = :marca, 
                      modelo = :modelo, 
                      numero_serie = :numero_serie, 
                      estado_tecnico = :estado_tecnico, 
                      fecha_instalacion = :fecha_instalacion, 
                      observaciones_tecnico = :observaciones_tecnico,
                      instalacion_id = :instalacion_id,
                      mac_address = :mac_address,
                      direccion_ip = :direccion_ip,
                      password_acceso = :password_acceso,
                      ssid = :ssid,
                      usuario_acceso = :usuario_acceso,
                      acceso_habilitado = :acceso_habilitado,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':cliente_id', $this->cliente_id);
        $stmt->bindParam(':tipo_equipo', $this->tipo_equipo);
        $stmt->bindParam(':marca', $this->marca);
        $stmt->bindParam(':modelo', $this->modelo);
        $stmt->bindParam(':numero_serie', $this->numero_serie);
        $stmt->bindParam(':estado_tecnico', $this->estado_tecnico);
        $stmt->bindParam(':fecha_instalacion', $this->fecha_instalacion);
        $stmt->bindParam(':observaciones_tecnico', $this->observaciones_tecnico);
        $stmt->bindParam(':instalacion_id', $this->instalacion_id);
        $stmt->bindParam(':mac_address', $this->mac_address);
        $stmt->bindParam(':direccion_ip', $this->direccion_ip);
        $stmt->bindParam(':password_acceso', $this->password_acceso);
        $stmt->bindParam(':ssid', $this->ssid);
        $stmt->bindParam(':usuario_acceso', $this->usuario_acceso);
        $stmt->bindParam(':acceso_habilitado', $this->acceso_habilitado);
        
        return $stmt->execute();
    }

    public function getReporteEquiposInstalados($filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['cliente'])) {
            $where[] = '(c.nombre LIKE :cliente OR c.telefono LIKE :cliente)';
            $params[':cliente'] = '%' . $filters['cliente'] . '%';
        }

        if (!empty($filters['tipo_equipo'])) {
            $where[] = 'e.tipo_equipo = :tipo_equipo';
            $params[':tipo_equipo'] = $filters['tipo_equipo'];
        }

        if (!empty($filters['estado_tecnico'])) {
            $where[] = 'e.estado_tecnico = :estado_tecnico';
            $params[':estado_tecnico'] = $filters['estado_tecnico'];
        }

        if (!empty($filters['mac_address'])) {
            $where[] = 'e.mac_address LIKE :mac_address';
            $params[':mac_address'] = '%' . $filters['mac_address'] . '%';
        }

        if (!empty($filters['direccion_ip'])) {
            $where[] = 'e.direccion_ip LIKE :direccion_ip';
            $params[':direccion_ip'] = '%' . $filters['direccion_ip'] . '%';
        }

        if (!empty($filters['numero_serie'])) {
            $where[] = 'e.numero_serie LIKE :numero_serie';
            $params[':numero_serie'] = '%' . $filters['numero_serie'] . '%';
        }

        if (($filters['estado_acceso'] ?? '') === 'activo') {
            $where[] = "e.tipo_equipo = 'modem' AND e.acceso_habilitado = 1";
        } elseif (($filters['estado_acceso'] ?? '') === 'inactivo') {
            $where[] = "e.tipo_equipo = 'modem' AND (e.acceso_habilitado = 0 OR e.acceso_habilitado IS NULL)";
        }

        if (!empty($filters['fecha_desde'])) {
            $where[] = 'e.fecha_instalacion >= :fecha_desde';
            $params[':fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $where[] = 'e.fecha_instalacion <= :fecha_hasta';
            $params[':fecha_hasta'] = $filters['fecha_hasta'];
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sort = ($filters['orden_fecha'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        $query = "SELECT
                    e.*,
                    c.nombre AS cliente_nombre,
                    c.telefono AS cliente_telefono,
                    i.id AS instalacion_id_real,
                    i.fecha_instalacion AS instalacion_fecha,
                    i.observaciones AS instalacion_observaciones
                  FROM {$this->table_name} e
                  INNER JOIN clientes c ON c.id = e.cliente_id
                  LEFT JOIN instalaciones i ON i.id = e.instalacion_id
                  {$whereSql}
                  ORDER BY e.fecha_instalacion {$sort}, e.created_at {$sort}";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function delete() {
        $equipoId = (int)$this->id;
        if ($equipoId <= 0) {
            throw new InvalidArgumentException('ID de equipo inválido');
        }

        $this->conn->beginTransaction();
        try {
            // visitas_tecnicas tiene una FK compuesta hacia equipos. Se eliminan
            // únicamente las visitas del equipo seleccionado antes del equipo.
            $visitas = $this->conn->prepare('DELETE FROM visitas_tecnicas WHERE equipo_id = :id');
            $visitas->bindValue(':id', $equipoId, PDO::PARAM_INT);
            $visitas->execute();

            $stmt = $this->conn->prepare('DELETE FROM equipos WHERE id = :id');
            $stmt->bindValue(':id', $equipoId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException('Equipo no encontrado');
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }
}
?>
