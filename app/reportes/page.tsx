import { sql } from "@/lib/db"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { TrendingUp, Users, DollarSign, Wifi, Calendar } from "lucide-react"
import Link from "next/link"

async function getReportesData() {
  try {
    // Estadísticas generales
    const [estadisticasGenerales] = await sql`
      SELECT 
        (SELECT COUNT(*) FROM clientes) as total_clientes,
        (SELECT COUNT(*) FROM clientes WHERE estado = 'activo') as clientes_activos,
        (SELECT COUNT(*) FROM pagos WHERE estado = 'pagado') as pagos_realizados,
        (SELECT SUM(monto) FROM pagos WHERE estado = 'pagado') as ingresos_totales,
        (SELECT COUNT(*) FROM equipos WHERE estado_tecnico = 'operativo') as equipos_operativos
    `

    // Ingresos por mes (últimos 6 meses)
    const ingresosPorMes = await sql`
      SELECT 
        DATE_TRUNC('month', fecha_pago) as mes,
        SUM(monto) as total,
        COUNT(*) as cantidad_pagos
      FROM pagos 
      WHERE estado = 'pagado' 
        AND fecha_pago >= CURRENT_DATE - INTERVAL '6 months'
      GROUP BY DATE_TRUNC('month', fecha_pago)
      ORDER BY mes DESC
    `

    // Distribución por tipo de conexión
    const tiposConexion = await sql`
      SELECT 
        tipo_conexion,
        COUNT(*) as cantidad,
        SUM(plan_mensual) as ingresos_potenciales
      FROM clientes 
      WHERE estado = 'activo'
      GROUP BY tipo_conexion
    `

    // Estados de equipos
    const estadosEquipos = await sql`
      SELECT 
        estado_tecnico,
        COUNT(*) as cantidad
      FROM equipos
      GROUP BY estado_tecnico
    `

    // Clientes con pagos vencidos
    const clientesMorosos = await sql`
      SELECT 
        c.nombre,
        c.telefono,
        COUNT(p.id) as pagos_vencidos,
        SUM(p.monto) as deuda_total
      FROM clientes c
      JOIN pagos p ON c.id = p.cliente_id
      WHERE p.estado = 'vencido'
      GROUP BY c.id, c.nombre, c.telefono
      ORDER BY deuda_total DESC
      LIMIT 10
    `

    // Próximas visitas técnicas
    const proximasVisitas = await sql`
      SELECT 
        v.fecha_visita,
        v.tipo_visita,
        c.nombre as cliente_nombre,
        v.tecnico_nombre
      FROM visitas_tecnicas v
      JOIN clientes c ON v.cliente_id = c.id
      WHERE v.estado = 'programada' 
        AND v.fecha_visita >= CURRENT_DATE
      ORDER BY v.fecha_visita ASC
      LIMIT 5
    `

    return {
      estadisticasGenerales,
      ingresosPorMes,
      tiposConexion,
      estadosEquipos,
      clientesMorosos,
      proximasVisitas,
    }
  } catch (error) {
    console.error("Error fetching reportes data:", error)
    return {
      estadisticasGenerales: {},
      ingresosPorMes: [],
      tiposConexion: [],
      estadosEquipos: [],
      clientesMorosos: [],
      proximasVisitas: [],
    }
  }
}

export default async function ReportesPage() {
  const data = await getReportesData()

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-6">
            <div>
              <Link href="/" className="text-blue-600 hover:text-blue-800 text-sm mb-2 block">
                ← Volver al Dashboard
              </Link>
              <h1 className="text-3xl font-bold text-gray-900">Reportes y Estadísticas</h1>
              <p className="text-gray-600">Análisis detallado del negocio y rendimiento</p>
            </div>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Estadísticas principales */}
        <div className="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Clientes</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{data.estadisticasGenerales.total_clientes || 0}</div>
              <p className="text-xs text-muted-foreground">
                {data.estadisticasGenerales.clientes_activos || 0} activos
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Ingresos Totales</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                ${Number(data.estadisticasGenerales.ingresos_totales || 0).toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">{data.estadisticasGenerales.pagos_realizados || 0} pagos</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Equipos Operativos</CardTitle>
              <Wifi className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {data.estadisticasGenerales.equipos_operativos || 0}
              </div>
              <p className="text-xs text-muted-foreground">funcionando bien</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Tasa Actividad</CardTitle>
              <TrendingUp className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-blue-600">
                {data.estadisticasGenerales.total_clientes > 0
                  ? Math.round(
                      (data.estadisticasGenerales.clientes_activos / data.estadisticasGenerales.total_clientes) * 100,
                    )
                  : 0}
                %
              </div>
              <p className="text-xs text-muted-foreground">clientes activos</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Promedio Mensual</CardTitle>
              <Calendar className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                $
                {data.ingresosPorMes.length > 0
                  ? Math.round(
                      data.ingresosPorMes.reduce((sum: number, mes: any) => sum + Number(mes.total), 0) /
                        data.ingresosPorMes.length,
                    ).toLocaleString()
                  : 0}
              </div>
              <p className="text-xs text-muted-foreground">últimos meses</p>
            </CardContent>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          {/* Ingresos por mes */}
          <Card>
            <CardHeader>
              <CardTitle>Ingresos por Mes</CardTitle>
              <CardDescription>Últimos 6 meses de ingresos</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {data.ingresosPorMes.map((mes: any, index: number) => (
                  <div key={index} className="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                      <div className="font-medium">
                        {new Date(mes.mes).toLocaleDateString("es-ES", {
                          year: "numeric",
                          month: "long",
                        })}
                      </div>
                      <div className="text-sm text-gray-600">{mes.cantidad_pagos} pagos</div>
                    </div>
                    <div className="text-right">
                      <div className="font-bold text-green-600">${Number(mes.total).toLocaleString()}</div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Tipos de conexión */}
          <Card>
            <CardHeader>
              <CardTitle>Distribución por Tipo de Conexión</CardTitle>
              <CardDescription>Clientes activos por tipo de servicio</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {data.tiposConexion.map((tipo: any, index: number) => (
                  <div key={index} className="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                      <div className="font-medium capitalize">{tipo.tipo_conexion?.replace("_", " ")}</div>
                      <div className="text-sm text-gray-600">{tipo.cantidad} clientes</div>
                    </div>
                    <div className="text-right">
                      <div className="font-bold text-blue-600">
                        ${Number(tipo.ingresos_potenciales).toLocaleString()}
                      </div>
                      <div className="text-xs text-gray-600">potencial mensual</div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Clientes morosos */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center text-red-600">
                <TrendingUp className="h-5 w-5 mr-2" />
                Clientes con Pagos Vencidos
              </CardTitle>
              <CardDescription>Top 10 clientes con mayor deuda</CardDescription>
            </CardHeader>
            <CardContent>
              {data.clientesMorosos.length === 0 ? (
                <p className="text-center text-gray-500 py-4">¡Excelente! No hay clientes morosos</p>
              ) : (
                <div className="space-y-3">
                  {data.clientesMorosos.map((cliente: any, index: number) => (
                    <div
                      key={index}
                      className="flex justify-between items-center p-3 bg-red-50 rounded-lg border border-red-200"
                    >
                      <div>
                        <div className="font-medium">{cliente.nombre}</div>
                        <div className="text-sm text-gray-600">{cliente.telefono}</div>
                        <div className="text-xs text-red-600">{cliente.pagos_vencidos} pagos vencidos</div>
                      </div>
                      <div className="text-right">
                        <div className="font-bold text-red-600">${Number(cliente.deuda_total).toLocaleString()}</div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Próximas visitas */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <Calendar className="h-5 w-5 mr-2" />
                Próximas Visitas Técnicas
              </CardTitle>
              <CardDescription>Visitas programadas próximamente</CardDescription>
            </CardHeader>
            <CardContent>
              {data.proximasVisitas.length === 0 ? (
                <p className="text-center text-gray-500 py-4">No hay visitas programadas</p>
              ) : (
                <div className="space-y-3">
                  {data.proximasVisitas.map((visita: any, index: number) => (
                    <div
                      key={index}
                      className="flex justify-between items-center p-3 bg-blue-50 rounded-lg border border-blue-200"
                    >
                      <div>
                        <div className="font-medium">{visita.cliente_nombre}</div>
                        <div className="text-sm text-gray-600">Técnico: {visita.tecnico_nombre}</div>
                        <Badge variant="outline" className="text-xs mt-1">
                          {visita.tipo_visita}
                        </Badge>
                      </div>
                      <div className="text-right">
                        <div className="font-medium">{new Date(visita.fecha_visita).toLocaleDateString()}</div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Estados de equipos */}
        <Card className="mt-8">
          <CardHeader>
            <CardTitle className="flex items-center">
              <Wifi className="h-5 w-5 mr-2" />
              Estado de Equipos
            </CardTitle>
            <CardDescription>Distribución del estado técnico de todos los equipos</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              {data.estadosEquipos.map((estado: any, index: number) => (
                <div key={index} className="text-center p-4 bg-gray-50 rounded-lg">
                  <div className="text-2xl font-bold mb-2">{estado.cantidad}</div>
                  <div className="text-sm text-gray-600 capitalize">{estado.estado_tecnico?.replace("_", " ")}</div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
