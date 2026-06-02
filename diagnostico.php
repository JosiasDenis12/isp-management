<?php
echo "<h1>Diagnóstico del Sistema</h1>";

echo "<h2>Información del Servidor</h2>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>HTTP Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";

echo "<h2>Rutas Esperadas</h2>";
echo "<ul>";
echo "<li>Dashboard: <a href='http://localhost/isp-management/'>http://localhost/isp-management/</a></li>";
echo "<li>Clientes: <a href='http://localhost/isp-management/clientes'>http://localhost/isp-management/clientes</a></li>";
echo "<li>Pagos: <a href='http://localhost/isp-management/pagos'>http://localhost/isp-management/pagos</a></li>";
echo "<li>Equipos: <a href='http://localhost/isp-management/equipos'>http://localhost/isp-management/equipos</a></li>";
echo "</ul>";

echo "<h2>Estado de los Archivos</h2>";
echo "<p>.htaccess existe: " . (file_exists('.htaccess') ? 'SÍ' : 'NO') . "</p>";
echo "<p>Router.php existe: " . (file_exists('core/Router.php') ? 'SÍ' : 'NO') . "</p>";
echo "<p>ClienteController.php existe: " . (file_exists('controllers/ClienteController.php') ? 'SÍ' : 'NO') . "</p>";

echo "<h2>Módulos de Apache</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<p>mod_rewrite está " . (in_array('mod_rewrite', $modules) ? 'HABILITADO' : 'DESHABILITADO') . "</p>";
} else {
    echo "<p>No se puede verificar el estado de mod_rewrite</p>";
}
?>
