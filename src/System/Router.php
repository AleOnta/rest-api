<?php

namespace Src\System;

class Router
{

    public array $routes;
    private string $groupPrefix;
    private array $defaultRoute;

    public function __construct()
    {
        $this->routes = [];
        $this->groupPrefix = "";
        $this->defaultRoute = [
            'uri' => '/bad-request',
            'controller' => 'RootController',
            'action' => 'handleBadRequests'
        ];
    }

    private function addRoute(string $uri, string $method, string $controller, string $action)
    {
        $uri = $this->groupPrefix . $uri;
        $route = ['uri' => $uri, 'controller' => $controller, 'action' => $action];
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
        foreach ($this->routes[$method] as $route) {
            $pattern = preg_replace('~\{[a-zA-Z_]+\}~', '(\d+)', $route['uri']);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $this->dispatch($route, $matches);
            }
        }
    }

    private function dispatch(array $route, array $params)
    {
        $controller = new $route['controller']();
        $action = $route['action'];
        $controller->$action(...$params);
    }

    private function debugRoute($route, $params = [])
    {
        echo "<h1>Route:</h1>";
        echo "<pre>" . json_encode($route, JSON_PRETTY_PRINT) . "</pre>";
        echo "<h1>Params:</h1>";
        echo "<pre>" . json_encode($params, JSON_PRETTY_PRINT) . "</pre>";
    }
}
