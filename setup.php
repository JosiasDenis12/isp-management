<?php
require_once 'config/config.php';

echo "<h1>Configuración del Sistema ISP</h1>";

// Verificar extensiones PHP
echo "<h2>1. Verificando extensiones PHP...</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext: Instalado<br>";
    } else {
        echo "❌ $ext: NO instalado<br>";
    }
}

// Verificar conexión a base de datos
echo "<h2>2. Verificando conexión a base de datos...</h2>";
try {
    $database = new Database();
    $conn = $database->getConnection();
    echo "✅ Conexión a base de datos: Exitosa<br>";
    
    // Verificar si las tablas existen
    echo "<h3>Verificando tablas...</h3>";
    $tables = ['clientes', 'pagos', 'equipos', 'visitas_tecnicas'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla '$table': Existe<br>";
        } else {
            echo "❌ Tabla '$table': NO existe<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
    echo "<p><strong>Solución:</strong></p>";
    echo "<ol>";
    echo "<li>Verifica que MySQL esté ejecutándose</li>";
    echo "<li>Crea la base de datos 'isp_management'</li>";
    echo "<li>Ejecuta los scripts SQL en database/01-create-tables.sql</li>";
    echo "<li>Configura las credenciales en config/database.php</li>";
    echo "</ol>";
}

echo "<h2>3. Instrucciones de instalación:</h2>";
echo "<ol>";
echo "<li><strong>Crear base de datos:</strong><br>";
echo "<code>CREATE DATABASE isp_management;</code></li>";
echo "<li><strong>Ejecutar script de tablas:</strong><br>";
echo "Ejecuta el contenido de <code>database/01-create-tables.sql</code></li>";
echo "<li><strong>Ejecutar datos de prueba:</strong><br>";
echo "Ejecuta el contenido de <code>database/02-seed-data.sql</code></li>";
echo "<li><strong>Configurar credenciales:</strong><br>";
echo "Edita <code>config/database.php</code> con tus credenciales</li>";
echo "</ol>";

echo "<h2>4. Estructura de archivos:</h2>";
echo "<pre>";
echo "/
├── config/          # Configuración
├── controllers/     # Controladores MVC
├── models/         # Modelos de datos
├── views/          # Vistas HTML
├── core/           # Router y clases base
├── database/       # Scripts SQL
├── setup.php       # Este archivo
└── index.php       # Punto de entrada
";
echo "</pre>";

echo "<p><a href='index.php'>🚀 Ir al sistema</a></p>";
?>
