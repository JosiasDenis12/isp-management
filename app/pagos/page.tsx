import { sql } from "@/lib/db"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Plus, AlertTriangle, CheckCircle, Clock } from "lucide-react"
import Link from "next/link"
import type { Pago } from "@/lib/db"

async function getPagos(): Promise<Pago[]> {
  try {
    const pagos = await sql`
      SELECT p.*, c.nombre as cliente_nombre
      FROM pagos p
      JOIN clientes c ON p.cliente_id = c.id
      ORDER BY p.fecha_vencimiento DESC
    `
    return pagos as Pago[]
  } catch (error) {
    console.error("Error fetching pagos:", error)
    return []
  }
}

function getEstadoBadge(estado: string) {
  switch (estado) {
    case "pagado":
      return (
        <Badge className="bg-green-100 text-green-800">
          <CheckCircle className="h-3 w-3 mr-1" />
          Pagado
        </Badge>
      )
    case "vencido":
      return (
        <Badge className="bg-red-100 text-red-800">
          <AlertTriangle className="h-3 w-3 mr-1" />
          Vencido
        </Badge>
      )
    case "pendiente":
      return (
        <Badge className="bg-yellow-100 text-yellow-800">
          <Clock className="h-3 w-3 mr-1" />
          Pendiente
        </Badge>
      )
    default:
      return <Badge variant="outline">{estado}</Badge>
  }
}

function getMetodoPagoIcon(metodo: string) {
  switch (metodo) {
    case "transferencia":
      return "🏦"
    case "efectivo":
      return "💵"
    case "paypal":
      return "💳"
    case "tarjeta":
      return "💳"
    default:
      return "💰"
  }
}

export default async function PagosPage() {
  const pagos = await getPagos()

  const estadisticas = {
    total: pagos.length,
    pagados: pagos.filter((p) => p.estado === "pagado").length,
    pendientes: pagos.filter((p) => p.estado === "pendiente").length,
    vencidos: pagos.filter((p) => p.estado === "vencido").length,
    ingresos: pagos.filter((p) => p.estado === "pagado").reduce((sum, p) => sum + Number(p.monto), 0),
  }

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
              <h1 className="text-3xl font-bold text-gray-900">Pagos y Facturación</h1>
              <p className="text-gray-600">Gestiona todos los pagos y genera facturas</p>
            </div>
            <Link href="/pagos/nuevo">
              <Button className="bg-blue-600 hover:bg-blue-700">
                <Plus className="h-4 w-4 mr-2" />
                Registrar Pago
              </Button>
            </Link>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Estadísticas */}
        <div className="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-blue-600">{estadisticas.total}</div>
              <p className="text-sm text-gray-600">Total Pagos</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-green-600">{estadisticas.pagados}</div>
              <p className="text-sm text-gray-600">Pagados</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-yellow-600">{estadisticas.pendientes}</div>
              <p className="text-sm text-gray-600">Pendientes</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-red-600">{estadisticas.vencidos}</div>
              <p className="text-sm text-gray-600">Vencidos</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-green-600">${estadisticas.ingresos.toLocaleString()}</div>
              <p className="text-sm text-gray-600">Ingresos</p>
            </CardContent>
          </Card>
        </div>

        {/* Lista de pagos */}
        <Card>
          <CardHeader>
            <CardTitle>Historial de Pagos</CardTitle>
            <CardDescription>Todos los pagos registrados en el sistema</CardDescription>
          </CardHeader>
          <CardContent>
            {pagos.length === 0 ? (
              <div className="text-center py-12">
                <h3 className="text-lg font-medium text-gray-900 mb-2">No hay pagos registrados</h3>
                <p className="text-gray-600 mb-4">Comienza registrando el primer pago</p>
                <Link href="/pagos/nuevo">
                  <Button>
                    <Plus className="h-4 w-4 mr-2" />
                    Registrar Pago
                  </Button>
                </Link>
              </div>
            ) : (
              <div className="space-y-4">
                {pagos.map((pago) => (
                  <div key={pago.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div className="flex justify-between items-start mb-3">
                      <div>
                        <h3 className="font-semibold text-lg">{pago.cliente_nombre}</h3>
                        <p className="text-sm text-gray-600">Factura: {pago.numero_factura}</p>
                      </div>
                      <div className="text-right">
                        <div className="text-xl font-bold text-green-600">${Number(pago.monto).toLocaleString()}</div>
                        {getEstadoBadge(pago.estado)}
                      </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                      <div>
                        <span className="text-gray-600">Método de pago:</span>
                        <div className="font-medium">
                          {getMetodoPagoIcon(pago.metodo_pago)} {pago.metodo_pago}
                        </div>
                      </div>
                      <div>
                        <span className="text-gray-600">Fecha de pago:</span>
                        <div className="font-medium">{new Date(pago.fecha_pago).toLocaleDateString()}</div>
                      </div>
                      <div>
                        <span className="text-gray-600">Fecha vencimiento:</span>
                        <div className="font-medium">{new Date(pago.fecha_vencimiento).toLocaleDateString()}</div>
                      </div>
                      <div>
                        <span className="text-gray-600">Registrado:</span>
                        <div className="font-medium">{new Date(pago.created_at).toLocaleDateString()}</div>
                      </div>
                    </div>

                    {pago.observaciones && (
                      <div className="mt-3 p-2 bg-gray-50 rounded text-sm">
                        <span className="text-gray-600">Observaciones:</span> {pago.observaciones}
                      </div>
                    )}
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
