<?php

namespace MyFramework;

use MyFramework\Exceptions\HttpException;
use MyFramework\Exceptions\RouterException;
use MyFramework\Middlewares\Middleware;
use MyFramework\Requests\Request;
use phpDocumentor\Reflection\Types\Void_;

class Router
{
    private static $urls = [];
    private const METHODS = ['GET', 'POST', 'PUT', 'PATH', 'DELETE'];

    private static function route(string $method, string $route, $handler, string $middleware = null): void
    {
        $invalid_type = !is_callable($handler) && !is_string($handler);
        $invalid_format = is_string($handler) && !preg_match('/@/', $handler);
        if($invalid_type || $invalid_type){
            throw new RouterException('Metodo invalido ' . $method);
        }

        if(!isset(self::$urls[$method])) {
            self::$urls[$method] = [];
        }

        if(isset(self::$urls[$method][$route])) {
            throw new RouterException("La ruta $route ya existe para el metodo $method");
        }

        self::$urls[$method][$route] = [
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public static function getRouteInfo()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? '';

        //Clean uri
        if(preg_match('/(?<uri>.*?)[\?|#]/', $uri, $m)) {
            $uri = $m['uri'];
        }

        if(!isset(self::$urls[$method])) {
            throw new RouterException("No hay ninguna ruta con método. $method");
        }

        foreach(self::$urls[$method] as $route => &$data) {
            $handler = $data['handler'];
            $middleware = $data['middleware'];
            if(preg_match('/^' . str_replace(['/'], ['\/'], $route) . '$/', $uri, $m)) {
                $path_params = [];

                foreach($m as $key => $val) {
                    if(is_numeric($key)){
                        continue;
                    }

                    $path_params[$key] = $val;
                }

                $request = new Request();
                if($middleware) {
                    $middleware = new $middleware;
                    if(!($middleware instanceof Middleware)) {
                        throw new RouterException("Middleware invalido, no extiende de MyFramework\\Middlewares\\Middleware");
                    }
                    $request = $middleware->handle($request);
                }
                $path_params['request'] = $request;

                return [
                    'handler' => $handler,
                    'path_params' => $path_params
                ];
            }
        }
        unset($data);
        throw new HttpException('Not found', 404);
    }

    public static function get(string $route, $handler, string $middleware = null): void
    {
        self::route('GET', $route, $handler, $middleware);
    }

    public static function post(string $route, $handler, string $middleware = null): void
    {
        self::route('POST', $route, $handler, $middleware);
    }

    public static function put(string $route, $handler, string $middleware = null): void
    {
        self::route('PUT', $route, $handler, $middleware);
    }

    public static function path(string $route, $handler, string $middleware = null): void
    {
        self::route('PATH', $route, $handler, $middleware);
    }

    public static function delete(string $route, $handler, string $middleware = null): void
    {
        self::route('DELETE', $route, $handler, $middleware);
    }

}