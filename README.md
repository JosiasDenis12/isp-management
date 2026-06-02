# Sistema de Gestión ISP - PHP MVC

Sistema completo de gestión de ventas y pagos para proveedores de internet desarrollado en PHP con patrón MVC.

## 🚀 Instalación Rápida

### 1. Requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache/Nginx con mod_rewrite
- Extensiones PHP: PDO, PDO_MySQL

### 2. Configuración de Base de Datos

\`\`\`sql
-- Crear base de datos
CREATE DATABASE isp_management;
USE isp_management;

-- Ejecutar scripts
-- 1. database/01-create-tables.sql
-- 2. database/02-seed-data.sql
\`\`\`

### 3. Configuración del Sistema

1. **Clonar/descargar archivos** en tu servidor web
2. **Configurar base de datos** en `config/database.php`:
   \`\`\`php
   private $host = 'localhost';
   private $db_name = 'isp_management';
   private $username = 'tu_usuario';
   private $password = 'tu_password';
   \`\`\`
3. **Verificar instalación** visitando `setup.php`
4. **Acceder al sistema** en `index.php`

## 📁 Estructura del Proyecto

\`\`\`
/
├── config/
│   ├── config.php          # Configuración general
│   └── database.php        # Conexión a BD
├── controllers/
│   ├── DashboardController.php
│   ├── ClienteController.php
│   ├── PagoController.php
│   └── EquipoController.php
├── models/
│   ├── Cliente.php
│   ├── Pago.php
│   └── Equipo.php
├── views/
│   ├── layouts/
│   ├── dashboard/
│   ├── clientes/
│   ├── pagos/
│   └── equipos/
├── core/
│   └── Router.php          # Sistema de rutas
├── database/
│   ├── 01-create-tables.sql
│   └── 02-seed-data.sql
├── setup.php               # Verificación de instalación
└── index.php              # Punto de entrada
\`\`\`

## 🔧 Solución de Problemas

### Error: "relation clientes does not exist"
Este error indica que estás ejecutando el código Next.js en lugar del PHP.

**Solución:**
1. Asegúrate de estar ejecutando el código PHP
2. Verifica que la base de datos esté creada
3. Ejecuta los scripts SQL
4. Visita `setup.php` para verificar la instalación

### Error de conexión a base de datos
1. Verifica que MySQL esté ejecutándose
2. Confirma las credenciales en `config/database.php`
3. Asegúrate de que la base de datos `isp_management` exista

### URLs no funcionan (404)
1. Habilita mod_rewrite en Apache
2. Configura el DocumentRoot correctamente
3. Verifica permisos de archivos

## 🌟 Características

- ✅ **Dashboard** con estadísticas en tiempo real
- ✅ **Gestión de Clientes** con estados y tipos de conexión
- ✅ **Pagos y Facturación** con generación automática de facturas
- ✅ **Equipos Técnicos** con seguimiento de estado
- ✅ **Diseño Responsivo** con Bootstrap 5
- ✅ **Seguridad** con prepared statements
- ✅ **Arquitectura MVC** limpia y escalable

## 🔗 URLs del Sistema

- `/` o `/dashboard` - Panel principal
- `/clientes` - Gestión de clientes
- `/clientes/create` - Nuevo cliente
- `/pagos` - Pagos y facturación
- `/pagos/create` - Registrar pago
- `/equipos` - Equipos técnicos
- `/equipos/create` - Registrar equipo

## 🛠️ Desarrollo

Para añadir nuevas funcionalidades:

1. **Modelo**: Crear en `models/`
2. **Controlador**: Crear en `controllers/`
3. **Vista**: Crear en `views/`
4. **Ruta**: Añadir en `index.php`

## 📞 Soporte

Si encuentras problemas:
1. Visita `setup.php` para diagnóstico
2. Verifica logs de PHP y MySQL
3. Confirma que todos los archivos estén subidos correctamente
