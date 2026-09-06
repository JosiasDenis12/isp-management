<?php
require_once 'config/config.php';
require_once 'core/Router.php';

// Incluir controladores
require_once 'controllers/DashboardController.php';
require_once 'controllers/ClienteController.php';
require_once 'controllers/PagoController.php';
require_once 'controllers/EquipoController.php';
require_once 'controllers/ReporteController.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/SearchController.php';
require_once 'controllers/ConfiguracionController.php';

$router = new Router();

// Guardado simple: si no está autenticado y ruta distinta a /login, redirigir.
// Deja pasar recursos públicos específicos si se necesitan (por ahora solo login).
if (!isAuthenticated()) {
	$publicPaths = ['/login'];
	$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
	if ($basePath && strpos($currentPath, $basePath) === 0) {
		$currentPath = substr($currentPath, strlen($basePath));
	}
	if (!in_array($currentPath, $publicPaths)) {
		header('Location: ' . url('login'));
		exit;
	}
}

// Definir rutas
$router->addRoute('GET', '/dashboard', 'DashboardController', 'index');
$router->addRoute('GET', '/', 'DashboardController', 'index');
$router->addRoute('GET', '/dashboard/actividad', 'DashboardController', 'actividad');
$router->addRoute('GET', '/dashboard/calendario', 'DashboardController', 'calendario');

// Rutas de clientes
$router->addRoute('GET', '/clientes', 'ClienteController', 'index');
$router->addRoute('GET', '/clientes/create', 'ClienteController', 'create');
$router->addRoute('POST', '/clientes/create', 'ClienteController', 'create');
$router->addRoute('GET', '/clientes/{id}', 'ClienteController', 'show');
$router->addRoute('GET', '/clientes/{id}/edit', 'ClienteController', 'edit');
$router->addRoute('POST', '/clientes/{id}/edit', 'ClienteController', 'edit');
$router->addRoute('GET', '/clientes/{id}/delete-summary', 'ClienteController', 'deleteSummary');
$router->addRoute('POST', '/clientes/{id}/delete', 'ClienteController', 'delete');
$router->addRoute('GET', '/clientes/{id}/update-status', 'ClienteController', 'updateStatus');

// Rutas de pagos
$router->addRoute('GET', '/pagos', 'PagoController', 'index');
$router->addRoute('GET', '/pagos/kpis', 'PagoController', 'kpis');
$router->addRoute('GET', '/pagos/create', 'PagoController', 'create');
$router->addRoute('POST', '/pagos/create', 'PagoController', 'create');
$router->addRoute('GET', '/pagos/{id}', 'PagoController', 'show');
$router->addRoute('POST', '/pagos/{id}/edit', 'PagoController', 'edit');
$router->addRoute('POST', '/pagos/{id}/delete', 'PagoController', 'delete');
$router->addRoute('GET', '/pagos/{id}/print', 'PagoController', 'print');
$router->addRoute('POST', '/pagos/{id}/marcar-pagado', 'PagoController', 'marcarPagado');
$router->addRoute('POST', '/pagos/{id}/recordatorio', 'PagoController', 'enviarRecordatorio');

// Rutas de equipos
$router->addRoute('GET', '/equipos', 'EquipoController', 'index');
$router->addRoute('GET', '/equipos/create', 'EquipoController', 'create');
$router->addRoute('POST', '/equipos/create', 'EquipoController', 'create');
$router->addRoute('GET', '/equipos/{id}/edit', 'EquipoController', 'edit');
$router->addRoute('POST', '/equipos/{id}/edit', 'EquipoController', 'edit');
$router->addRoute('GET', '/equipos/{id}', 'EquipoController', 'show');
$router->addRoute('POST', '/equipos/{id}/delete', 'EquipoController', 'delete');
$router->addRoute('POST', '/equipos/{id}/programar-mantenimiento', 'EquipoController', 'programarMantenimiento');
$router->addRoute('GET', '/equipos/{id}/visitas', 'EquipoController', 'visitas');
$router->addRoute('POST', '/equipos/{id}/visitas', 'EquipoController', 'visitas');
$router->addRoute('GET', '/equipos/{equipoId}/visitas/{visitaId}', 'EquipoController', 'verVisita');
$router->addRoute('GET', '/equipos/{equipoId}/visitas/{visitaId}/edit', 'EquipoController', 'editarVisita');
$router->addRoute('POST', '/equipos/{equipoId}/visitas/{visitaId}/edit', 'EquipoController', 'editarVisita');
$router->addRoute('POST', '/equipos/{equipoId}/visitas/{visitaId}/cancel', 'EquipoController', 'cancelarVisita');
$router->addRoute('POST', '/equipos/{equipoId}/visitas/{visitaId}/delete', 'EquipoController', 'eliminarVisita');
$router->addRoute('GET', '/equipos/{id}/cambiar-estado', 'EquipoController', 'cambiarEstado');
$router->addRoute('POST', '/equipos/{id}/cambiar-estado', 'EquipoController', 'cambiarEstado');
$router->addRoute('GET', '/equipos/{id}/reporte', 'EquipoController', 'generarReporte');

// Rutas de reportes
$router->addRoute('GET', '/reportes', 'ReporteController', 'index');
$router->addRoute('GET', '/reportes/pagos', 'ReporteController', 'pagos');
$router->addRoute('GET', '/reportes/pagos/excel', 'ReporteController', 'pagosExcel');
$router->addRoute('GET', '/reportes/suscripciones', 'ReporteController', 'suscripciones');
$router->addRoute('GET', '/reportes/equipos-visitas', 'ReporteController', 'equiposVisitas');
$router->addRoute('GET', '/reportes/equipos-instalados', 'ReporteController', 'equiposInstalados');

// Búsqueda global
$router->addRoute('GET', '/search', 'SearchController', 'index');
$router->addRoute('GET', '/search/suggest', 'SearchController', 'suggest');

// Configuración y restauración de respaldos (la operación se ejecuta en Electron).
$router->addRoute('GET', '/configuracion', 'ConfiguracionController', 'index');

// Rutas de autenticación
$router->addRoute('GET', '/login', 'AuthController', 'login');
$router->addRoute('POST', '/login', 'AuthController', 'login');
$router->addRoute('GET', '/logout', 'AuthController', 'logout');

// Despachar la ruta
$router->dispatch();
?>
