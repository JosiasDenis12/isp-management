import { sql } from "@/lib/db"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Plus, Phone, Mail, MapPin, Wifi } from "lucide-react"
import Link from "next/link"
import type { Cliente } from "@/lib/db"

async function getClientes(): Promise<Cliente[]> {
  try {
    const clientes = await sql`
      SELECT * FROM clientes 
      ORDER BY created_at DESC
    `
    return clientes as Cliente[]
  } catch (error) {
    console.error("Error fetching clientes:", error)
    return []
  }
}

function getEstadoBadge(estado: string) {
  switch (estado) {
    case "activo":
      return <Badge className="bg-green-100 text-green-800">Activo</Badge>
    case "suspendido":
      return <Badge className="bg-red-100 text-red-800">Suspendido</Badge>
    case "pendiente":
      return <Badge className="bg-yellow-100 text-yellow-800">Pendiente</Badge>
    default:
      return <Badge variant="outline">{estado}</Badge>
  }
}

function getTipoConexionIcon(tipo: string) {
  const iconClass = "h-4 w-4 mr-1"
  switch (tipo) {
    case "fibra_optica":
      return <Wifi className={`${iconClass} text-blue-500`} />
    case "inalambrica":
      return <Wifi className={`${iconClass} text-green-500`} />
    case "cableado_utp":
      return <Wifi className={`${iconClass} text-orange-500`} />
    default:
      return <Wifi className={iconClass} />
  }
}

export default async function ClientesPage() {
  const clientes = await getClientes()

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
              <h1 className="text-3xl font-bold text-gray-900">Gestión de Clientes</h1>
              <p className="text-gray-600">Administra todos tus clientes y sus conexiones</p>
            </div>
            <Link href="/clientes/nuevo">
              <Button className="bg-blue-600 hover:bg-blue-700">
                <Plus className="h-4 w-4 mr-2" />
                Nuevo Cliente
              </Button>
            </Link>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Estadísticas rápidas */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-green-600">
                {clientes.filter((c) => c.estado === "activo").length}
              </div>
              <p className="text-sm text-gray-600">Clientes Activos</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-red-600">
                {clientes.filter((c) => c.estado === "suspendido").length}
              </div>
              <p className="text-sm text-gray-600">Suspendidos</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-yellow-600">
                {clientes.filter((c) => c.estado === "pendiente").length}
              </div>
              <p className="text-sm text-gray-600">Pendientes</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-blue-600">
                ${clientes.reduce((sum, c) => sum + Number(c.plan_mensual), 0).toLocaleString()}
              </div>
              <p className="text-sm text-gray-600">Ingresos Potenciales</p>
            </CardContent>
          </Card>
        </div>

        {/* Lista de clientes */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {clientes.map((cliente) => (
            <Card key={cliente.id} className="hover:shadow-lg transition-shadow">
              <CardHeader>
                <div className="flex justify-between items-start">
                  <div>
                    <CardTitle className="text-lg">{cliente.nombre}</CardTitle>
                    <CardDescription className="flex items-center mt-1">
                      {getTipoConexionIcon(cliente.tipo_conexion)}
                      {cliente.tipo_conexion?.replace("_", " ").toUpperCase()}
                    </CardDescription>
                  </div>
                  {getEstadoBadge(cliente.estado)}
                </div>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  <div className="flex items-center text-sm text-gray-600">
                    <MapPin className="h-4 w-4 mr-2" />
                    {cliente.direccion}
                  </div>
                  <div className="flex items-center text-sm text-gray-600">
                    <Phone className="h-4 w-4 mr-2" />
                    {cliente.telefono}
                  </div>
                  <div className="flex items-center text-sm text-gray-600">
                    <Mail className="h-4 w-4 mr-2" />
                    {cliente.email}
                  </div>
                  <div className="pt-2 border-t">
                    <div className="flex justify-between items-center">
                      <span className="text-sm text-gray-600">Plan mensual:</span>
                      <span className="font-bold text-green-600">${Number(cliente.plan_mensual).toLocaleString()}</span>
                    </div>
                    <div className="flex justify-between items-center mt-1">
                      <span className="text-sm text-gray-600">Cliente desde:</span>
                      <span className="text-sm">{new Date(cliente.fecha_contratacion).toLocaleDateString()}</span>
                    </div>
                  </div>
                  <div className="flex space-x-2 pt-2">
                    <Link href={`/clientes/${cliente.id}`} className="flex-1">
                      <Button variant="outline" size="sm" className="w-full bg-transparent">
                        Ver Detalles
                      </Button>
                    </Link>
                    <Link href={`/pagos?cliente=${cliente.id}`}>
                      <Button variant="outline" size="sm">
                        Pagos
                      </Button>
                    </Link>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {clientes.length === 0 && (
          <Card className="text-center py-12">
            <CardContent>
              <h3 className="text-lg font-medium text-gray-900 mb-2">No hay clientes registrados</h3>
              <p className="text-gray-600 mb-4">Comienza agregando tu primer cliente</p>
              <Link href="/clientes/nuevo">
                <Button>
                  <Plus className="h-4 w-4 mr-2" />
                  Agregar Cliente
                </Button>
              </Link>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  )
}
