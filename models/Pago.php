<?php
require_once 'config/database.php';

class Pago {
    private $conn;
    private $table_name = "pagos";
    
    public $id;
    public $cliente_id;
    public $monto;
    public $fecha_pago;
    public $fecha_vencimiento;
    public $metodo_pago;
    public $estado;
    public $numero_factura;
    public $observaciones;
    public $created_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        $query = "SELECT p.*, c.nombre as cliente_nombre 
                  FROM " . $this->table_name . " p
                  JOIN clientes c ON p.cliente_id = c.id
                  ORDER BY p.fecha_vencimiento DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT p.*, c.nombre as cliente_nombre 
                  FROM " . $this->table_name . " p
                  JOIN clientes c ON p.cliente_id = c.id
                  WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (cliente_id, monto, fecha_pago, fecha_vencimiento, metodo_pago, estado, numero_factura, observaciones) 
                  VALUES (:cliente_id, :monto, :fecha_pago, :fecha_vencimiento, :metodo_pago, :estado, :numero_factura, :observaciones)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':cliente_id', $this->cliente_id);
        $stmt->bindParam(':monto', $this->monto);
        $stmt->bindParam(':fecha_pago', $this->fecha_pago);
        $stmt->bindParam(':fecha_vencimiento', $this->fecha_vencimiento);
        $stmt->bindParam(':metodo_pago', $this->metodo_pago);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':numero_factura', $this->numero_factura);
        $stmt->bindParam(':observaciones', $this->observaciones);
        
        return $stmt->execute();
    }
    
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_pagos,
                    SUM(CASE WHEN estado = 'pagado' THEN monto ELSE 0 END) as ingresos_mes,
                    COUNT(CASE WHEN estado = 'vencido' THEN 1 END) as pagos_vencidos,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pagos_pendientes
                  FROM " . $this->table_name . " 
                  WHERE MONTH(fecha_vencimiento) = MONTH(CURRENT_DATE()) 
                  AND YEAR(fecha_vencimiento) = YEAR(CURRENT_DATE())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getProximosVencimientos() {
        $query = "SELECT p.*, c.nombre as cliente_nombre
                  FROM " . $this->table_name . " p
                  JOIN clientes c ON p.cliente_id = c.id
                  WHERE p.estado = 'pendiente' 
                  AND p.fecha_vencimiento <= DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)
                  ORDER BY p.fecha_vencimiento ASC
                  LIMIT 5";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function generateFacturaNumber() {
        $query = "SELECT MAX(CAST(SUBSTRING(numero_factura, 5) AS UNSIGNED)) as max_num 
                  FROM " . $this->table_name . " 
                  WHERE numero_factura LIKE 'FAC-%'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'FAC-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }
    
    public function getHistorialCliente($cliente_id) {
        $query = "SELECT p.*, c.nombre as cliente_nombre
                  FROM " . $this->table_name . " p
                  JOIN clientes c ON p.cliente_id = c.id
                  WHERE p.cliente_id = :cliente_id
                  ORDER BY p.fecha_vencimiento DESC, p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getEstadisticasCliente($cliente_id) {
        $query = "SELECT 
                    COUNT(*) as total_pagos,
                    COUNT(CASE WHEN estado = 'pagado' THEN 1 END) as pagos_realizados,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pagos_pendientes,
                    COUNT(CASE WHEN estado = 'vencido' THEN 1 END) as pagos_vencidos,
                    SUM(CASE WHEN estado = 'pagado' THEN monto ELSE 0 END) as total_pagado,
                    SUM(CASE WHEN estado IN ('pendiente', 'vencido') THEN monto ELSE 0 END) as total_pendiente,
                    MAX(CASE WHEN estado = 'pagado' THEN fecha_pago END) as ultimo_pago,
                    MIN(CASE WHEN estado IN ('pendiente', 'vencido') THEN fecha_vencimiento END) as proximo_vencimiento
                  FROM " . $this->table_name . " 
                  WHERE cliente_id = :cliente_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
