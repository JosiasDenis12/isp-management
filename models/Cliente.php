<?php

require_once 'config/database.php';
require_once 'core/SubscriptionStatus.php';

class Cliente
{
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

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /*
     * Helpers heredados.
     * La lógica principal utiliza SubscriptionStatus.
     */
    private function calcularVencimientoPorCorte(
        ?string $baseDate,
        int $diaCorte
    ): ?string {
        $baseDate = trim((string)$baseDate);

        if ($baseDate === '') {
            return null;
        }

        $base = DateTimeImmutable::createFromFormat(
            'Y-m-d',
            substr($baseDate, 0, 10)
        );

        if (!$base) {
            return null;
        }

        $year = (int)$base->format('Y');
        $month = (int)$base->format('m');

        $firstOfMonth = new DateTimeImmutable(
            sprintf('%04d-%02d-01', $year, $month)
        );

        $lastDay = (int)$firstOfMonth->format('t');

        $day = max(
            1,
            min($diaCorte, $lastDay)
        );

        $candidate = $firstOfMonth->setDate(
            $year,
            $month,
            $day
        );

        if ($candidate <= $base) {
            $next = $firstOfMonth->modify(
                'first day of next month'
            );

            $nextYear = (int)$next->format('Y');
            $nextMonth = (int)$next->format('m');
            $nextLast = (int)$next->format('t');

            $nextDay = max(
                1,
                min($diaCorte, $nextLast)
            );

            $candidate = $next->setDate(
                $nextYear,
                $nextMonth,
                $nextDay
            );
        }

        return $candidate->format('Y-m-d');
    }

    private function calcularDiasRestantes(
        ?string $fechaVencimiento
    ): ?int {
        $fechaVencimiento = trim(
            (string)$fechaVencimiento
        );

        if ($fechaVencimiento === '') {
            return null;
        }

        $venc = DateTimeImmutable::createFromFormat(
            'Y-m-d',
            substr($fechaVencimiento, 0, 10)
        );

        if (!$venc) {
            return null;
        }

        $hoy = new DateTimeImmutable('today');

        return (int)$hoy
            ->diff($venc)
            ->format('%r%a');
    }

    private function obtenerResumenSuscripcion(
        array $row
    ): array {
        $calculo = SubscriptionStatus::calcular(
            $row['ultima_fecha_pago'] ?? null,
            $row['fecha_contratacion'] ?? null,
            (int)($row['dia_corte'] ?? 0)
        );

        $calculo['fecha_vencimiento_calc'] =
            $calculo['fecha_corte'];

        $totalPagos =
            (int)($row['total_pagos'] ?? 0);

        $monto =
            (float)($row['plan_mensual'] ?? 0);

        return array_merge(
            $row,
            $calculo,
            [
                'id' =>
                    (int)($row['id'] ?? 0),

                'nombre' =>
                    (string)($row['nombre'] ?? ''),

                'telefono' =>
                    (string)($row['telefono'] ?? ''),

                'estado_cliente' =>
                    (string)($row['estado'] ?? ''),

                'monto' =>
                    $monto,

                'numero_factura' =>
                    (string)(
                        $row['numero_factura'] ?? ''
                    ),

                'dias' =>
                    $calculo['dias_para_pago'],

                'dias_para_vencer' =>
                    $calculo['dias_para_pago'] !== null
                        ? max(
                            0,
                            $calculo['dias_para_pago']
                        )
                        : null,

                'total_pagos' =>
                    $totalPagos,

                'plan_mensual' =>
                    (float)(
                        $row['plan_mensual'] ?? 0
                    ),
            ]
        );
    }

    public function getAll()
    {
        $query = "
            SELECT *
            FROM {$this->table_name}
            ORDER BY created_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllConEstadoSuscripcion(): array
    {
        $estados = [];

        foreach (
            $this->getResumenSuscripciones()
            as $row
        ) {
            $estados[(int)$row['id']] =
                $this->obtenerResumenSuscripcion(
                    $row
                );
        }

        return array_map(
            function ($cliente) use ($estados) {

                $clienteId =
                    (int)$cliente['id'];

                $estadoCalculado =
                    $estados[$clienteId]
                    ??
                    SubscriptionStatus::calcular(
                        null,
                        $cliente[
                            'fecha_contratacion'
                        ] ?? null,
                        (int)(
                            $cliente[
                                'dia_corte'
                            ] ?? 0
                        )
                    );

                return array_merge(
                    $cliente,
                    $estadoCalculado
                );
            },
            $this->getAll()
        );
    }

    public function enriquecerResumenSuscripciones(): array
    {
        return array_map(
            fn($row) =>
                $this->obtenerResumenSuscripcion(
                    $row
                ),
            $this->getResumenSuscripciones()
        );
    }

    public function getById($id)
    {
        $query = "
            SELECT *
            FROM {$this->table_name}
            WHERE id = :id
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(
            ':id',
            (int)$id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create()
    {
        $query = "
            INSERT INTO {$this->table_name}
            (
                nombre,
                direccion,
                telefono,
                email,
                estado,
                tipo_conexion,
                fecha_contratacion,
                dia_corte,
                plan_mensual,
                megas_contratados
            )
            VALUES
            (
                :nombre,
                :direccion,
                :telefono,
                :email,
                :estado,
                :tipo_conexion,
                :fecha_contratacion,
                :dia_corte,
                :plan_mensual,
                :megas_contratados
            )
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(
            ':nombre',
            $this->nombre
        );

        $stmt->bindValue(
            ':direccion',
            $this->direccion
        );

        $stmt->bindValue(
            ':telefono',
            $this->telefono
        );

        $stmt->bindValue(
            ':email',
            $this->email
        );

        $stmt->bindValue(
            ':estado',
            $this->estado
        );

        $stmt->bindValue(
            ':tipo_conexion',
            $this->tipo_conexion
        );

        $stmt->bindValue(
            ':fecha_contratacion',
            $this->fecha_contratacion
        );

        $stmt->bindValue(
            ':dia_corte',
            $this->dia_corte
        );

        $stmt->bindValue(
            ':plan_mensual',
            $this->plan_mensual
        );

        $stmt->bindValue(
            ':megas_contratados',
            $this->megas_contratados
        );

        return $stmt->execute();
    }

    public function update()
    {
        $query = "
            UPDATE {$this->table_name}

            SET
                nombre = :nombre,
                direccion = :direccion,
                telefono = :telefono,
                email = :email,
                estado = :estado,
                tipo_conexion = :tipo_conexion,
                fecha_contratacion = :fecha_contratacion,
                dia_corte = :dia_corte,
                plan_mensual = :plan_mensual,
                megas_contratados = :megas_contratados

            WHERE id = :id
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(
            ':id',
            (int)$this->id,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':nombre',
            $this->nombre
        );

        $stmt->bindValue(
            ':direccion',
            $this->direccion
        );

        $stmt->bindValue(
            ':telefono',
            $this->telefono
        );

        $stmt->bindValue(
            ':email',
            $this->email
        );

        $stmt->bindValue(
            ':estado',
            $this->estado
        );

        $stmt->bindValue(
            ':tipo_conexion',
            $this->tipo_conexion
        );

        $stmt->bindValue(
            ':fecha_contratacion',
            $this->fecha_contratacion
        );

        $stmt->bindValue(
            ':dia_corte',
            $this->dia_corte
        );

        $stmt->bindValue(
            ':plan_mensual',
            $this->plan_mensual
        );

        $stmt->bindValue(
            ':megas_contratados',
            $this->megas_contratados
        );

        return $stmt->execute();
    }

    public function delete($id)
    {
        $resultado = $this->deleteWithDependencies(
            (int)$id
        );

        return (bool)(
            $resultado['deleted'] ?? false
        );
    }

    /*
     * Resumen mostrado antes de confirmar
     * la eliminación del cliente.
     */
    public function getDeleteImpact(
        int $id
    ): array {
        $id = (int)$id;

        if ($id <= 0) {
            throw new InvalidArgumentException(
                'ID de cliente inválido'
            );
        }

        $cliente = $this->getById($id);

        if (!$cliente) {
            throw new RuntimeException(
                'Cliente no encontrado'
            );
        }

        $counts = [
            'pagos' =>
                $this->countByClienteId(
                    'pagos',
                    $id
                ),

            'equipos' =>
                $this->countByClienteId(
                    'equipos',
                    $id
                ),

            'instalaciones' =>
                $this->countByClienteId(
                    'instalaciones',
                    $id
                ),

            'visitas_tecnicas' =>
                $this->countVisitasByCliente(
                    $id
                ),
        ];

        return [
            'cliente' => [
                'id' =>
                    (int)$cliente['id'],

                'nombre' =>
                    (string)(
                        $cliente['nombre'] ?? ''
                    ),
            ],

            'counts' =>
                $counts,

            'total_relacionados' =>
                array_sum($counts),

            'fk_audit' =>
                $this->getForeignKeyAudit(),
        ];
    }

    /*
     * Eliminación completa del cliente
     * y todos sus registros relacionados.
     */
    public function deleteWithDependencies(int $id): array
{
    $id = (int)$id;

    if ($id <= 0) {
        throw new InvalidArgumentException(
            'ID de cliente inválido'
        );
    }

    /*
     * IMPORTANTE:
     * Usamos beginTransaction() de PDO.
     * NO usamos:
     * $this->conn->exec('BEGIN IMMEDIATE TRANSACTION');
     *
     * Así commit() y rollBack() funcionan correctamente.
     */
    $this->conn->beginTransaction();

    try {

        /*
         * Verificar que el cliente exista.
         *
         * SQLite no soporta SELECT ... FOR UPDATE,
         * por lo que lockClienteById() debe usar
         * únicamente un SELECT normal.
         */
        $cliente = $this->lockClienteById($id);

        if (!$cliente) {
            throw new RuntimeException(
                'Cliente no encontrado'
            );
        }

        /*
         * Obtener información antes de eliminar.
         */
        $impacto = $this->getDeleteImpact($id);

        /*
         * =================================================
         * ORDEN DE ELIMINACIÓN
         * =================================================
         *
         * 1. Visitas técnicas
         * 2. Pagos
         * 3. Equipos
         * 4. Instalaciones
         * 5. Cliente
         *
         * equipos.instalacion_id depende de instalaciones.id
         * Por eso equipos debe eliminarse ANTES.
         */

        /*
         * 1. Eliminar visitas técnicas.
         */
        $deletedVisitas =
            $this->deleteVisitasByCliente($id);

        /*
         * 2. Eliminar pagos.
         */
        $deletedPagos =
            $this->deleteByClienteId(
                'pagos',
                $id
            );

        /*
         * 3. Eliminar equipos.
         *
         * Se eliminan antes que instalaciones porque
         * equipos.instalacion_id puede tener una FK
         * hacia instalaciones.id.
         */
        $deletedEquipos =
            $this->deleteByClienteId(
                'equipos',
                $id
            );

        /*
         * 4. Ahora eliminar instalaciones.
         */
        $deletedInstalaciones =
            $this->deleteByClienteId(
                'instalaciones',
                $id
            );

        /*
         * 5. Finalmente eliminar al cliente.
         *
         * SQLite no necesita ni admite LIMIT aquí.
         */
        $stmtCliente = $this->conn->prepare(
            '
            DELETE FROM clientes
            WHERE id = :id
            '
        );

        $stmtCliente->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmtCliente->execute();

        if ($stmtCliente->rowCount() !== 1) {
            throw new RuntimeException(
                'No fue posible eliminar el cliente.'
            );
        }

        /*
         * Confirmar TODOS los cambios.
         */
        $this->conn->commit();

        return [
            'deleted' => true,

            'cliente' => [
                'id' => $id,
                'nombre' => (string)(
                    $cliente['nombre'] ?? ''
                ),
            ],

            'impact' => $impacto['counts'],

            'deleted_rows' => [
                'visitas_tecnicas' =>
                    $deletedVisitas,

                'pagos' =>
                    $deletedPagos,

                'equipos' =>
                    $deletedEquipos,

                'instalaciones' =>
                    $deletedInstalaciones,

                'clientes' => 1,
            ],
        ];

    } catch (Throwable $e) {

        /*
         * Solo hacer rollback si PDO reconoce
         * que existe una transacción activa.
         */
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }

        throw $e;
    }
}
    /*
     * SQLite no soporta FOR UPDATE.
     * La transacción BEGIN IMMEDIATE ya protege
     * la operación de eliminación.
     */
    private function lockClienteById(int $id): ?array
{
    $stmt = $this->conn->prepare(
        '
        SELECT id, nombre
        FROM clientes
        WHERE id = :id
        '
    );

    $stmt->bindValue(
        ':id',
        $id,
        PDO::PARAM_INT
    );

    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

    private function countByClienteId(
        string $table,
        int $clienteId
    ): int {
        $allowed = [
            'pagos',
            'equipos',
            'instalaciones'
        ];

        if (
            !in_array(
                $table,
                $allowed,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Tabla no permitida para conteo'
            );
        }

        $sql = "
            SELECT COUNT(*) AS total
            FROM {$table}
            WHERE cliente_id = :cliente_id
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(
            ':cliente_id',
            $clienteId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)(
            $row['total'] ?? 0
        );
    }

    private function countVisitasByCliente(
        int $clienteId
    ): int {
        $sql = "
            SELECT COUNT(*) AS total

            FROM visitas_tecnicas v

            LEFT JOIN equipos e
                ON e.id = v.equipo_id

            WHERE v.cliente_id = :cliente_id
               OR e.cliente_id = :cliente_id
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(
            ':cliente_id',
            $clienteId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)(
            $row['total'] ?? 0
        );
    }

    private function deleteByClienteId(
        string $table,
        int $clienteId
    ): int {
        $allowed = [
            'pagos',
            'equipos',
            'instalaciones'
        ];

        if (
            !in_array(
                $table,
                $allowed,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Tabla no permitida para eliminación'
            );
        }

        $sql = "
            DELETE FROM {$table}
            WHERE cliente_id = :cliente_id
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(
            ':cliente_id',
            $clienteId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (int)$stmt->rowCount();
    }

    /*
     * SQLite no soporta:
     *
     * DELETE v
     * FROM visitas_tecnicas v
     * JOIN ...
     *
     * Se reemplaza por una subconsulta.
     */
    private function deleteVisitasByCliente(
        int $clienteId
    ): int {
        $sql = "
            DELETE FROM visitas_tecnicas

            WHERE cliente_id = :cliente_id

               OR equipo_id IN (
                    SELECT id
                    FROM equipos
                    WHERE cliente_id = :cliente_id
               )
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(
            ':cliente_id',
            $clienteId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (int)$stmt->rowCount();
    }

    /*
     * SQLite usa PRAGMA foreign_key_list()
     * en lugar de information_schema.
     */
    private function getForeignKeyAudit(): array
    {
        $tables = [
            'clientes',
            'equipos',
            'visitas_tecnicas',
            'instalaciones',
            'pagos'
        ];

        $foreignKeys = [];

        foreach ($tables as $table) {

            $stmt = $this->conn->query(
                "PRAGMA foreign_key_list({$table})"
            );

            $rows = $stmt
                ? $stmt->fetchAll(PDO::FETCH_ASSOC)
                : [];

            foreach ($rows as $row) {

                $referencedTable =
                    $row['table'] ?? '';

                /*
                 * Conservamos únicamente las relaciones
                 * relevantes para el cliente y sus
                 * dependencias.
                 */
                if (
                    $referencedTable === 'clientes'
                    ||
                    in_array(
                        $table,
                        [
                            'equipos',
                            'visitas_tecnicas',
                            'instalaciones',
                            'pagos'
                        ],
                        true
                    )
                ) {
                    $foreignKeys[] = [
                        'TABLE_NAME' =>
                            $table,

                        'COLUMN_NAME' =>
                            $row['from'] ?? '',

                        'CONSTRAINT_NAME' =>
                            'fk_' .
                            $table .
                            '_' .
                            ($row['id'] ?? ''),

                        'REFERENCED_TABLE_NAME' =>
                            $referencedTable,

                        'REFERENCED_COLUMN_NAME' =>
                            $row['to'] ?? '',

                        'UPDATE_RULE' =>
                            $row['on_update'] ?? '',

                        'DELETE_RULE' =>
                            $row['on_delete'] ?? '',
                    ];
                }
            }
        }

        return $foreignKeys;
    }

    public function getStats()
    {
        $query = "
            SELECT
                COUNT(*) AS total,

                COUNT(
                    CASE
                        WHEN estado = 'activo'
                        THEN 1
                    END
                ) AS activos,

                COUNT(
                    CASE
                        WHEN estado = 'suspendido'
                        THEN 1
                    END
                ) AS suspendidos,

                COUNT(
                    CASE
                        WHEN estado = 'pendiente'
                        THEN 1
                    END
                ) AS pendientes

            FROM {$this->table_name}
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt->fetch(
            PDO::FETCH_ASSOC
        );
    }

    public function getClientesConPagosVencidos()
    {
        $clientes = [];

        foreach (
            $this->getResumenSuscripciones()
            as $row
        ) {

            $resumen =
                $this->obtenerResumenSuscripcion(
                    $row
                );

            if (
                ($resumen[
                    'estado_calculado'
                ] ?? '') !== 'vencido'
            ) {
                continue;
            }

            $clientes[] = [
                'id' =>
                    $resumen['id'],

                'nombre' =>
                    $resumen['nombre'],

                'telefono' =>
                    $resumen['telefono'],

                'estado_cliente' =>
                    $resumen[
                        'estado_cliente'
                    ],

                'fecha_vencimiento' =>
                    $resumen[
                        'fecha_vencimiento'
                    ],

                'monto' =>
                    $resumen['monto'],

                'numero_factura' =>
                    $resumen[
                        'numero_factura'
                    ],

                'dias_vencido' =>
                    $resumen[
                        'dias_vencido'
                    ],
            ];
        }

        usort(
            $clientes,
            function ($a, $b) {
                return strcmp(
                    (string)(
                        $a[
                            'fecha_vencimiento'
                        ] ?? ''
                    ),
                    (string)(
                        $b[
                            'fecha_vencimiento'
                        ] ?? ''
                    )
                );
            }
        );

        return $clientes;
    }

    public function getClientesConPagosPorVencer(
        $dias = SubscriptionStatus::DIAS_PROXIMO_VENCIMIENTO
    ) {
        $dias = max(
            0,
            (int)$dias
        );

        $clientes = [];

        foreach (
            $this->getResumenSuscripciones()
            as $row
        ) {

            $resumen =
                $this->obtenerResumenSuscripcion(
                    $row
                );

            $diasRestantes =
                $resumen[
                    'dias_para_pago'
                ];

            if (
                ($resumen[
                    'estado_calculado'
                ] ?? '') !== 'porvencer'
                ||
                $diasRestantes === null
                ||
                $diasRestantes > $dias
            ) {
                continue;
            }

            $clientes[] = [
                'id' =>
                    $resumen['id'],

                'nombre' =>
                    $resumen['nombre'],

                'telefono' =>
                    $resumen['telefono'],

                'estado_cliente' =>
                    $resumen[
                        'estado_cliente'
                    ],

                'fecha_vencimiento' =>
                    $resumen[
                        'fecha_vencimiento'
                    ],

                'monto' =>
                    $resumen['monto'],

                'numero_factura' =>
                    $resumen[
                        'numero_factura'
                    ],

                'dias_para_vencer' =>
                    $diasRestantes,
            ];
        }

        usort(
            $clientes,
            function ($a, $b) {
                return (int)(
                    $a[
                        'dias_para_vencer'
                    ] ?? 0
                )
                <=>
                (int)(
                    $b[
                        'dias_para_vencer'
                    ] ?? 0
                );
            }
        );

        return $clientes;
    }

    public function getResumenEstadoPagos()
    {
        $clientesVencidos = 0;
        $clientesPorVencer = 0;
        $montoTotalVencido = 0;
        $diasAtraso = [];

        foreach (
            $this->getResumenSuscripciones()
            as $row
        ) {

            $resumen =
                $this->obtenerResumenSuscripcion(
                    $row
                );

            $diasRestantes =
                $resumen[
                    'dias_para_pago'
                ];

            if (
                $diasRestantes === null
            ) {
                continue;
            }

            if (
                ($resumen[
                    'estado_calculado'
                ] ?? '') === 'vencido'
            ) {

                $clientesVencidos++;

                $montoTotalVencido +=
                    (float)$resumen['monto'];

                $diasAtraso[] =
                    abs($diasRestantes);

            } elseif (
                ($resumen[
                    'estado_calculado'
                ] ?? '') === 'porvencer'
            ) {

                $clientesPorVencer++;
            }
        }

        return [
            'clientes_con_pagos_vencidos' =>
                $clientesVencidos,

            'clientes_por_vencer_7_dias' =>
                $clientesPorVencer,

            'monto_total_vencido' =>
                $montoTotalVencido,

            'promedio_dias_atraso' =>
                !empty($diasAtraso)
                    ? array_sum($diasAtraso)
                        / count($diasAtraso)
                    : null,
        ];
    }

    public function getResumenSuscripciones()
    {
        $query = "
            SELECT
                c.id,
                c.nombre,
                c.telefono,
                c.estado,
                c.tipo_conexion,
                c.plan_mensual,
                c.megas_contratados,
                c.fecha_contratacion,
                c.dia_corte,

                COALESCE(
                    res.total_pagos,
                    0
                ) AS total_pagos,

                COALESCE(
                    res.total_pagado,
                    0
                ) AS total_pagado,

                COALESCE(
                    res.meses_pagados,
                    0
                ) AS meses_pagados,

                ult.fecha_pago AS ultima_fecha_pago

            FROM {$this->table_name} c

            LEFT JOIN (
                SELECT
                    cliente_id,

                    COUNT(*) AS total_pagos,

                    SUM(
                        CASE
                            WHEN estado = 'pagado'
                            THEN monto
                            ELSE 0
                        END
                    ) AS total_pagado,

                    COUNT(
                        CASE
                            WHEN estado = 'pagado'
                            THEN 1
                        END
                    ) AS meses_pagados

                FROM pagos

                GROUP BY cliente_id

            ) res
                ON res.cliente_id = c.id

            LEFT JOIN (
                SELECT
                    cliente_id,
                    MAX(fecha_pago) AS fecha_pago

                FROM pagos

                WHERE estado = 'pagado'

                GROUP BY cliente_id

            ) ult
                ON ult.cliente_id = c.id

            ORDER BY c.nombre ASC
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }
}