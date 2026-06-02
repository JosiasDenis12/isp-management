<?php
echo "<h1>Diagnóstico de Rutas</h1>";
echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>REQUEST_METHOD:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($basePath === '.') {
    $basePath = '';
}
echo "<p><strong>Base Path:</strong> " . $basePath . "</p>";

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($basePath && strpos($requestPath, $basePath) === 0) {
    $requestPath = substr($requestPath, strlen($basePath));
}
echo "<p><strong>Request Path:</strong> " . $requestPath . "</p>";

require_once 'config/config.php';
echo "<p><strong>URL function test:</strong> " . url('pagos/create') . "</p>";

echo "<h2>Prueba de Rutas:</h2>";
echo "<ul>";
echo "<li><a href='" . url('pagos') . "'>Lista de Pagos</a></li>";
echo "<li><a href='" . url('pagos/create') . "'>Crear Pago</a></li>";
echo "<li><a href='" . url('clientes') . "'>Lista de Clientes</a></li>";
echo "</ul>";
?>
