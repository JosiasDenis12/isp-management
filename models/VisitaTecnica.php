<?php
require_once 'config/database.php';

class VisitaTecnica {
    private $conn;
    private $table_name = "visitas_tecnicas";

    public $id;
    public $cliente_id;
    public $equipo_id;
    public $fecha_visita;
    public $tipo_visita;
    public $tecnico_nombre;
    public $observaciones;
    public $estado;
    public $created_at;

    private $fechaColumnType = null;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getByEquipoId($equipo_id) {
        $query = "SELECT * FROM {$this->table_name} WHERE equipo_id = :equipo_id ORDER BY fecha_visita DESC, created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':equipo_id', $equipo_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $fecha = $this->normalizeFechaVisita($this->fecha_visita);
        $tipo = $this->normalizeTipoVisita($this->tipo_visita);
        $estado = $this->normalizeEstado($this->estado);

        if (!$fecha || !$tipo || !$estado) {
            return false;
        }

        $query = "INSERT INTO {$this->table_name}
                  (cliente_id, equipo_id, fecha_visita, tipo_visita, tecnico_nombre, observaciones, estado)
                  VALUES (:cliente_id, :equipo_id, :fecha_visita, :tipo_visita, :tecnico_nombre, :observaciones, :estado)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':cliente_id', $this->cliente_id);
        $stmt->bindParam(':equipo_id', $this->equipo_id);
        $stmt->bindParam(':fecha_visita', $fecha);
        $stmt->bindParam(':tipo_visita', $tipo);
        $stmt->bindParam(':tecnico_nombre', $this->tecnico_nombre);
        $stmt->bindParam(':observaciones', $this->observaciones);
        $stmt->bindParam(':estado', $estado);

        return $stmt->execute();
    }

    public function update($id) {
        $fecha = $this->normalizeFechaVisita($this->fecha_visita);
        $tipo = $this->normalizeTipoVisita($this->tipo_visita);
        $estado = $this->normalizeEstado($this->estado);

        if (!$fecha || !$tipo || !$estado) {
            return false;
        }

        $query = "UPDATE {$this->table_name}
                  SET fecha_visita = :fecha_visita,
                      tipo_visita = :tipo_visita,
                      tecnico_nombre = :tecnico_nombre,
                      observaciones = :observaciones,
                      estado = :estado
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fecha_visita', $fecha);
        $stmt->bindParam(':tipo_visita', $tipo);
        $stmt->bindParam(':tecnico_nombre', $this->tecnico_nombre);
        $stmt->bindParam(':observaciones', $this->observaciones);
        $stmt->bindParam(':estado', $estado);

        return $stmt->execute();
    }

    public function deleteById($id) {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateEstado($id, $estado) {
        $estado = $this->normalizeEstado($estado);
        if (!$estado) {
            return false;
        }

        $query = "UPDATE {$this->table_name} SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':estado', $estado);
        return $stmt->execute();
    }

    public function getFutureScheduledMantenimientoByEquipoId($equipo_id) {
        $type = strtolower((string)$this->getFechaVisitaColumnType());
        $comparison = (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false)
            ? 'NOW()'
            : 'CURDATE()';

        $query = "SELECT * FROM {$this->table_name}
                  WHERE equipo_id = :equipo_id
                    AND tipo_visita = 'mantenimiento'
                    AND estado = 'programada'
                    AND fecha_visita >= {$comparison}
                  ORDER BY fecha_visita ASC, created_at ASC
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':equipo_id', $equipo_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function normalizeTipoVisita($tipo) {
        $tipo = trim((string)$tipo);
        if ($tipo === '') {
            return null;
        }

        $map = [
            'instalación' => 'instalacion',
            'instalacion' => 'instalacion',
            'mantenimiento' => 'mantenimiento',
            'mantenimiento preventivo' => 'mantenimiento',
            'reparación' => 'reparacion',
            'reparacion' => 'reparacion',
            'revisión' => 'revision',
            'revision' => 'revision',
        ];

        $key = $this->safeLower($tipo);
        $normalized = $map[$key] ?? $key;

        $allowed = ['instalacion', 'mantenimiento', 'reparacion', 'revision'];
        return in_array($normalized, $allowed, true) ? $normalized : null;
    }

    private function normalizeEstado($estado) {
        $estado = trim((string)$estado);
        if ($estado === '') {
            return 'programada';
        }

        $map = [
            'programada' => 'programada',
            'completada' => 'completada',
            'cancelada' => 'cancelada',
            'en proceso' => 'programada',
            'enproceso' => 'programada',
        ];

        $key = $this->safeLower($estado);
        $normalized = $map[$key] ?? $key;

        $allowed = ['programada', 'completada', 'cancelada'];
        return in_array($normalized, $allowed, true) ? $normalized : null;
    }

    private function getFechaVisitaColumnType() {
        if ($this->fechaColumnType !== null) {
            return $this->fechaColumnType;
        }

        try {
            $stmt = $this->conn->query("DESCRIBE {$this->table_name} fecha_visita");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->fechaColumnType = $row['Type'] ?? '';
        } catch (Exception $e) {
            $this->fechaColumnType = '';
        }

        return $this->fechaColumnType;
    }

    private function normalizeFechaVisita($fecha) {
        $fecha = trim((string)$fecha);
        if ($fecha === '') {
            return null;
        }

        // Soporta valores como 2026-05-12T14:30 (datetime-local)
        $fecha = str_replace('T', ' ', $fecha);

        $timestamp = strtotime($fecha);
        if ($timestamp === false) {
            return null;
        }

        $type = strtolower((string)$this->getFechaVisitaColumnType());
        if (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }

        return date('Y-m-d', $timestamp);
    }

    private function safeLower($value) {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }
        return strtolower($value);
    }
}
?>
