<?php
namespace Core;

class Router {
    private $routes = [];

    public function add($method, $route, $controllerAction) {
        $route = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route);
        $route = '#^' . $route . '$#';
        
        $this->routes[] = [
            'method' => $method,
            'route' => $route,
            'action' => $controllerAction
        ];
    }

    public function dispatch($url, $method) {
        // Remover query string e trailing slash
        $url = parse_url($url, PHP_URL_PATH);
        $url = rtrim($url, '/');
        if (empty($url)) {
            $url = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['route'], $url, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                list($controllerClass, $actionMethod) = explode('@', $route['action']);
                
                $controllerClass = "Controllers\\" . $controllerClass;
                
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $actionMethod)) {
                        return call_user_func_array([$controller, $actionMethod], $params);
                    }
                }
            }
        }
        
        // 404
        http_response_code(404);
        echo "Página não encontrada. URL: $url";
    }
}
