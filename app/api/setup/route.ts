import { sql } from "@/lib/db"
import { NextResponse } from "next/server"

export async function POST() {
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
        tipo_conexion VARCHAR(50) CHECK (tipo_conexion IN ('fibra_optica', 'inalambrica', 'cable_coaxial')),
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

    // Crear tabla de visitas técnicas
    await sql`
      CREATE TABLE IF NOT EXISTS visitas_tecnicas (
        id SERIAL PRIMARY KEY,
        cliente_id INTEGER REFERENCES clientes(id) ON DELETE CASCADE,
        equipo_id INTEGER REFERENCES equipos(id) ON DELETE SET NULL,
        fecha_visita DATE NOT NULL,
        tipo_visita VARCHAR(50) CHECK (tipo_visita IN ('instalacion', 'mantenimiento', 'reparacion', 'revision')),
        tecnico_nombre VARCHAR(255),
        observaciones TEXT,
        estado VARCHAR(20) DEFAULT 'programada' CHECK (estado IN ('programada', 'completada', 'cancelada')),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `

    // Insertar datos de ejemplo
    await sql`
      INSERT INTO clientes (nombre, direccion, telefono, email, estado, tipo_conexion, plan_mensual) VALUES
      ('Juan Pérez', 'Calle 123 #45-67', '+57 300 123 4567', 'juan@email.com', 'activo', 'fibra_optica', 50000.00),
      ('María García', 'Carrera 89 #12-34', '+57 301 234 5678', 'maria@email.com', 'activo', 'inalambrica', 35000.00),
      ('Carlos López', 'Avenida 56 #78-90', '+57 302 345 6789', 'carlos@email.com', 'suspendido', 'cable_coaxial', 40000.00),
      ('Ana Martínez', 'Calle 34 #56-78', '+57 303 456 7890', 'ana@email.com', 'activo', 'fibra_optica', 60000.00),
      ('Luis Rodríguez', 'Carrera 12 #34-56', '+57 304 567 8901', 'luis@email.com', 'pendiente', 'inalambrica', 30000.00)
      ON CONFLICT DO NOTHING
    `

    await sql`
      INSERT INTO pagos (cliente_id, monto, fecha_pago, fecha_vencimiento, metodo_pago, estado, numero_factura) VALUES
      (1, 50000.00, '2024-01-15', '2024-01-15', 'transferencia', 'pagado', 'FAC-001'),
      (1, 50000.00, '2024-02-15', '2024-02-15', 'transferencia', 'pagado', 'FAC-002'),
      (2, 35000.00, '2024-01-20', '2024-01-20', 'efectivo', 'pagado', 'FAC-003'),
      (3, 40000.00, '2024-01-10', '2024-01-10', 'paypal', 'vencido', 'FAC-004'),
      (4, 60000.00, '2024-02-01', '2024-02-01', 'transferencia', 'pagado', 'FAC-005'),
      (5, 30000.00, '2024-02-10', '2024-02-10', 'efectivo', 'pendiente', 'FAC-006'),
      (1, 50000.00, CURRENT_DATE + INTERVAL '5 days', CURRENT_DATE + INTERVAL '5 days', 'transferencia', 'pendiente', 'FAC-007'),
      (2, 35000.00, CURRENT_DATE + INTERVAL '3 days', CURRENT_DATE + INTERVAL '3 days', 'efectivo', 'pendiente', 'FAC-008')
      ON CONFLICT DO NOTHING
    `

    await sql`
      INSERT INTO equipos (cliente_id, tipo_equipo, marca, modelo, numero_serie, estado_tecnico, fecha_instalacion, observaciones_tecnico) VALUES
      (1, 'Router Fibra', 'TP-Link', 'Archer AX73', 'SN001234', 'operativo', '2024-01-01', 'Instalación exitosa, señal excelente'),
      (2, 'Antena Inalámbrica', 'Ubiquiti', 'NanoStation M5', 'SN002345', 'operativo', '2024-01-05', 'Buena recepción de señal'),
      (3, 'Módem Cable', 'Motorola', 'MB7621', 'SN003456', 'necesita_revision', '2024-01-03', 'Intermitencias en la conexión'),
      (4, 'ONU Fibra', 'Huawei', 'HG8245H', 'SN004567', 'operativo', '2024-01-20', 'Configuración óptima'),
      (5, 'Router Inalámbrico', 'Netgear', 'R6700', 'SN005678', 'operativo', '2024-01-25', 'Pendiente optimización')
      ON CONFLICT DO NOTHING
    `

    await sql`
      INSERT INTO visitas_tecnicas (cliente_id, equipo_id, fecha_visita, tipo_visita, tecnico_nombre, observaciones, estado) VALUES
      (1, 1, '2024-01-01', 'instalacion', 'Pedro Técnico', 'Instalación de fibra óptica completada', 'completada'),
      (2, 2, '2024-01-05', 'instalacion', 'Pedro Técnico', 'Instalación de antena inalámbrica', 'completada'),
      (3, 3, CURRENT_DATE + INTERVAL '2 days', 'reparacion', 'Ana Técnico', 'Revisión por intermitencias', 'programada'),
      (4, 4, '2024-01-20', 'instalacion', 'Pedro Técnico', 'Instalación ONU fibra', 'completada'),
      (5, 5, CURRENT_DATE + INTERVAL '5 days', 'mantenimiento', 'Ana Técnico', 'Mantenimiento preventivo', 'programada')
      ON CONFLICT DO NOTHING
    `

    return NextResponse.redirect(new URL("/", process.env.NEXT_PUBLIC_URL || "http://localhost:3000"))
  } catch (error) {
    console.error("Error setting up database:", error)
    return NextResponse.json({ error: error.message }, { status: 500 })
  }
}
