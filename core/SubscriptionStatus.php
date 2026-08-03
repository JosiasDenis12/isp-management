<?php
/** Única fuente de verdad para el ciclo de cobro de una suscripción. */
final class SubscriptionStatus
{
    public const DIAS_PROXIMO_VENCIMIENTO = 5;

    public static function calcular(?string $ultimaFechaPago, ?string $fechaContratacion, int $diasGracia, ?DateTimeInterface $hoy = null): array
    {
        $base = self::parseDate($ultimaFechaPago) ?: self::parseDate($fechaContratacion);
        $today = $hoy ? DateTimeImmutable::createFromInterface($hoy)->setTime(0, 0) : new DateTimeImmutable('today');
        $diasGracia = max(0, min(31, $diasGracia));
        if (!$base) return self::sinFechas($diasGracia);

        // Frecuencia mensual: 15/07 -> 15/08, limitado al último día del mes.
        $proximoPago = self::sumarMes($base);
        $fechaCorte = $proximoPago->modify("+{$diasGracia} days");
        $diasParaPago = (int) $today->diff($proximoPago)->format('%r%a');
        $diasRestantes = (int) $today->diff($fechaCorte)->format('%r%a');
        $estado = $diasRestantes < 0 ? 'vencido' : ($diasParaPago <= self::DIAS_PROXIMO_VENCIMIENTO ? 'porvencer' : 'aldia');

        return [
            'fecha_ultimo_pago' => $ultimaFechaPago ?: null,
            'proxima_fecha_pago' => $proximoPago->format('Y-m-d'),
            'fecha_corte' => $fechaCorte->format('Y-m-d'),
            'fecha_vencimiento' => $fechaCorte->format('Y-m-d'),
            'dias_para_pago' => $diasParaPago,
            'dias_restantes' => max(0, $diasRestantes),
            'dias_vencido' => max(0, -$diasRestantes),
            'estado_calculado' => $estado,
            'en_periodo_gracia' => $diasParaPago < 0 && $diasRestantes >= 0,
            'dias_gracia' => $diasGracia,
        ];
    }
    private static function sinFechas(int $diasGracia): array { return ['fecha_ultimo_pago'=>null,'proxima_fecha_pago'=>null,'fecha_corte'=>null,'fecha_vencimiento'=>null,'dias_para_pago'=>null,'dias_restantes'=>null,'dias_vencido'=>0,'estado_calculado'=>'sinpagos','en_periodo_gracia'=>false,'dias_gracia'=>$diasGracia]; }
    private static function parseDate(?string $value): ?DateTimeImmutable { $value=trim((string)$value); if ($value==='' || $value==='0000-00-00') return null; $date=DateTimeImmutable::createFromFormat('!Y-m-d',substr($value,0,10)); return $date ?: null; }
    private static function sumarMes(DateTimeImmutable $fecha): DateTimeImmutable { $siguiente=$fecha->modify('first day of next month'); $dia=min((int)$fecha->format('d'),(int)$siguiente->format('t')); return $siguiente->setDate((int)$siguiente->format('Y'),(int)$siguiente->format('m'),$dia); }
}
