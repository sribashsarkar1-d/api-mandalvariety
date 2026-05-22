<?php
namespace Core;

class Router {
    private $routes = [];

    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    public function resolve() {
        $method = Request::getMethod();
        $uri = Request::getUri();

        if ($method === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            exit;
        }

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $callback) {
                if ($route === $uri) {
                    if (is_array($callback)) {
                        $controller = new $callback[0]();
                        $action = $callback[1];
                        return $controller->$action();
                    }
                    return call_user_func($callback);
                }
            }
        }
        
        Response::error('Route not found', 404);
    }
}
