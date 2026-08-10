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
        $resultado = $this->deleteWithDependencies((int)$id);
        return (bool)($resultado['deleted'] ?? false);
    }

    public function getDeleteImpact(int $id): array {
        $id = (int)$id;
        if ($id <= 0) {
            throw new InvalidArgumentException('ID de cliente inválido');
        }

        $cliente = $this->getById($id);
        if (!$cliente) {
            throw new RuntimeException('Cliente no encontrado');
        }

        $counts = [
            'pagos' => $this->countByClienteId('pagos', $id),
            'equipos' => $this->countByClienteId('equipos', $id),
            'instalaciones' => $this->countByClienteId('instalaciones', $id),
            'visitas_tecnicas' => $this->countVisitasByCliente($id),
        ];

        return [
            'cliente' => [
                'id' => (int)$cliente['id'],
                'nombre' => (string)($cliente['nombre'] ?? ''),
            ],
            'counts' => $counts,
            'total_relacionados' => array_sum($counts),
            'fk_audit' => $this->getForeignKeyAudit(),
        ];
    }

    public function deleteWithDependencies(int $id): array {
        $id = (int)$id;
        if ($id <= 0) {
            throw new InvalidArgumentException('ID de cliente inválido');
        }

        $this->conn->beginTransaction();

        try {
            $cliente = $this->lockClienteById($id);
            if (!$cliente) {
                throw new RuntimeException('Cliente no encontrado');
            }

            $impacto = $this->getDeleteImpact($id);

            $deletedVisitas = $this->deleteVisitasByCliente($id);
            $deletedPagos = $this->deleteByClienteId('pagos', $id);
            $deletedEquipos = $this->deleteByClienteId('equipos', $id);
            $deletedInstalaciones = $this->deleteByClienteId('instalaciones', $id);

            $stmtCliente = $this->conn->prepare('DELETE FROM clientes WHERE id = :id LIMIT 1');
            $stmtCliente->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtCliente->execute();
            if ($stmtCliente->rowCount() !== 1) {
                throw new RuntimeException('No fue posible eliminar el cliente.');
            }

            $this->conn->commit();

            return [
                'deleted' => true,
                'cliente' => [
                    'id' => $id,
                    'nombre' => (string)($cliente['nombre'] ?? ''),
                ],
                'impact' => $impacto['counts'],
                'deleted_rows' => [
                    'visitas_tecnicas' => $deletedVisitas,
                    'pagos' => $deletedPagos,
                    'equipos' => $deletedEquipos,
                    'instalaciones' => $deletedInstalaciones,
                    'clientes' => 1,
                ],
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    private function lockClienteById(int $id): ?array {
        $stmt = $this->conn->prepare('SELECT id, nombre FROM clientes WHERE id = :id FOR UPDATE');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function countByClienteId(string $table, int $clienteId): int {
        $allowed = ['pagos', 'equipos', 'instalaciones'];
        if (!in_array($table, $allowed, true)) {
            throw new InvalidArgumentException('Tabla no permitida para conteo');
        }

        $sql = "SELECT COUNT(*) AS total FROM {$table} WHERE cliente_id = :cliente_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    private function countVisitasByCliente(int $clienteId): int {
        $sql = "SELECT COUNT(*) AS total
                FROM visitas_tecnicas v
                LEFT JOIN equipos e ON e.id = v.equipo_id
                WHERE v.cliente_id = :cliente_id
                   OR e.cliente_id = :cliente_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    private function deleteByClienteId(string $table, int $clienteId): int {
        $allowed = ['pagos', 'equipos', 'instalaciones'];
        if (!in_array($table, $allowed, true)) {
            throw new InvalidArgumentException('Tabla no permitida para eliminación');
        }

        $sql = "DELETE FROM {$table} WHERE cliente_id = :cliente_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->rowCount();
    }

    private function deleteVisitasByCliente(int $clienteId): int {
        $sql = "DELETE v
                FROM visitas_tecnicas v
                LEFT JOIN equipos e ON e.id = v.equipo_id
                WHERE v.cliente_id = :cliente_id
                   OR e.cliente_id = :cliente_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->rowCount();
    }

    private function getForeignKeyAudit(): array {
        $sql = "SELECT
                    kcu.TABLE_NAME,
                    kcu.COLUMN_NAME,
                    kcu.CONSTRAINT_NAME,
                    kcu.REFERENCED_TABLE_NAME,
                    kcu.REFERENCED_COLUMN_NAME,
                    COALESCE(rc.UPDATE_RULE, '') AS UPDATE_RULE,
                    COALESCE(rc.DELETE_RULE, '') AS DELETE_RULE
                FROM information_schema.KEY_COLUMN_USAGE kcu
                LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                  ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
                 AND rc.TABLE_NAME = kcu.TABLE_NAME
                 AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                WHERE kcu.CONSTRAINT_SCHEMA = DATABASE()
                  AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
                  AND (
                    kcu.REFERENCED_TABLE_NAME = 'clientes'
                    OR kcu.TABLE_NAME IN ('equipos', 'visitas_tecnicas', 'instalaciones', 'pagos')
                  )
                ORDER BY kcu.TABLE_NAME, kcu.CONSTRAINT_NAME, kcu.ORDINAL_POSITION";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
