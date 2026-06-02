import { sql } from "@/lib/db"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Plus, Wifi, AlertTriangle, CheckCircle, Wrench } from "lucide-react"
import Link from "next/link"
import type { Equipo } from "@/lib/db"

async function getEquipos(): Promise<Equipo[]> {
  try {
    const equipos = await sql`
      SELECT e.*, c.nombre as cliente_nombre
      FROM equipos e
      JOIN clientes c ON e.cliente_id = c.id
      ORDER BY e.created_at DESC
    `
    return equipos as Equipo[]
  } catch (error) {
    console.error("Error fetching equipos:", error)
    return []
  }
}

function getEstadoBadge(estado: string) {
  switch (estado) {
    case "operativo":
      return (
        <Badge className="bg-green-100 text-green-800">
          <CheckCircle className="h-3 w-3 mr-1" />
          Operativo
        </Badge>
      )
    case "necesita_revision":
      return (
        <Badge className="bg-yellow-100 text-yellow-800">
          <AlertTriangle className="h-3 w-3 mr-1" />
          Necesita Revisión
        </Badge>
      )
    case "dañado":
      return (
        <Badge className="bg-red-100 text-red-800">
          <Wrench className="h-3 w-3 mr-1" />
          Dañado
        </Badge>
      )
    default:
      return <Badge variant="outline">{estado}</Badge>
  }
}

export default async function EquiposPage() {
  const equipos = await getEquipos()

  const estadisticas = {
    total: equipos.length,
    operativos: equipos.filter((e) => e.estado_tecnico === "operativo").length,
    necesitan_revision: equipos.filter((e) => e.estado_tecnico === "necesita_revision").length,
    dañados: equipos.filter((e) => e.estado_tecnico === "dañado").length,
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
              <h1 className="text-3xl font-bold text-gray-900">Equipos Técnicos</h1>
              <p className="text-gray-600">Gestiona todos los equipos instalados en casa de clientes</p>
            </div>
            <Link href="/equipos/nuevo">
              <Button className="bg-blue-600 hover:bg-blue-700">
                <Plus className="h-4 w-4 mr-2" />
                Registrar Equipo
              </Button>
            </Link>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Estadísticas */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-blue-600">{estadisticas.total}</div>
              <p className="text-sm text-gray-600">Total Equipos</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-green-600">{estadisticas.operativos}</div>
              <p className="text-sm text-gray-600">Operativos</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-yellow-600">{estadisticas.necesitan_revision}</div>
              <p className="text-sm text-gray-600">Necesitan Revisión</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4">
              <div className="text-2xl font-bold text-red-600">{estadisticas.dañados}</div>
              <p className="text-sm text-gray-600">Dañados</p>
            </CardContent>
          </Card>
        </div>

        {/* Lista de equipos */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {equipos.map((equipo) => (
            <Card key={equipo.id} className="hover:shadow-lg transition-shadow">
              <CardHeader>
                <div className="flex justify-between items-start">
                  <div>
                    <CardTitle className="text-lg flex items-center">
                      <Wifi className="h-5 w-5 mr-2" />
                      {equipo.tipo_equipo}
                    </CardTitle>
                    <CardDescription>{equipo.cliente_nombre}</CardDescription>
                  </div>
                  {getEstadoBadge(equipo.estado_tecnico)}
                </div>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  <div className="grid grid-cols-2 gap-2 text-sm">
                    <div>
                      <span className="text-gray-600">Marca:</span>
                      <div className="font-medium">{equipo.marca}</div>
                    </div>
                    <div>
                      <span className="text-gray-600">Modelo:</span>
                      <div className="font-medium">{equipo.modelo}</div>
                    </div>
                  </div>

                  <div className="text-sm">
                    <span className="text-gray-600">Número de serie:</span>
                    <div className="font-mono text-xs bg-gray-100 p-1 rounded">{equipo.numero_serie}</div>
                  </div>

                  <div className="text-sm">
                    <span className="text-gray-600">Fecha instalación:</span>
                    <div className="font-medium">
                      {equipo.fecha_instalacion
                        ? new Date(equipo.fecha_instalacion).toLocaleDateString()
                        : "No registrada"}
                    </div>
                  </div>

                  {equipo.observaciones_tecnico && (
                    <div className="text-sm">
                      <span className="text-gray-600">Observaciones:</span>
                      <div className="text-xs bg-blue-50 p-2 rounded mt-1">{equipo.observaciones_tecnico}</div>
                    </div>
                  )}

                  <div className="flex space-x-2 pt-2">
                    <Link href={`/equipos/${equipo.id}`} className="flex-1">
                      <Button variant="outline" size="sm" className="w-full bg-transparent">
                        Ver Detalles
                      </Button>
                    </Link>
                    <Link href={`/visitas?equipo=${equipo.id}`}>
                      <Button variant="outline" size="sm">
                        Visitas
                      </Button>
                    </Link>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {equipos.length === 0 && (
          <Card className="text-center py-12">
            <CardContent>
              <h3 className="text-lg font-medium text-gray-900 mb-2">No hay equipos registrados</h3>
              <p className="text-gray-600 mb-4">Comienza registrando el primer equipo</p>
              <Link href="/equipos/nuevo">
                <Button>
                  <Plus className="h-4 w-4 mr-2" />
                  Registrar Equipo
                </Button>
              </Link>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  )
}
