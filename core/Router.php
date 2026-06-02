<?php
class Router {
    private $routes = [];
    private $basePath = '';
    
    public function __construct() {
        // Determinar el directorio base a partir de BASE_URL definido en config
        if (defined('BASE_URL')) {
            $path = parse_url(BASE_URL, PHP_URL_PATH);
            $this->basePath = $path ?: '';
        } else {
            $this->basePath = '';
        }
    }
    
    public function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];
        
        // Remover query string
        $requestPath = parse_url($requestUri, PHP_URL_PATH);
        
        // Remover el directorio base
        if ($this->basePath && strpos($requestPath, $this->basePath) === 0) {
            $requestPath = substr($requestPath, strlen($this->basePath));
        }
        
        // Asegurar que la ruta comience con /
        if (!$requestPath || $requestPath === '') {
            $requestPath = '/';
        }
        
        // Ruta por defecto
        if ($requestPath === '/') {
            $requestPath = '/dashboard';
        }
        
        // Buscar coincidencia de ruta
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestPath)) {
                $controllerName = $route['controller'];
                $actionName = $route['action'];
                
                // Verificar que la clase del controlador existe
                if (!class_exists($controllerName)) {
                    $this->handleError(500, "Controlador {$controllerName} no encontrado");
                    return;
                }
                
                $controller = new $controllerName();
                
                // Verificar que el método existe
                if (!method_exists($controller, $actionName)) {
                    $this->handleError(500, "Método {$actionName} no encontrado en {$controllerName}");
                    return;
                }
                
                // Extraer parámetros de la URL
                $params = $this->extractParams($route['path'], $requestPath);
                
                try {
                    // Ejecutar el controlador
                    call_user_func_array([$controller, $actionName], $params);
                    return;
                } catch (Exception $e) {
                    $this->handleError(500, "Error en controlador: " . $e->getMessage());
                    return;
                }
            }
        }
        
        // Si no se encuentra la ruta, mostrar 404
        $this->handleError(404, "Página no encontrada");
    }
    
    private function matchPath($routePath, $requestPath) {
        // Convertir parámetros {id} a expresiones regulares
        $routePattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        return preg_match($routePattern, $requestPath);
    }
    
    private function extractParams($routePath, $requestPath) {
        $routePattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        preg_match($routePattern, $requestPath, $matches);
        array_shift($matches); // Remover el match completo
        return $matches;
    }
    
    private function handleError($code, $message) {
        http_response_code($code);
        
        if ($code === 404) {
            if (file_exists('views/errors/404.php')) {
                include 'views/errors/404.php';
            } else {
                echo "<h1>404 - Página no encontrada</h1>";
                echo "<p>La página que buscas no existe.</p>";
                echo "<a href='/dashboard'>Volver al Dashboard</a>";
            }
        } else {
            if (DEBUG_MODE) {
                echo "<h1>Error {$code}</h1>";
                echo "<p>{$message}</p>";
                echo "<a href='/dashboard'>Volver al Dashboard</a>";
            } else {
                echo "<h1>Error del servidor</h1>";
                echo "<p>Ha ocurrido un error interno.</p>";
                echo "<a href='/dashboard'>Volver al Dashboard</a>";
            }
        }
    }
    
    public function getBasePath() {
        return $this->basePath;
    }
}
?>
