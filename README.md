# ISP-MANAGEMENT

<p align="center">
  <strong>Sistema de gestión integral para proveedores de servicios de Internet (ISP).</strong>
</p>

<p align="center">
  Plataforma de escritorio diseñada para centralizar la gestión de clientes, pagos, servicios, equipos técnicos y operaciones administrativas.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Platform-Windows-0078D4?style=for-the-badge&logo=windows&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/SQLite-Local%20Database-003B57?style=for-the-badge&logo=sqlite&logoColor=white" />
  <img src="https://img.shields.io/badge/Electron-Desktop-47848F?style=for-the-badge&logo=electron&logoColor=white" />
  <img src="https://img.shields.io/badge/Architecture-MVC-111827?style=for-the-badge" />
</p>

---

## 🚀 Acerca de ISP-MANAGEMENT

**ISP-MANAGEMENT** es un sistema de gestión diseñado para proveedores de servicios de Internet (ISP/WISP), desarrollado para facilitar la administración diaria de clientes, pagos, servicios contratados, infraestructura y equipos técnicos.

El sistema centraliza las operaciones principales de un proveedor de Internet en una interfaz moderna, intuitiva y preparada para ejecutarse como aplicación de escritorio.

Su arquitectura permite trabajar localmente mediante **SQLite**, eliminando la necesidad de instalar y configurar un servidor de base de datos externo para su funcionamiento como aplicación distribuible.

---

## ✨ Características principales

### 📊 Dashboard
Visualización centralizada de información relevante para la operación del ISP.

- Estadísticas generales
- Clientes activos
- Clientes próximos a vencer
- Servicios vencidos
- Pagos recientes
- Indicadores operativos
- Resumen general del negocio

### 👥 Gestión de clientes

Administración completa del ciclo de vida de los clientes.

- Registro de nuevos clientes
- Edición de información
- Consulta detallada
- Estado del servicio
- Información de contacto
- Servicio contratado
- Fecha de vencimiento
- Gestión de cortes
- Historial relacionado

### 💳 Gestión de pagos

Control y registro de operaciones financieras.

- Registro de pagos
- Historial de pagos
- Métodos de pago
- Control de vencimientos
- Pagos pendientes
- Información de clientes asociada
- Generación de comprobantes

### 📡 Gestión de equipos

Control de infraestructura y equipos asignados.

- Registro de equipos
- Equipos instalados
- Antenas
- Routers / Modems
- Direcciones IP
- Direcciones MAC
- Credenciales de dispositivos
- Estado del equipo
- Relación con clientes

### 📈 Reportes

Herramientas para consultar y analizar información operativa.

- Reportes de clientes
- Información de pagos
- Equipos registrados
- Estados operativos
- Consultas administrativas

---

# 🖥️ Aplicación de Escritorio

ISP-MANAGEMENT puede ejecutarse como una aplicación de escritorio para Windows.

La aplicación integra:

```text
Electron
   │
   ▼
Aplicación de Escritorio
   │
   ▼
Servidor PHP Portable
   │
   ▼
Arquitectura MVC
   │
   ▼
SQLite
