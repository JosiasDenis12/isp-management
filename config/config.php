<?php
// Configuración general de la aplicación
define('BASE_URL', 'http://localhost/isp-management');
define('APP_NAME', 'Sistema de Gestión ISP');
define('APP_VERSION', '1.0.0');

// Configuración de zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de errores (cambiar a false en producción)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Autoload de clases
spl_autoload_register(function ($class_name) {
    $directories = [
        'models/',
        'controllers/',
        'core/',
        'config/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Función para generar URLs correctas
function url($path = '') {
    // Derivar el basePath desde BASE_URL para evitar hardcodear rutas
    $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
    // Asegurar formato correcto (sin doble slash)
    $basePath = rtrim($basePath, '/');
    return $basePath . '/' . ltrim($path, '/');
}

// Función para verificar si la ruta actual coincide
function isActiveRoute($route) {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    if ($basePath && strpos($currentPath, $basePath) === 0) {
        $currentPath = substr($currentPath, strlen($basePath));
    }
    return strpos($currentPath, $route) !== false;
}

// ----------- Sesiones / Autenticación simple -----------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAuthenticated() {
    return isset($_SESSION['auth']) && $_SESSION['auth'] === true;
}

function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: ' . url('login'));
        exit;
    }
}

function loginUser($username) {
    $_SESSION['auth'] = true;
    $_SESSION['username'] = $username;
}

function logoutUser() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
?>
