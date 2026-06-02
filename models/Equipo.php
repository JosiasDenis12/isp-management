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
        $query = "SELECT e.*, c.nombre as cliente_nombre 
                  FROM " . $this->table_name . " e
                  JOIN clientes c ON e.cliente_id = c.id
                  WHERE e.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (cliente_id, tipo_equipo, marca, modelo, numero_serie, estado_tecnico, fecha_instalacion, observaciones_tecnico) 
                  VALUES (:cliente_id, :tipo_equipo, :marca, :modelo, :numero_serie, :estado_tecnico, :fecha_instalacion, :observaciones_tecnico)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':cliente_id', $this->cliente_id);
        $stmt->bindParam(':tipo_equipo', $this->tipo_equipo);
        $stmt->bindParam(':marca', $this->marca);
        $stmt->bindParam(':modelo', $this->modelo);
        $stmt->bindParam(':numero_serie', $this->numero_serie);
        $stmt->bindParam(':estado_tecnico', $this->estado_tecnico);
        $stmt->bindParam(':fecha_instalacion', $this->fecha_instalacion);
        $stmt->bindParam(':observaciones_tecnico', $this->observaciones_tecnico);
        
        return $stmt->execute();
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
        
        return $stmt->execute();
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
?>
