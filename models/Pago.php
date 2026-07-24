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
        // Un pago pendiente pasa a vencido automáticamente al iniciar un nuevo día.
        // Esto evita que las tarjetas dependan de una actualización manual del estado.
        $this->actualizarEstadosVencidos();
        $query = "SELECT p.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono, c.estado as cliente_estado 
                  FROM " . $this->table_name . " p
                  JOIN clientes c ON p.cliente_id = c.id
                  ORDER BY p.fecha_vencimiento DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstadosVencidos() {
        $query = "UPDATE " . $this->table_name . "
                  SET estado = 'vencido'
                  WHERE estado = 'pendiente'
                    AND fecha_vencimiento IS NOT NULL
                    AND fecha_vencimiento < CURRENT_DATE()";
        return $this->conn->prepare($query)->execute();
    }

    public function getReportePagos(array $filters = []) {
        $this->actualizarEstadosVencidos();

        $where = [];
        $params = [];
        if (!empty($filters['fecha_desde'])) {
            $where[] = "COALESCE(NULLIF(p.fecha_pago, '0000-00-00'), p.fecha_vencimiento) >= :fecha_desde";
            $params[':fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "COALESCE(NULLIF(p.fecha_pago, '0000-00-00'), p.fecha_vencimiento) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filters['fecha_hasta'];
        }
        if (!empty($filters['estado']) && in_array($filters['estado'], ['pagado', 'pendiente', 'vencido'], true)) {
            $where[] = 'p.estado = :estado';
            $params[':estado'] = $filters['estado'];
        }
        if (!empty($filters['metodo'])) {
            $where[] = 'p.metodo_pago = :metodo';
            $params[':metodo'] = $filters['metodo'];
        }

        $query = "SELECT p.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono,
                         COUNT(CASE WHEN p2.estado = 'pagado' THEN 1 END) AS meses_pagados_cliente
                  FROM pagos p
                  INNER JOIN clientes c ON c.id = p.cliente_id
                  LEFT JOIN pagos p2 ON p2.cliente_id = p.cliente_id
                  " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . "
                  GROUP BY p.id
                  ORDER BY COALESCE(NULLIF(p.fecha_pago, '0000-00-00'), p.fecha_vencimiento) DESC, p.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT p.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono, c.estado as cliente_estado 
                  FROM " . $this->table_name . " p
                  JOIN clientes c ON p.cliente_id = c.id
                  WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function marcarComoPagado($id) {
        $id = (int)$id;
        if ($id <= 0) {
            throw new InvalidArgumentException('ID de pago inválido');
        }

        try {
            $this->conn->beginTransaction();

            $query = "SELECT p.*, c.id as cliente_existe, c.estado as cliente_estado
                      , c.dia_corte as cliente_dia_corte
                      FROM " . $this->table_name . " p
                      JOIN clientes c ON p.cliente_id = c.id
                      WHERE p.id = :id
                      FOR UPDATE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $pago = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pago) {
                throw new RuntimeException('El pago no existe o no tiene un cliente asociado');
            }

            if (($pago['estado'] ?? '') === 'pagado') {
                throw new RuntimeException('Este pago ya estaba marcado como pagado');
            }

            $nuevaFechaVencimiento = $this->calcularSiguienteVencimiento((int)($pago['cliente_dia_corte'] ?? 5));

            $updatePago = "UPDATE " . $this->table_name . "
                           SET estado = 'pagado', fecha_pago = CURRENT_DATE(), fecha_vencimiento = :fecha_vencimiento
                           WHERE id = :id AND estado <> 'pagado'";
            $stmtUpdate = $this->conn->prepare($updatePago);
            $stmtUpdate->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtUpdate->bindValue(':fecha_vencimiento', $nuevaFechaVencimiento);
            $stmtUpdate->execute();

            if ($stmtUpdate->rowCount() !== 1) {
                throw new RuntimeException('No se pudo actualizar el pago. Verifica que no haya sido modificado por otra acción.');
            }

            $clienteId = (int)$pago['cliente_id'];
            $pendientesQuery = "SELECT COUNT(*) as total
                                FROM " . $this->table_name . "
                                WHERE cliente_id = :cliente_id
                                  AND estado IN ('pendiente', 'vencido')
                                  AND fecha_vencimiento < CURRENT_DATE()";
            $stmtPendientes = $this->conn->prepare($pendientesQuery);
            $stmtPendientes->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmtPendientes->execute();
            $pendientesVencidos = (int)($stmtPendientes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

            $clienteActivado = false;
            if ($pendientesVencidos === 0 && ($pago['cliente_estado'] ?? '') === 'suspendido') {
                $stmtCliente = $this->conn->prepare("UPDATE clientes SET estado = 'activo' WHERE id = :cliente_id");
                $stmtCliente->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
                $stmtCliente->execute();
                $clienteActivado = $stmtCliente->rowCount() > 0;
            }

            $this->conn->commit();

            return [
                'pago_id' => $id,
                'cliente_id' => $clienteId,
                'fecha_pago' => date('Y-m-d'),
                'fecha_vencimiento' => $nuevaFechaVencimiento,
                'cliente_activado' => $clienteActivado,
                'pendientes_vencidos' => $pendientesVencidos
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    public function prepararRecordatorio($id) {
        $pago = $this->getById((int)$id);
        if (!$pago) {
            throw new RuntimeException('Pago no encontrado');
        }

        if (($pago['estado'] ?? '') === 'pagado') {
            throw new RuntimeException('El pago ya está marcado como pagado; no requiere recordatorio');
        }

        $telefono = preg_replace('/\D+/', '', (string)($pago['cliente_telefono'] ?? ''));
        if ($telefono === '') {
            throw new RuntimeException('El cliente no tiene número telefónico registrado');
        }

        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono;
        }

        $monto = number_format((float)($pago['monto'] ?? 0), 2);
        $vencimiento = !empty($pago['fecha_vencimiento']) ? date('d/m/Y', strtotime($pago['fecha_vencimiento'])) : 'sin fecha registrada';
        $factura = $pago['numero_factura'] ?? 'N/A';
        $cliente = $pago['cliente_nombre'] ?? 'cliente';
        $mensaje = "Hola {$cliente}, te recordamos que tienes un pago pendiente de \${$monto} correspondiente a la factura {$factura}, con vencimiento {$vencimiento}. Gracias.";

        return [
            'telefono' => $telefono,
            'mensaje' => $mensaje,
            'whatsapp_url' => 'https://wa.me/' . $telefono . '?text=' . rawurlencode($mensaje)
        ];
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
        return $this->getKpis();
    }

    /** Fuente única para todos los KPIs de pagos y dashboard. */
    public function getKpis() {
        require_once 'models/Cliente.php';
        $this->actualizarEstadosVencidos();

        $query = "SELECT
                    SUM(CASE WHEN fecha_pago >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') AND fecha_pago < DATE_ADD(DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01'), INTERVAL 1 MONTH) THEN 1 ELSE 0 END) AS total_pagos_mes,
                    SUM(CASE WHEN estado = 'pagado' AND fecha_pago >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') AND fecha_pago < DATE_ADD(DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01'), INTERVAL 1 MONTH) THEN 1 ELSE 0 END) AS pagos_realizados_mes,
                    COALESCE(SUM(CASE WHEN estado = 'pagado' AND fecha_pago >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') AND fecha_pago < DATE_ADD(DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01'), INTERVAL 1 MONTH) THEN monto ELSE 0 END), 0) AS ingresos_mes,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) AS pagos_pendientes,
                    SUM(CASE WHEN estado = 'vencido' THEN 1 ELSE 0 END) AS pagos_vencidos_registrados,
                    SUM(CASE WHEN fecha_pago >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y-%m-01') AND fecha_pago < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS total_pagos_mes_anterior,
                    SUM(CASE WHEN estado = 'pagado' AND fecha_pago >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y-%m-01') AND fecha_pago < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS pagos_realizados_mes_anterior,
                    COALESCE(SUM(CASE WHEN estado = 'pagado' AND fecha_pago >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y-%m-01') AND fecha_pago < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN monto ELSE 0 END), 0) AS ingresos_mes_anterior
                    , SUM(CASE WHEN estado = 'pendiente' AND fecha_vencimiento >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y-%m-01') AND fecha_vencimiento < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS pagos_pendientes_mes_anterior
                  FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $clienteModel = new Cliente();
        $vencidos = $clienteModel->getClientesConPagosVencidos();
        $proximos = $clienteModel->getClientesConPagosPorVencer(7);
        foreach (['total_pagos_mes', 'pagos_realizados_mes', 'pagos_pendientes', 'pagos_vencidos_registrados', 'total_pagos_mes_anterior', 'pagos_realizados_mes_anterior', 'pagos_pendientes_mes_anterior'] as $key) $stats[$key] = (int)($stats[$key] ?? 0);
        foreach (['ingresos_mes', 'ingresos_mes_anterior'] as $key) $stats[$key] = (float)($stats[$key] ?? 0);

        $stats['clientes_vencidos'] = count($vencidos);
        $stats['proximos_vencimientos'] = count($proximos);
        $stats['total_pagos'] = $stats['total_pagos_mes'];
        $stats['pagos_vencidos'] = $stats['clientes_vencidos'];
        $stats['clientes_vencidos_detalle'] = $vencidos;
        $stats['proximos_vencimientos_detalle'] = $proximos;
        return $stats;
    }
    
    public function getProximosVencimientos() {
        require_once 'models/Cliente.php';

        $clienteModel = new Cliente();
        $clientes = $clienteModel->getClientesConPagosPorVencer(7);

        return array_slice(array_map(function ($cliente) {
            return [
                'cliente_id' => $cliente['id'] ?? null,
                'cliente_nombre' => $cliente['nombre'] ?? '—',
                'cliente_telefono' => $cliente['telefono'] ?? '',
                'fecha_vencimiento' => $cliente['fecha_vencimiento'] ?? null,
                'monto' => $cliente['monto'] ?? 0,
                'numero_factura' => $cliente['numero_factura'] ?? '',
                'dias_para_vencer' => $cliente['dias_para_vencer'] ?? null,
                'estado' => ($cliente['dias_para_vencer'] ?? 0) === 0 ? 'vencido' : 'pendiente',
            ];
        }, $clientes), 0, 5);
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

    private function calcularSiguienteVencimiento($diaCorte) {
        $diaCorte = max(1, min(31, (int)$diaCorte));

        $base = new DateTimeImmutable('today');
        $nextMonth = $base->modify('first day of next month');
        $year = (int)$nextMonth->format('Y');
        $month = (int)$nextMonth->format('m');
        $lastDay = (int)$nextMonth->format('t');
        $day = min($diaCorte, $lastDay);

        return $nextMonth->setDate($year, $month, $day)->format('Y-m-d');
    }
}
?>
