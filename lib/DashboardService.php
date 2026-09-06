<?php

/**
 * Fuente única de datos del panel. No devuelve datos de ejemplo: cada fila
 * procede de la base SQLite configurada para SkyNetwork.
 */
class DashboardService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function getDashboard(string $period = '7d'): array
    {
        $period = in_array($period, ['7d', '30d', 'month', 'previous_month', 'year'], true) ? $period : '7d';
        [$from, $to, $bucket] = $this->period($period);
        $today = date('Y-m-d');

        $stats = $this->one("SELECT
            (SELECT COUNT(*) FROM clientes WHERE estado = 'activo') AS clientes_activos,
            (SELECT COUNT(*) FROM clientes) AS clientes_total,
            (SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'pagado' AND fecha_pago >= :from AND fecha_pago < :to) AS ingresos,
            (SELECT COUNT(*) FROM pagos WHERE estado = 'pagado' AND fecha_pago >= :from AND fecha_pago < :to) AS pagos_procesados,
            (SELECT COUNT(DISTINCT cliente_id) FROM pagos WHERE estado IN ('pendiente', 'vencido') AND fecha_vencimiento < :today) AS clientes_vencidos,
            (SELECT COUNT(*) FROM equipos WHERE estado_tecnico = 'operativo') AS equipos_operativos,
            (SELECT COUNT(*) FROM equipos WHERE estado_tecnico = 'necesita_revision') AS equipos_revision", [
                ':from' => $from, ':to' => $to, ':today' => $today,
            ]);

        $alerts = $this->alerts($today);
        return [
            'period' => $period,
            'stats' => $stats,
            'series' => $this->revenueSeries($from, $to, $bucket),
            'alerts' => $alerts,
            'recentActivity' => $this->activity(5),
            'calendar' => $this->calendar(date('Y-m')),
        ];
    }

    public function getActivity(int $limit = 100): array
    {
        return $this->activity(max(1, min($limit, 200)));
    }

    public function getCalendar(string $month): array
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) $month = date('Y-m');
        return $this->calendar($month);
    }

    private function period(string $period): array
    {
        $today = new DateTimeImmutable('today');
        switch ($period) {
            case '30d': return [$today->modify('-29 days')->format('Y-m-d'), $today->modify('+1 day')->format('Y-m-d'), 'day'];
            case 'month': return [$today->modify('first day of this month')->format('Y-m-d'), $today->modify('first day of next month')->format('Y-m-d'), 'day'];
            case 'previous_month': return [$today->modify('first day of last month')->format('Y-m-d'), $today->modify('first day of this month')->format('Y-m-d'), 'day'];
            case 'year': return [$today->modify('first day of january')->format('Y-m-d'), $today->modify('first day of january next year')->format('Y-m-d'), 'month'];
            default: return [$today->modify('-6 days')->format('Y-m-d'), $today->modify('+1 day')->format('Y-m-d'), 'day'];
        }
    }

    private function revenueSeries(string $from, string $to, string $bucket): array
    {
        $format = $bucket === 'month' ? '%Y-%m' : '%Y-%m-%d';
        $stmt = $this->db->prepare("SELECT strftime('$format', fecha_pago) bucket, COALESCE(SUM(monto), 0) ingresos, COUNT(*) pagos
            FROM pagos WHERE estado = 'pagado' AND fecha_pago >= :from AND fecha_pago < :to GROUP BY bucket ORDER BY bucket");
        $stmt->execute([':from' => $from, ':to' => $to]);
        $indexed = [];
        foreach ($stmt->fetchAll() as $row) $indexed[$row['bucket']] = $row;
        $result = [];
        $cursor = new DateTimeImmutable($from);
        $end = new DateTimeImmutable($to);
        while ($cursor < $end) {
            $key = $bucket === 'month' ? $cursor->format('Y-m') : $cursor->format('Y-m-d');
            $row = $indexed[$key] ?? ['ingresos' => 0, 'pagos' => 0];
            $result[] = ['key' => $key, 'label' => $bucket === 'month' ? $this->monthLabel($cursor) : $cursor->format('j M'), 'ingresos' => (float)$row['ingresos'], 'pagos' => (int)$row['pagos']];
            $cursor = $bucket === 'month' ? $cursor->modify('first day of next month') : $cursor->modify('+1 day');
        }
        return $result;
    }

    private function alerts(string $today): array
    {
        $overdue = (int)$this->value("SELECT COUNT(DISTINCT cliente_id) FROM pagos WHERE estado IN ('pendiente','vencido') AND fecha_vencimiento < :today", [':today' => $today]);
        $review = (int)$this->value("SELECT COUNT(*) FROM equipos WHERE estado_tecnico = 'necesita_revision'");
        $upcoming = (int)$this->value("SELECT COUNT(DISTINCT cliente_id) FROM pagos WHERE estado = 'pendiente' AND fecha_vencimiento BETWEEN :today AND date(:today, '+7 days')", [':today' => $today]);
        $visits = (int)$this->value("SELECT COUNT(*) FROM visitas_tecnicas WHERE estado IN ('programada','pendiente') AND date(fecha_visita) <= date(:today, '+7 days')", [':today' => $today]);
        $items = [];
        if ($overdue) $items[] = ['kind'=>'danger','icon'=>'fa-triangle-exclamation','title'=> $overdue . ' cliente' . ($overdue === 1 ? '' : 's') . ' con pago vencido', 'subtitle'=>'Requiere seguimiento', 'url'=>url('pagos?estado=vencido')];
        if ($review) $items[] = ['kind'=>'warning','icon'=>'fa-screwdriver-wrench','title'=> $review . ' equipo' . ($review === 1 ? '' : 's') . ' necesita' . ($review === 1 ? '' : 'n') . ' revisión', 'subtitle'=>'Programar mantenimiento', 'url'=>url('equipos?estado_tecnico=necesita_revision')];
        if ($upcoming) $items[] = ['kind'=>'info','icon'=>'fa-calendar-days','title'=> $upcoming . ' pago' . ($upcoming === 1 ? '' : 's') . ' próximo' . ($upcoming === 1 ? '' : 's') . ' a vencer', 'subtitle'=>'En los próximos 7 días', 'url'=>url('pagos?estado=pendiente')];
        if ($visits) $items[] = ['kind'=>'purple','icon'=>'fa-helmet-safety','title'=> $visits . ' visita' . ($visits === 1 ? '' : 's') . ' pendiente' . ($visits === 1 ? '' : 's'), 'subtitle'=>'Revisar agenda técnica', 'url'=>url('equipos')];
        return $items;
    }

    private function activity(int $limit): array
    {
        // Unión de altas y operaciones ya registradas; no existe tabla de auditoría,
        // por ello se muestra únicamente el historial comprobable en SQLite.
        $sql = "SELECT * FROM (
            SELECT COALESCE(created_at, fecha_contratacion || ' 00:00:00') fecha, 'cliente' tipo, 'Nuevo cliente' descripcion, nombre relacionado, estado, NULL monto FROM clientes
            UNION ALL SELECT COALESCE(p.created_at, p.fecha_pago || ' 00:00:00'), 'pago', CASE WHEN p.estado='pagado' THEN 'Pago registrado' ELSE 'Pago pendiente' END, COALESCE(c.nombre, 'Cliente eliminado'), p.estado, p.monto FROM pagos p LEFT JOIN clientes c ON c.id=p.cliente_id
            UNION ALL SELECT COALESCE(e.created_at, e.fecha_instalacion || ' 00:00:00'), 'equipo', 'Equipo registrado', COALESCE(c.nombre, e.tipo_equipo), e.estado_tecnico, NULL FROM equipos e LEFT JOIN clientes c ON c.id=e.cliente_id
            UNION ALL SELECT COALESCE(v.created_at, v.fecha_visita || ' 00:00:00'), 'visita', COALESCE(v.tipo_visita, 'Visita técnica'), COALESCE(c.nombre, 'Equipo técnico'), v.estado, NULL FROM visitas_tecnicas v LEFT JOIN clientes c ON c.id=v.cliente_id
        ) ORDER BY datetime(replace(fecha, 'T', ' ')) DESC, fecha DESC LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll();
    }

    private function calendar(string $month): array
    {
        $start = $month . '-01';
        $end = (new DateTimeImmutable($start))->modify('first day of next month')->format('Y-m-d');
        $stmt = $this->db->prepare("SELECT fecha, titulo, tipo, detalle FROM (
            SELECT fecha_vencimiento fecha, 'Vencimiento de pago' titulo, CASE WHEN fecha_vencimiento < date('now') THEN 'vencido' ELSE 'proximo' END tipo, c.nombre detalle FROM pagos p JOIN clientes c ON c.id=p.cliente_id WHERE p.estado IN ('pendiente','vencido')
            UNION ALL SELECT date(fecha_visita), COALESCE(tipo_visita, 'Visita técnica'), 'visita', COALESCE(c.nombre, 'Sin cliente') FROM visitas_tecnicas v LEFT JOIN clientes c ON c.id=v.cliente_id WHERE v.estado IN ('programada','pendiente')
        ) WHERE fecha >= :start AND fecha < :end ORDER BY fecha");
        $stmt->execute([':start'=>$start, ':end'=>$end]);
        return ['month'=>$month, 'events'=>$stmt->fetchAll()];
    }

    private function monthLabel(DateTimeImmutable $date): string { return ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'][(int)$date->format('n') - 1]; }
    private function one(string $sql, array $params = []): array { $stmt=$this->db->prepare($sql); $stmt->execute($params); return $stmt->fetch() ?: []; }
    private function value(string $sql, array $params = []) { $stmt=$this->db->prepare($sql); $stmt->execute($params); return $stmt->fetchColumn(); }
}
