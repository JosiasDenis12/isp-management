"use client"

import { sql } from "@/lib/db"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Users, DollarSign, AlertTriangle, Wifi, TrendingUp, Calendar, Database, RefreshCw } from "lucide-react"
import Link from "next/link"

async function getDashboardStats() {
  try {
    // Verificar si las tablas existen
    const tablesCheck = await sql`
      SELECT table_name 
      FROM information_schema.tables 
      WHERE table_schema = 'public' 
      AND table_name IN ('clientes', 'pagos', 'equipos', 'visitas_tecnicas')
    `

    if (tablesCheck.length === 0) {
      return {
        error: "Las tablas de la base de datos no existen. Ejecuta los scripts SQL primero.",
        tablesExist: false,
        clientes: { total: 0, activos: 0, suspendidos: 0, pendientes: 0 },
        pagos: { total_pagos: 0, ingresos_mes: 0, pagos_vencidos: 0, pagos_pendientes: 0 },
        equipos: { total_equipos: 0, operativos: 0, necesitan_revision: 0 },
        proximosVencimientos: [],
      }
    }

    // Estadísticas de clientes
    const [clientesStats] = await sql`
      SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN estado = 'activo' THEN 1 END) as activos,
        COUNT(CASE WHEN estado = 'suspendido' THEN 1 END) as suspendidos,
        COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes
      FROM clientes
    `

    // Estadísticas de pagos
    const [pagosStats] = await sql`
      SELECT 
        COUNT(*) as total_pagos,
        COALESCE(SUM(CASE WHEN estado = 'pagado' THEN monto ELSE 0 END), 0) as ingresos_mes,
        COUNT(CASE WHEN estado = 'vencido' THEN 1 END) as pagos_vencidos,
        COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pagos_pendientes
      FROM pagos 
      WHERE DATE_TRUNC('month', fecha_vencimiento) = DATE_TRUNC('month', CURRENT_DATE)
    `

    // Estadísticas de equipos
    const [equiposStats] = await sql`
      SELECT 
        COUNT(*) as total_equipos,
        COUNT(CASE WHEN estado_tecnico = 'operativo' THEN 1 END) as operativos,
        COUNT(CASE WHEN estado_tecnico = 'necesita_revision' THEN 1 END) as necesitan_revision
      FROM equipos
    `

    // Próximos vencimientos
    const proximosVencimientos = await sql`
      SELECT p.*, c.nombre as cliente_nombre
      FROM pagos p
      JOIN clientes c ON p.cliente_id = c.id
      WHERE p.estado = 'pendiente' 
      AND p.fecha_vencimiento <= CURRENT_DATE + INTERVAL '7 days'
      ORDER BY p.fecha_vencimiento ASC
      LIMIT 5
    `

    return {
      tablesExist: true,
      clientes: clientesStats,
      pagos: pagosStats,
      equipos: equiposStats,
      proximosVencimientos,
    }
  } catch (error) {
    console.error("Error fetching dashboard stats:", error)
    return {
      error: `Error de base de datos: ${error.message}`,
      tablesExist: false,
      clientes: { total: 0, activos: 0, suspendidos: 0, pendientes: 0 },
      pagos: { total_pagos: 0, ingresos_mes: 0, pagos_vencidos: 0, pagos_pendientes: 0 },
      equipos: { total_equipos: 0, operativos: 0, necesitan_revision: 0 },
      proximosVencimientos: [],
    }
  }
}

async function createTables() {
  try {
    // Crear tabla de clientes
    await sql`
      CREATE TABLE IF NOT EXISTS clientes (
        id SERIAL PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        direccion TEXT,
        telefono VARCHAR(20),
        email VARCHAR(255),
        estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('activo', 'suspendido', 'pendiente')),
        tipo_conexion VARCHAR(50) CHECK (tipo_conexion IN ('fibra_optica', 'inalambrica', 'cableado_utp')),
        fecha_contratacion DATE DEFAULT CURRENT_DATE,
        plan_mensual DECIMAL(10,2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `

    // Crear tabla de pagos
    await sql`
      CREATE TABLE IF NOT EXISTS pagos (
        id SERIAL PRIMARY KEY,
        cliente_id INTEGER REFERENCES clientes(id) ON DELETE CASCADE,
        monto DECIMAL(10,2) NOT NULL,
        fecha_pago DATE NOT NULL,
        fecha_vencimiento DATE NOT NULL,
        metodo_pago VARCHAR(50) CHECK (metodo_pago IN ('transferencia', 'efectivo', 'paypal', 'tarjeta')),
        estado VARCHAR(20) DEFAULT 'pendiente' CHECK (estado IN ('pagado', 'pendiente', 'vencido')),
        numero_factura VARCHAR(100),
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `

    // Crear tabla de equipos
    await sql`
      CREATE TABLE IF NOT EXISTS equipos (
        id SERIAL PRIMARY KEY,
        cliente_id INTEGER REFERENCES clientes(id) ON DELETE CASCADE,
        tipo_equipo VARCHAR(100) NOT NULL,
        marca VARCHAR(100),
        modelo VARCHAR(100),
        numero_serie VARCHAR(100),
        estado_tecnico VARCHAR(20) DEFAULT 'operativo' CHECK (estado_tecnico IN ('operativo', 'necesita_revision', 'dañado')),
        fecha_instalacion DATE,
        observaciones_tecnico TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `

    // Insertar datos de ejemplo
    await sql`
      INSERT INTO clientes (nombre, direccion, telefono, email, estado, tipo_conexion, plan_mensual) VALUES
      ('Juan Pérez', 'Calle 123 #45-67', '+57 300 123 4567', 'juan@email.com', 'activo', 'fibra_optica', 50000.00),
      ('María García', 'Carrera 89 #12-34', '+57 301 234 5678', 'maria@email.com', 'activo', 'inalambrica', 35000.00),
      ('Carlos López', 'Avenida 56 #78-90', '+57 302 345 6789', 'carlos@email.com', 'suspendido', 'cableado_utp', 40000.00)
      ON CONFLICT DO NOTHING
    `

    await sql`
      INSERT INTO pagos (cliente_id, monto, fecha_pago, fecha_vencimiento, metodo_pago, estado, numero_factura) VALUES
      (1, 50000.00, '2024-01-15', '2024-01-15', 'transferencia', 'pagado', 'FAC-001'),
      (2, 35000.00, '2024-01-20', '2024-01-20', 'efectivo', 'pagado', 'FAC-002'),
      (1, 50000.00, CURRENT_DATE + INTERVAL '5 days', CURRENT_DATE + INTERVAL '5 days', 'transferencia', 'pendiente', 'FAC-003')
      ON CONFLICT DO NOTHING
    `

    await sql`
      INSERT INTO equipos (cliente_id, tipo_equipo, marca, modelo, numero_serie, estado_tecnico, fecha_instalacion, observaciones_tecnico) VALUES
      (1, 'Router Fibra', 'TP-Link', 'Archer AX73', 'SN001234', 'operativo', '2024-01-01', 'Instalación exitosa'),
      (2, 'Antena Inalámbrica', 'Ubiquiti', 'NanoStation M5', 'SN002345', 'operativo', '2024-01-05', 'Buena señal'),
      (3, 'Módem Cable', 'Motorola', 'MB7621', 'SN003456', 'necesita_revision', '2024-01-03', 'Intermitencias')
      ON CONFLICT DO NOTHING
    `

    return { success: true }
  } catch (error) {
    console.error("Error creating tables:", error)
    return { success: false, error: error.message }
  }
}

export default async function Dashboard() {
  const stats = await getDashboardStats()

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-6">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">Sistema de Gestión ISP</h1>
              <p className="text-gray-600">Panel de control y administración</p>
            </div>
            <div className="flex space-x-4">
              {!stats.tablesExist && (
                <form action="/api/setup" method="POST">
                  <Button type="submit" className="bg-green-600 hover:bg-green-700">
                    <Database className="h-4 w-4 mr-2" />
                    Crear Tablas
                  </Button>
                </form>
              )}
              <Link href="/clientes" className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Gestionar Clientes
              </Link>
            </div>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Error de base de datos */}
        {stats.error && (
          <div className="mb-8 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div className="flex items-center">
              <AlertTriangle className="h-5 w-5 text-red-500 mr-2" />
              <h3 className="text-lg font-medium text-red-800">Error de Base de Datos</h3>
            </div>
            <p className="mt-2 text-red-700">{stats.error}</p>
            <div className="mt-4">
              <h4 className="font-medium text-red-800">Soluciones:</h4>
              <ol className="mt-2 list-decimal list-inside text-red-700 space-y-1">
                <li>Verifica que tu base de datos Neon esté configurada correctamente</li>
                <li>Ejecuta los scripts SQL para crear las tablas</li>
                <li>Verifica la variable de entorno DATABASE_URL</li>
                <li>Haz clic en "Crear Tablas" para configurar automáticamente</li>
              </ol>
              <div className="mt-4 space-x-2">
                <form action="/api/setup" method="POST" className="inline">
                  <Button type="submit" className="bg-green-600 hover:bg-green-700">
                    <Database className="h-4 w-4 mr-2" />
                    Crear Tablas Automáticamente
                  </Button>
                </form>
                <Button onClick={() => window.location.reload()} variant="outline">
                  <RefreshCw className="h-4 w-4 mr-2" />
                  Recargar Página
                </Button>
              </div>
            </div>
          </div>
        )}

        {/* Estadísticas principales */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Clientes Activos</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.clientes.activos}</div>
              <p className="text-xs text-muted-foreground">de {stats.clientes.total} clientes totales</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Ingresos del Mes</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">${Number(stats.pagos.ingresos_mes).toLocaleString()}</div>
              <p className="text-xs text-muted-foreground">{stats.pagos.total_pagos} pagos procesados</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pagos Vencidos</CardTitle>
              <AlertTriangle className="h-4 w-4 text-red-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">{stats.pagos.pagos_vencidos}</div>
              <p className="text-xs text-muted-foreground">{stats.pagos.pagos_pendientes} pendientes</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Equipos Operativos</CardTitle>
              <Wifi className="h-4 w-4 text-green-500" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{stats.equipos.operativos}</div>
              <p className="text-xs text-muted-foreground">{stats.equipos.necesitan_revision} necesitan revisión</p>
            </CardContent>
          </Card>
        </div>

        {/* Navegación rápida */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <Link href="/clientes" className="block">
            <Card className="hover:shadow-lg transition-shadow cursor-pointer">
              <CardHeader>
                <CardTitle className="flex items-center">
                  <Users className="h-5 w-5 mr-2" />
                  Gestión de Clientes
                </CardTitle>
                <CardDescription>Administrar clientes, estados y tipos de conexión</CardDescription>
              </CardHeader>
            </Card>
          </Link>

          <Link href="/pagos" className="block">
            <Card className="hover:shadow-lg transition-shadow cursor-pointer">
              <CardHeader>
                <CardTitle className="flex items-center">
                  <DollarSign className="h-5 w-5 mr-2" />
                  Pagos y Facturación
                </CardTitle>
                <CardDescription>Seguimiento de pagos, facturas y alertas</CardDescription>
              </CardHeader>
            </Card>
          </Link>

          <Link href="/equipos" className="block">
            <Card className="hover:shadow-lg transition-shadow cursor-pointer">
              <CardHeader>
                <CardTitle className="flex items-center">
                  <Wifi className="h-5 w-5 mr-2" />
                  Equipos Técnicos
                </CardTitle>
                <CardDescription>Control de equipos instalados y mantenimiento</CardDescription>
              </CardHeader>
            </Card>
          </Link>

          <Link href="/reportes" className="block">
            <Card className="hover:shadow-lg transition-shadow cursor-pointer">
              <CardHeader>
                <CardTitle className="flex items-center">
                  <TrendingUp className="h-5 w-5 mr-2" />
                  Reportes
                </CardTitle>
                <CardDescription>Estadísticas e informes detallados</CardDescription>
              </CardHeader>
            </Card>
          </Link>
        </div>

        {/* Próximos vencimientos */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <Calendar className="h-5 w-5 mr-2" />
              Próximos Vencimientos (7 días)
            </CardTitle>
            <CardDescription>Pagos que vencen en los próximos días</CardDescription>
          </CardHeader>
          <CardContent>
            {stats.proximosVencimientos.length === 0 ? (
              <p className="text-gray-500 text-center py-4">No hay pagos próximos a vencer</p>
            ) : (
              <div className="space-y-3">
                {stats.proximosVencimientos.map((pago: any) => (
                  <div
                    key={pago.id}
                    className="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200"
                  >
                    <div>
                      <p className="font-medium">{pago.cliente_nombre}</p>
                      <p className="text-sm text-gray-600">Factura: {pago.numero_factura}</p>
                    </div>
                    <div className="text-right">
                      <p className="font-bold">${Number(pago.monto).toLocaleString()}</p>
                      <Badge variant="outline" className="text-yellow-700 border-yellow-300">
                        Vence: {new Date(pago.fecha_vencimiento).toLocaleDateString()}
                      </Badge>
                    </div>
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
