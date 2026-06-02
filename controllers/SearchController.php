<?php
require_once 'config/database.php';

class SearchController {
    private Database $database;

    public function __construct() {
        $this->database = new Database();
    }

    public function index() {
        requireAuth();

        $q = trim((string)($_GET['q'] ?? ''));
        $minLen = 2;

        $data = [
            'title' => 'Buscar - ' . APP_NAME,
            'q' => $q,
            'clientes' => [],
            'pagos' => [],
            'equipos' => [],
            'minLen' => $minLen,
        ];

        if (mb_strlen($q, 'UTF-8') >= $minLen) {
            $data['clientes'] = $this->searchClientes($q, 20);
            $data['pagos'] = $this->searchPagos($q, 20);
            $data['equipos'] = $this->searchEquipos($q, 20);
        }

        $this->loadView('search/index', $data);
    }

    public function suggest() {
        requireAuth();

        header('Content-Type: application/json; charset=utf-8');

        $q = trim((string)($_GET['q'] ?? ''));
        $minLen = 2;

        if (mb_strlen($q, 'UTF-8') < $minLen) {
            echo json_encode([
                'q' => $q,
                'minLen' => $minLen,
                'groups' => [
                    'clientes' => [],
                    'pagos' => [],
                    'equipos' => [],
                ],
            ]);
            return;
        }

        $clientes = $this->searchClientes($q, 5);
        $pagos = $this->searchPagos($q, 5);
        $equipos = $this->searchEquipos($q, 5);

        echo json_encode([
            'q' => $q,
            'minLen' => $minLen,
            'groups' => [
                'clientes' => $clientes,
                'pagos' => $pagos,
                'equipos' => $equipos,
            ],
        ]);
    }

    private function searchClientes(string $q, int $limit): array {
        $conn = $this->database->getConnection();

        $isNumeric = ctype_digit($q);
        $like = '%' . $q . '%';

        $sql = "SELECT id, nombre, telefono, email, estado
                FROM clientes
                WHERE (nombre LIKE :like OR telefono LIKE :like OR email LIKE :like" . ($isNumeric ? " OR id = :id" : "") . ")
                ORDER BY
                    CASE WHEN nombre LIKE :prefix THEN 0 ELSE 1 END,
                    nombre ASC
                LIMIT :limit";

        $stmt = $conn->prepare($sql);
        $prefix = $q . '%';

        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
        $stmt->bindValue(':prefix', $prefix, PDO::PARAM_STR);
        if ($isNumeric) {
            $stmt->bindValue(':id', (int)$q, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = [];

        foreach ($rows as $row) {
            $subtitleParts = [];
            if (!empty($row['telefono'])) {
                $subtitleParts[] = (string)$row['telefono'];
            }
            if (!empty($row['email'])) {
                $subtitleParts[] = (string)$row['email'];
            }
            $subtitle = implode(' • ', $subtitleParts);

            $results[] = [
                'type' => 'cliente',
                'id' => (int)$row['id'],
                'title' => (string)$row['nombre'],
                'subtitle' => $subtitle,
                'badge' => isset($row['estado']) ? ucfirst((string)$row['estado']) : null,
                'url' => url('clientes/' . $row['id']),
            ];
        }

        return $results;
    }

    private function searchPagos(string $q, int $limit): array {
        $conn = $this->database->getConnection();

        $isNumeric = ctype_digit($q);
        $like = '%' . $q . '%';

        $sql = "SELECT p.id, p.numero_factura, p.estado, p.monto, p.fecha_vencimiento, c.nombre AS cliente_nombre
                FROM pagos p
                JOIN clientes c ON p.cliente_id = c.id
                WHERE (p.numero_factura LIKE :like OR c.nombre LIKE :like" . ($isNumeric ? " OR p.id = :id" : "") . ")
                ORDER BY
                    CASE WHEN p.numero_factura LIKE :prefix THEN 0 ELSE 1 END,
                    p.fecha_vencimiento DESC
                LIMIT :limit";

        $stmt = $conn->prepare($sql);
        $prefix = $q . '%';

        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
        $stmt->bindValue(':prefix', $prefix, PDO::PARAM_STR);
        if ($isNumeric) {
            $stmt->bindValue(':id', (int)$q, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = [];

        foreach ($rows as $row) {
            $title = (string)($row['numero_factura'] ?: ('Pago #' . $row['id']));
            $subtitleParts = [];
            if (!empty($row['cliente_nombre'])) {
                $subtitleParts[] = (string)$row['cliente_nombre'];
            }
            if (isset($row['monto'])) {
                $subtitleParts[] = '$' . number_format((float)$row['monto']);
            }
            if (!empty($row['fecha_vencimiento'])) {
                $subtitleParts[] = 'Vence ' . date('d/m/Y', strtotime((string)$row['fecha_vencimiento']));
            }
            $subtitle = implode(' • ', $subtitleParts);

            $results[] = [
                'type' => 'pago',
                'id' => (int)$row['id'],
                'title' => $title,
                'subtitle' => $subtitle,
                'badge' => isset($row['estado']) ? ucfirst((string)$row['estado']) : null,
                'url' => url('pagos/' . $row['id']),
            ];
        }

        return $results;
    }

    private function searchEquipos(string $q, int $limit): array {
        $conn = $this->database->getConnection();

        $isNumeric = ctype_digit($q);
        $like = '%' . $q . '%';

        $sql = "SELECT e.id, e.tipo_equipo, e.marca, e.modelo, e.numero_serie, e.estado_tecnico, c.nombre AS cliente_nombre
                FROM equipos e
                JOIN clientes c ON e.cliente_id = c.id
                WHERE (e.numero_serie LIKE :like OR e.marca LIKE :like OR e.modelo LIKE :like OR c.nombre LIKE :like" . ($isNumeric ? " OR e.id = :id" : "") . ")
                ORDER BY
                    CASE WHEN e.numero_serie LIKE :prefix THEN 0 ELSE 1 END,
                    e.created_at DESC
                LIMIT :limit";

        $stmt = $conn->prepare($sql);
        $prefix = $q . '%';

        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
        $stmt->bindValue(':prefix', $prefix, PDO::PARAM_STR);
        if ($isNumeric) {
            $stmt->bindValue(':id', (int)$q, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = [];

        foreach ($rows as $row) {
            $labelParts = [];
            if (!empty($row['tipo_equipo'])) {
                $labelParts[] = ucwords(str_replace('_', ' ', (string)$row['tipo_equipo']));
            }
            if (!empty($row['marca'])) {
                $labelParts[] = (string)$row['marca'];
            }
            if (!empty($row['modelo'])) {
                $labelParts[] = (string)$row['modelo'];
            }
            $title = trim(implode(' ', $labelParts));
            if ($title === '') {
                $title = 'Equipo #' . $row['id'];
            }

            $subtitleParts = [];
            if (!empty($row['cliente_nombre'])) {
                $subtitleParts[] = (string)$row['cliente_nombre'];
            }
            if (!empty($row['numero_serie'])) {
                $subtitleParts[] = 'S/N ' . (string)$row['numero_serie'];
            }
            $subtitle = implode(' • ', $subtitleParts);

            $results[] = [
                'type' => 'equipo',
                'id' => (int)$row['id'],
                'title' => $title,
                'subtitle' => $subtitle,
                'badge' => isset($row['estado_tecnico']) ? ucwords(str_replace('_', ' ', (string)$row['estado_tecnico'])) : null,
                'url' => url('equipos/' . $row['id']),
            ];
        }

        return $results;
    }

    private function loadView($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }
}
?>
