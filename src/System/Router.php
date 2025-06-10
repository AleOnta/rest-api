<?php

namespace Src\System;

class Router
{

    public array $routes;
    private string $groupPrefix;

    public function __construct()
    {
        $this->routes = [];
        $this->groupPrefix = "";
    }

    private function addRoute(string $uri, string $method, string $controller, string $action)
    {

        $uri = $this->groupPrefix . $uri;
        if (substr($uri, -1, 1) === '/' && strlen($uri) > 1) {
            $uri = substr($uri, 0, strlen($uri) - 1);
        }
        $route = [$uri, $controller, $action];
        $this->routes[$method][] = $route;
    }

    public function group(string $prefix, callable $callback)
    {
        $prevPrefix = $this->groupPrefix;
        $this->groupPrefix = $prefix;
        $callback($this);
        $this->groupPrefix = $prevPrefix;
    }

    public function get(string $uri, string $controller, string $action)
    {
        $this->addRoute($uri, 'GET', $controller, $action);
    }

    public function post(string $uri, string $controller, string $action)
    {
        $this->addRoute($uri, 'POST', $controller, $action);
    }

    public function put(string $uri, string $controller, string $action)
    {
        $this->addRoute($uri, 'PUT', $controller, $action);
    }

    public function patch(string $uri, string $controller, string $action)
    {
        $this->addRoute($uri, 'PATCH', $controller, $action);
    }

    public function delete(string $uri, string $controller, string $action)
    {
        $this->addRoute($uri, 'DELETE', $controller, $action);
    }

    public function match()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route) {
                $pattern = preg_replace('~\{[a-zA-Z_]+\}~', '(\d+)', $route[0]);
                $pattern = "#^{$pattern}$#";

                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches);
                    $this->dispatch($route, $matches);
                    exit;
                }
            }
        }

        # not found
        $this->handleNotFound($uri);
    }

    private function dispatch(array $route, array $params)
    {
        [$uri, $class, $method] = $route;
        $controller = new $class();
        $action = $method;
        $controller->$action(...$params);
    }

    private function handleNotFound($uri)
    {
        http_response_code(404);
        echo json_encode(['error' => 'not found', 'message' => "URL '{$uri}' doesn't match any of our API routes."]);
        exit;
    }

    private function debugRoute($route, $params = [])
    {
        echo "<h1>Route:</h1>";
        echo "<pre>" . json_encode($route, JSON_PRETTY_PRINT) . "</pre>";
        echo "<h1>Params:</h1>";
        echo "<pre>" . json_encode($params, JSON_PRETTY_PRINT) . "</pre>";
    }
}
