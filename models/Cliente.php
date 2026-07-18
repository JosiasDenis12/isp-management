<?php
require_once 'config/database.php';

class Cliente {
    private $conn;
    private $table_name = "clientes";
    
    public $id;
    public $nombre;
    public $direccion;
    public $telefono;
    public $email;
    public $estado;
    public $tipo_conexion;
    public $fecha_contratacion;
    public $dia_corte;
    public $plan_mensual;
    public $megas_contratados;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, direccion, telefono, email, estado, tipo_conexion, fecha_contratacion, dia_corte, plan_mensual, megas_contratados) 
                  VALUES (:nombre, :direccion, :telefono, :email, :estado, :tipo_conexion, :fecha_contratacion, :dia_corte, :plan_mensual, :megas_contratados)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':tipo_conexion', $this->tipo_conexion);
        $stmt->bindParam(':fecha_contratacion', $this->fecha_contratacion);
        $stmt->bindParam(':dia_corte', $this->dia_corte);
        $stmt->bindParam(':plan_mensual', $this->plan_mensual);
        $stmt->bindParam(':megas_contratados', $this->megas_contratados);
        
        return $stmt->execute();
    }
    
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, direccion = :direccion, telefono = :telefono, 
                      email = :email, estado = :estado, tipo_conexion = :tipo_conexion, 
                      fecha_contratacion = :fecha_contratacion, dia_corte = :dia_corte, plan_mensual = :plan_mensual,
                      megas_contratados = :megas_contratados 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':tipo_conexion', $this->tipo_conexion);
        $stmt->bindParam(':fecha_contratacion', $this->fecha_contratacion);
        $stmt->bindParam(':dia_corte', $this->dia_corte);
        $stmt->bindParam(':plan_mensual', $this->plan_mensual);
        $stmt->bindParam(':megas_contratados', $this->megas_contratados);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN estado = 'activo' THEN 1 END) as activos,
                    COUNT(CASE WHEN estado = 'suspendido' THEN 1 END) as suspendidos,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getClientesConPagosVencidos() {
        $query = "SELECT 
                    c.id,
                    c.nombre,
                    c.telefono,
                    c.estado as estado_cliente,
                    p.fecha_vencimiento,
                    p.monto,
                    p.numero_factura,
                    DATEDIFF(CURRENT_DATE(), p.fecha_vencimiento) as dias_vencido
                  FROM " . $this->table_name . " c
                  INNER JOIN pagos p ON c.id = p.cliente_id
                  WHERE p.estado = 'vencido' 
                  OR (p.estado = 'pendiente' AND p.fecha_vencimiento < CURRENT_DATE())
                  ORDER BY p.fecha_vencimiento ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getClientesConPagosPorVencer($dias = 7) {
        $query = "SELECT 
                    c.id,
                    c.nombre,
                    c.telefono,
                    c.estado as estado_cliente,
                    p.fecha_vencimiento,
                    p.monto,
                    p.numero_factura,
                    DATEDIFF(p.fecha_vencimiento, CURRENT_DATE()) as dias_para_vencer
                  FROM " . $this->table_name . " c
                  INNER JOIN pagos p ON c.id = p.cliente_id
                  WHERE p.estado = 'pendiente' 
                  AND p.fecha_vencimiento BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL :dias DAY)
                  ORDER BY p.fecha_vencimiento ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dias', $dias);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getResumenEstadoPagos() {
        $query = "SELECT 
                    COUNT(DISTINCT CASE 
                        WHEN p.estado = 'vencido' OR (p.estado = 'pendiente' AND p.fecha_vencimiento < CURRENT_DATE())
                        THEN c.id END) as clientes_con_pagos_vencidos,
                    COUNT(DISTINCT CASE 
                        WHEN p.estado = 'pendiente' AND p.fecha_vencimiento BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)
                        THEN c.id END) as clientes_por_vencer_7_dias,
                    SUM(CASE 
                        WHEN p.estado = 'vencido' OR (p.estado = 'pendiente' AND p.fecha_vencimiento < CURRENT_DATE())
                        THEN p.monto ELSE 0 END) as monto_total_vencido,
                    AVG(CASE 
                        WHEN p.estado = 'vencido' OR (p.estado = 'pendiente' AND p.fecha_vencimiento < CURRENT_DATE())
                        THEN DATEDIFF(CURRENT_DATE(), p.fecha_vencimiento) END) as promedio_dias_atraso
                  FROM " . $this->table_name . " c
                  LEFT JOIN pagos p ON c.id = p.cliente_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getResumenSuscripciones() {
        $query = "SELECT
                    c.id,
                    c.nombre,
                    c.telefono,
                    c.tipo_conexion,
                    c.plan_mensual,
                    c.megas_contratados,
                    c.fecha_contratacion,
                    c.dia_corte,
                                        COALESCE(res.total_pagos, 0) as total_pagos,
                                        COALESCE(res.total_pagado, 0) as total_pagado,
                                        COALESCE(res.meses_pagados, 0) as meses_pagados,
                                        ult.fecha_pago as ultima_fecha_pago,
                                        ult.fecha_vencimiento as ultima_fecha_vencimiento,
                                        ult.estado as ultimo_estado_pago
                  FROM " . $this->table_name . " c
                  LEFT JOIN (
                    SELECT
                                                cliente_id,
                        COUNT(*) as total_pagos,
                        SUM(CASE WHEN estado = 'pagado' THEN monto ELSE 0 END) as total_pagado,
                                                COUNT(CASE WHEN estado = 'pagado' THEN 1 END) as meses_pagados
                                        FROM pagos
                    GROUP BY cliente_id
                                                                        ) res ON res.cliente_id = c.id
                                    LEFT JOIN (
                                        SELECT p1.cliente_id, p1.fecha_pago, p1.fecha_vencimiento, p1.estado
                                        FROM pagos p1
                                        INNER JOIN (
                                                SELECT cliente_id, MAX(id) AS max_id
                                                FROM pagos
                                                GROUP BY cliente_id
                                        ) p2 ON p2.cliente_id = p1.cliente_id AND p2.max_id = p1.id
                                    ) ult ON ult.cliente_id = c.id
                                    ORDER BY c.nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
