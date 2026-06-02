import { neon } from "@neondatabase/serverless"

if (!process.env.DATABASE_URL) {
  throw new Error("DATABASE_URL must be set")
}

export const sql = neon(process.env.DATABASE_URL)

// Tipos de datos
export interface Cliente {
  id: number
  nombre: string
  direccion: string
  telefono: string
  email: string
  estado: "activo" | "suspendido" | "pendiente"
  tipo_conexion: "fibra_optica" | "inalambrica" | "cable_coaxial"
  fecha_contratacion: string
  plan_mensual: number
  created_at: string
  updated_at: string
}

export interface Pago {
  id: number
  cliente_id: number
  monto: number
  fecha_pago: string
  fecha_vencimiento: string
  metodo_pago: "transferencia" | "efectivo" | "paypal" | "tarjeta"
  estado: "pagado" | "pendiente" | "vencido"
  numero_factura: string
  observaciones: string
  created_at: string
  cliente_nombre?: string
}

export interface Equipo {
  id: number
  cliente_id: number
  tipo_equipo: string
  marca: string
  modelo: string
  numero_serie: string
  estado_tecnico: "operativo" | "necesita_revision" | "dañado"
  fecha_instalacion: string
  observaciones_tecnico: string
  created_at: string
  updated_at: string
  cliente_nombre?: string
}

export interface VisitaTecnica {
  id: number
  cliente_id: number
  equipo_id: number
  fecha_visita: string
  tipo_visita: "instalacion" | "mantenimiento" | "reparacion" | "revision"
  tecnico_nombre: string
  observaciones: string
  estado: "programada" | "completada" | "cancelada"
  created_at: string
  cliente_nombre?: string
  equipo_tipo?: string
}
