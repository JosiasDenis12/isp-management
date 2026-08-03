<?php
require_once 'config/database.php';
require_once 'core/SubscriptionStatus.php';

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

    /* Legacy helpers kept out of the business flow; use SubscriptionStatus. */
    private function calcularVencimientoPorCorte(?string $baseDate, int $diaCorte): ?string {
        $baseDate = trim((string)$baseDate);
        if ($baseDate === '') {
            return null;
        }

        $base = DateTimeImmutable::createFromFormat('Y-m-d', substr($baseDate, 0, 10));
        if (!$base) {
            return null;
        }

        $year = (int)$base->format('Y');
        $month = (int)$base->format('m');

        $firstOfMonth = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $lastDay = (int)$firstOfMonth->format('t');
        $day = max(1, min($diaCorte, $lastDay));
        $candidate = $firstOfMonth->setDate($year, $month, $day);

        if ($candidate <= $base) {
            $next = $firstOfMonth->modify('first day of next month');
            $nextYear = (int)$next->format('Y');
            $nextMonth = (int)$next->format('m');
            $nextLast = (int)$next->format('t');
            $nextDay = max(1, min($diaCorte, $nextLast));
            $candidate = $next->setDate($nextYear, $nextMonth, $nextDay);
        }

        return $candidate->format('Y-m-d');
    }

    private function calcularDiasRestantes(?string $fechaVencimiento): ?int {
        $fechaVencimiento = trim((string)$fechaVencimiento);
        if ($fechaVencimiento === '') {
            return null;
        }

        $venc = DateTimeImmutable::createFromFormat('Y-m-d', substr($fechaVencimiento, 0, 10));
        if (!$venc) {
            return null;
        }

        $hoy = new DateTimeImmutable('today');
        return (int)$hoy->diff($venc)->format('%r%a');
    }

    private function obtenerResumenSuscripcion(array $row): array {
        $calculo = SubscriptionStatus::calcular($row['ultima_fecha_pago'] ?? null, $row['fecha_contratacion'] ?? null, (int)($row['dia_corte'] ?? 0));
        $calculo['fecha_vencimiento_calc'] = $calculo['fecha_corte'];
        $totalPagos = (int)($row['total_pagos'] ?? 0);
        $monto = (float)($row['plan_mensual'] ?? 0);
        return array_merge($row, $calculo, [
            'id' => (int)($row['id'] ?? 0),
            'nombre' => (string)($row['nombre'] ?? ''),
            'telefono' => (string)($row['telefono'] ?? ''),
            'estado_cliente' => (string)($row['estado'] ?? ''),
            'monto' => $monto,
            'numero_factura' => (string)($row['numero_factura'] ?? ''),
            'dias' => $calculo['dias_para_pago'],
            'dias_para_vencer' => $calculo['dias_para_pago'] !== null ? max(0, $calculo['dias_para_pago']) : null,
            'total_pagos' => $totalPagos,
            'plan_mensual' => (float)($row['plan_mensual'] ?? 0),
        ]);
    }
    
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllConEstadoSuscripcion(): array {
        $estados = [];
        foreach ($this->getResumenSuscripciones() as $row) $estados[(int)$row['id']] = $this->obtenerResumenSuscripcion($row);
        return array_map(fn($cliente) => array_merge($cliente, $estados[(int)$cliente['id']] ?? SubscriptionStatus::calcular(null, $cliente['fecha_contratacion'] ?? null, (int)($cliente['dia_corte'] ?? 0))), $this->getAll());
    }

    public function enriquecerResumenSuscripciones(): array {
        return array_map(fn($row) => $this->obtenerResumenSuscripcion($row), $this->getResumenSuscripciones());
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
        $clientes = [];

        foreach ($this->getResumenSuscripciones() as $row) {
            $resumen = $this->obtenerResumenSuscripcion($row);
            if (($resumen['estado_calculado'] ?? '') !== 'vencido') {
                continue;
            }

            $clientes[] = [
                'id' => $resumen['id'],
                'nombre' => $resumen['nombre'],
                'telefono' => $resumen['telefono'],
                'estado_cliente' => $resumen['estado_cliente'],
                'fecha_vencimiento' => $resumen['fecha_vencimiento'],
                'monto' => $resumen['monto'],
                'numero_factura' => $resumen['numero_factura'],
                'dias_vencido' => $resumen['dias_vencido'],
            ];
        }

        usort($clientes, function ($a, $b) {
            return strcmp((string)($a['fecha_vencimiento'] ?? ''), (string)($b['fecha_vencimiento'] ?? ''));
        });

        return $clientes;
    }
    
    public function getClientesConPagosPorVencer($dias = SubscriptionStatus::DIAS_PROXIMO_VENCIMIENTO) {
        $dias = max(0, (int)$dias);
        $clientes = [];

        foreach ($this->getResumenSuscripciones() as $row) {
            $resumen = $this->obtenerResumenSuscripcion($row);
            $diasRestantes = $resumen['dias_para_pago'];
            if (($resumen['estado_calculado'] ?? '') !== 'porvencer' || $diasRestantes === null || $diasRestantes > $dias) {
                continue;
            }

            $clientes[] = [
                'id' => $resumen['id'],
                'nombre' => $resumen['nombre'],
                'telefono' => $resumen['telefono'],
                'estado_cliente' => $resumen['estado_cliente'],
                'fecha_vencimiento' => $resumen['fecha_vencimiento'],
                'monto' => $resumen['monto'],
                'numero_factura' => $resumen['numero_factura'],
                'dias_para_vencer' => $diasRestantes,
            ];
        }

        usort($clientes, function ($a, $b) {
            return (int)($a['dias_para_vencer'] ?? 0) <=> (int)($b['dias_para_vencer'] ?? 0);
        });

        return $clientes;
    }
    
    public function getResumenEstadoPagos() {
        $clientesVencidos = 0;
        $clientesPorVencer = 0;
        $montoTotalVencido = 0;
        $diasAtraso = [];

        foreach ($this->getResumenSuscripciones() as $row) {
            $resumen = $this->obtenerResumenSuscripcion($row);
            $diasRestantes = $resumen['dias_para_pago'];

            if ($diasRestantes === null) {
                continue;
            }

            if (($resumen['estado_calculado'] ?? '') === 'vencido') {
                $clientesVencidos++;
                $montoTotalVencido += (float)$resumen['monto'];
                $diasAtraso[] = abs($diasRestantes);
            } elseif (($resumen['estado_calculado'] ?? '') === 'porvencer') {
                $clientesPorVencer++;
            }
        }

        return [
            'clientes_con_pagos_vencidos' => $clientesVencidos,
            'clientes_por_vencer_7_dias' => $clientesPorVencer,
            'monto_total_vencido' => $montoTotalVencido,
            'promedio_dias_atraso' => !empty($diasAtraso) ? array_sum($diasAtraso) / count($diasAtraso) : null,
        ];
    }

    public function getResumenSuscripciones() {
        $query = "SELECT
                    c.id,
                    c.nombre,
                    c.telefono,
                    c.estado,
                    c.tipo_conexion,
                    c.plan_mensual,
                    c.megas_contratados,
                    c.fecha_contratacion,
                    c.dia_corte,
                                        COALESCE(res.total_pagos, 0) as total_pagos,
                                        COALESCE(res.total_pagado, 0) as total_pagado,
                                        COALESCE(res.meses_pagados, 0) as meses_pagados,
                                        ult.fecha_pago as ultima_fecha_pago
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
                                        SELECT cliente_id, MAX(fecha_pago) AS fecha_pago
                                        FROM pagos WHERE estado = 'pagado'
                                        GROUP BY cliente_id
                                    ) ult ON ult.cliente_id = c.id
                                    ORDER BY c.nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
