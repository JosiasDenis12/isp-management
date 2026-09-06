<?php

require_once 'config/database.php';

class VisitaTecnica
{
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


    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }


    public function getByEquipoId($equipo_id)
    {
        $query = "
            SELECT *
            FROM {$this->table_name}
            WHERE equipo_id = :equipo_id
            ORDER BY datetime(REPLACE(fecha_visita, 'T', ' ')) DESC,
                     created_at DESC
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(
            ':equipo_id',
            (int)$equipo_id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getById($id)
    {
        $query = "
            SELECT *
            FROM {$this->table_name}
            WHERE id = :id
            LIMIT 1
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
        /*
         * Validar IDs obligatorios.
         */
        if (
            empty($this->cliente_id)
            || empty($this->equipo_id)
        ) {
            return false;
        }


        /*
         * Verificar que el equipo realmente exista.
         */
        $equipoQuery = "
            SELECT id
            FROM equipos
            WHERE id = :equipo_id
            LIMIT 1
        ";

        $equipoStmt = $this->conn->prepare($equipoQuery);

        $equipoStmt->bindValue(
            ':equipo_id',
            (int)$this->equipo_id,
            PDO::PARAM_INT
        );

        $equipoStmt->execute();

        if (!$equipoStmt->fetch(PDO::FETCH_ASSOC)) {
            return false;
        }


        /*
         * Normalizar datos.
         */
        $fecha = $this->normalizeFechaVisita(
            $this->fecha_visita
        );

        $tipo = $this->normalizeTipoVisita(
            $this->tipo_visita
        );

        $estado = $this->normalizeEstado(
            $this->estado
        );


        if (
            !$fecha
            || !$tipo
            || !$estado
        ) {
            return false;
        }


        /*
         * Insertar visita técnica.
         */
        $query = "
            INSERT INTO {$this->table_name}
            (
                cliente_id,
                equipo_id,
                fecha_visita,
                tipo_visita,
                tecnico_nombre,
                observaciones,
                estado
            )
            VALUES
            (
                :cliente_id,
                :equipo_id,
                :fecha_visita,
                :tipo_visita,
                :tecnico_nombre,
                :observaciones,
                :estado
            )
        ";


        $stmt = $this->conn->prepare($query);


        $stmt->bindValue(
            ':cliente_id',
            (int)$this->cliente_id,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':equipo_id',
            (int)$this->equipo_id,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':fecha_visita',
            $fecha,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':tipo_visita',
            $tipo,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':tecnico_nombre',
            (string)$this->tecnico_nombre,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':observaciones',
            (string)$this->observaciones,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':estado',
            $estado,
            PDO::PARAM_STR
        );


        return $stmt->execute();
    }


    public function update($id)
    {
        $fecha = $this->normalizeFechaVisita(
            $this->fecha_visita
        );

        $tipo = $this->normalizeTipoVisita(
            $this->tipo_visita
        );

        $estado = $this->normalizeEstado(
            $this->estado
        );


        if (
            !$fecha
            || !$tipo
            || !$estado
        ) {
            return false;
        }


        $query = "
            UPDATE {$this->table_name}

            SET
                fecha_visita = :fecha_visita,
                tipo_visita = :tipo_visita,
                tecnico_nombre = :tecnico_nombre,
                observaciones = :observaciones,
                estado = :estado

            WHERE id = :id
        ";


        $stmt = $this->conn->prepare($query);


        $stmt->bindValue(
            ':id',
            (int)$id,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':fecha_visita',
            $fecha,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':tipo_visita',
            $tipo,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':tecnico_nombre',
            (string)$this->tecnico_nombre,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':observaciones',
            (string)$this->observaciones,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':estado',
            $estado,
            PDO::PARAM_STR
        );


        return $stmt->execute();
    }


    public function deleteById($id)
    {
        $query = "
            DELETE FROM {$this->table_name}
            WHERE id = :id
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(
            ':id',
            (int)$id,
            PDO::PARAM_INT
        );

        return $stmt->execute();
    }


    public function updateEstado($id, $estado)
    {
        $estado = $this->normalizeEstado($estado);

        if (!$estado) {
            return false;
        }


        $query = "
            UPDATE {$this->table_name}
            SET estado = :estado
            WHERE id = :id
        ";


        $stmt = $this->conn->prepare($query);


        $stmt->bindValue(
            ':id',
            (int)$id,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':estado',
            $estado,
            PDO::PARAM_STR
        );


        return $stmt->execute();
    }


    /*
     * =========================================================
     * BUSCAR MANTENIMIENTO PROGRAMADO FUTURO
     * =========================================================
     *
     * IMPORTANTE:
     *
     * Antes utilizaba:
     *
     * CURDATE()
     * NOW()
     *
     * Estas funciones son de MySQL y NO existen en SQLite.
     *
     * Ahora usamos funciones compatibles con SQLite.
     */
    public function getFutureScheduledMantenimientoByEquipoId($equipo_id)
    {
        $query = "
            SELECT *

            FROM {$this->table_name}

            WHERE equipo_id = :equipo_id

              AND tipo_visita = 'mantenimiento'

              AND estado = 'programada'

              AND datetime(
                    REPLACE(
                        fecha_visita,
                        'T',
                        ' '
                    )
                  ) >= datetime(
                        'now',
                        'localtime'
                  )

            ORDER BY

                datetime(
                    REPLACE(
                        fecha_visita,
                        'T',
                        ' '
                    )
                ) ASC,

                created_at ASC

            LIMIT 1
        ";


        $stmt = $this->conn->prepare($query);


        $stmt->bindValue(
            ':equipo_id',
            (int)$equipo_id,
            PDO::PARAM_INT
        );


        $stmt->execute();


        $row = $stmt->fetch(PDO::FETCH_ASSOC);


        return $row ?: null;
    }


    public function getReporteEquiposVisitas($filters = [])
    {
        $where = [];
        $params = [];


        if (!empty($filters['fecha_desde'])) {

            $where[] = "
                DATE(
                    REPLACE(
                        v.fecha_visita,
                        'T',
                        ' '
                    )
                ) >= :fecha_desde
            ";

            $params[':fecha_desde'] =
                $filters['fecha_desde'];
        }


        if (!empty($filters['fecha_hasta'])) {

            $where[] = "
                DATE(
                    REPLACE(
                        v.fecha_visita,
                        'T',
                        ' '
                    )
                ) <= :fecha_hasta
            ";

            $params[':fecha_hasta'] =
                $filters['fecha_hasta'];
        }


        if (!empty($filters['cliente'])) {

            $where[] = "
                (
                    c.nombre LIKE :cliente
                    OR c.telefono LIKE :cliente
                )
            ";

            $params[':cliente'] =
                '%' . $filters['cliente'] . '%';
        }


        if (!empty($filters['tecnico'])) {

            $where[] = "
                v.tecnico_nombre LIKE :tecnico
            ";

            $params[':tecnico'] =
                '%' . $filters['tecnico'] . '%';
        }


        if (!empty($filters['equipo'])) {

            $where[] = "
                (
                    e.tipo_equipo LIKE :equipo
                    OR e.marca LIKE :equipo
                    OR e.modelo LIKE :equipo
                    OR e.numero_serie LIKE :equipo
                )
            ";

            $params[':equipo'] =
                '%' . $filters['equipo'] . '%';
        }


        if (!empty($filters['estado_visita'])) {

            $where[] = "
                v.estado = :estado_visita
            ";

            $params[':estado_visita'] =
                $filters['estado_visita'];
        }


        if (!empty($filters['estado_equipo'])) {

            $where[] = "
                e.estado_tecnico = :estado_equipo
            ";

            $params[':estado_equipo'] =
                $filters['estado_equipo'];
        }


        if (!empty($filters['tipo_visita'])) {

            $where[] = "
                v.tipo_visita = :tipo_visita
            ";

            $params[':tipo_visita'] =
                $filters['tipo_visita'];
        }


        $whereSql = !empty($where)
            ? 'WHERE ' . implode(' AND ', $where)
            : '';


        $query = "
            SELECT

                v.id AS visita_id,
                v.fecha_visita,
                v.tipo_visita,
                v.tecnico_nombre,

                v.observaciones
                    AS actividades_observaciones,

                v.estado
                    AS estado_visita,

                v.created_at
                    AS visita_registrada,


                e.id
                    AS equipo_id,

                e.tipo_equipo,
                e.marca,
                e.modelo,
                e.numero_serie,
                e.estado_tecnico,
                e.fecha_instalacion,
                e.observaciones_tecnico,

                e.updated_at
                    AS equipo_actualizado,


                COALESCE(
                    c.id,
                    e.cliente_id
                ) AS cliente_id,


                c.nombre
                    AS cliente_nombre,

                c.telefono
                    AS cliente_telefono,

                c.direccion
                    AS cliente_direccion


            FROM {$this->table_name} v


            INNER JOIN equipos e
                ON e.id = v.equipo_id


            LEFT JOIN clientes c
                ON c.id = COALESCE(
                    v.cliente_id,
                    e.cliente_id
                )


            {$whereSql}


            ORDER BY

                datetime(
                    REPLACE(
                        v.fecha_visita,
                        'T',
                        ' '
                    )
                ) DESC,

                v.created_at DESC
        ";


        $stmt = $this->conn->prepare($query);


        foreach ($params as $key => $value) {

            $stmt->bindValue(
                $key,
                $value
            );

        }


        $stmt->execute();


        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    public function getReporteEquiposVisitasStats($filters = [])
    {
        $rows =
            $this->getReporteEquiposVisitas(
                $filters
            );


        $stats = [

            'total_visitas' =>
                count($rows),

            'equipos_involucrados' =>
                0,

            'clientes_involucrados' =>
                0,

            'completadas' =>
                0,

            'pendientes' =>
                0,

            'canceladas' =>
                0,

            'reprogramadas' =>
                0,

        ];


        $equipos = [];
        $clientes = [];


        foreach ($rows as $row) {

            if (!empty($row['equipo_id'])) {

                $equipos[
                    (int)$row['equipo_id']
                ] = true;

            }


            if (!empty($row['cliente_id'])) {

                $clientes[
                    (int)$row['cliente_id']
                ] = true;

            }


            $estado =
                $row['estado_visita'] ?? '';


            if ($estado === 'completada') {

                $stats['completadas']++;

            }


            if (
                $estado === 'programada'
                || $estado === 'pendiente'
            ) {

                $stats['pendientes']++;

            }


            if ($estado === 'cancelada') {

                $stats['canceladas']++;

            }


            if ($estado === 'reprogramada') {

                $stats['reprogramadas']++;

            }

        }


        $stats['equipos_involucrados'] =
            count($equipos);


        $stats['clientes_involucrados'] =
            count($clientes);


        return $stats;
    }


    private function normalizeTipoVisita($tipo)
    {
        $tipo = trim(
            (string)$tipo
        );


        if ($tipo === '') {
            return null;
        }


        $map = [

            'instalación' =>
                'instalacion',

            'instalacion' =>
                'instalacion',

            'mantenimiento' =>
                'mantenimiento',

            'mantenimiento preventivo' =>
                'mantenimiento',

            'reparación' =>
                'reparacion',

            'reparacion' =>
                'reparacion',

            'revisión' =>
                'revision',

            'revision' =>
                'revision',

        ];


        $key =
            $this->safeLower($tipo);


        $normalized =
            $map[$key] ?? $key;


        $allowed = [

            'instalacion',
            'mantenimiento',
            'reparacion',
            'revision'

        ];


        return in_array(
            $normalized,
            $allowed,
            true
        )
            ? $normalized
            : null;
    }


    private function normalizeEstado($estado)
    {
        $estado = trim(
            (string)$estado
        );


        if ($estado === '') {
            return 'programada';
        }


        $map = [

            'programada' =>
                'programada',

            'pendiente' =>
                'pendiente',

            'completada' =>
                'completada',

            'cancelada' =>
                'cancelada',

            'reprogramada' =>
                'reprogramada',

            'en proceso' =>
                'programada',

            'enproceso' =>
                'programada',

        ];


        $key =
            $this->safeLower($estado);


        $normalized =
            $map[$key] ?? $key;


        $allowed = [

            'programada',
            'pendiente',
            'completada',
            'cancelada',
            'reprogramada'

        ];


        return in_array(
            $normalized,
            $allowed,
            true
        )
            ? $normalized
            : null;
    }


    /*
     * =========================================================
     * DETECTAR TIPO DE COLUMNA SQLITE
     * =========================================================
     *
     * MySQL:
     * DESCRIBE tabla columna
     *
     * SQLite:
     * PRAGMA table_info(tabla)
     */
    private function getFechaVisitaColumnType()
    {
        if ($this->fechaColumnType !== null) {

            return $this->fechaColumnType;

        }


        try {

            $stmt = $this->conn->query(
                "PRAGMA table_info({$this->table_name})"
            );


            $columns =
                $stmt->fetchAll(
                    PDO::FETCH_ASSOC
                );


            foreach ($columns as $column) {

                if (
                    isset($column['name'])
                    && $column['name'] === 'fecha_visita'
                ) {

                    $this->fechaColumnType =
                        $column['type'] ?? '';

                    return $this->fechaColumnType;

                }

            }


            $this->fechaColumnType = '';

        } catch (Throwable $e) {

            $this->fechaColumnType = '';

        }


        return $this->fechaColumnType;
    }


    private function normalizeFechaVisita($fecha)
    {
        $fecha = trim(
            (string)$fecha
        );


        if ($fecha === '') {
            return null;
        }


        /*
         * Soporta datetime-local:
         *
         * 2026-09-05T21:30
         */
        $fecha = str_replace(
            'T',
            ' ',
            $fecha
        );


        $timestamp =
            strtotime($fecha);


        if ($timestamp === false) {
            return null;
        }


        /*
         * Guardamos siempre fecha y hora.
         *
         * SQLite no requiere un tipo DATETIME
         * estricto y datetime() puede interpretar
         * correctamente este formato.
         */
        return date(
            'Y-m-d H:i:s',
            $timestamp
        );
    }


    private function safeLower($value)
    {
        if (
            function_exists(
                'mb_strtolower'
            )
        ) {

            return mb_strtolower(
                $value,
                'UTF-8'
            );

        }


        return strtolower($value);
    }
}