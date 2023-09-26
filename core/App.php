<?php

namespace MyFramework;

use MyFramework\Router;
use MyFramework\Response;
use MyFramework\Exceptions\HttpException;
use MyFramework\Exceptions\MyFrameworkException;
use Throwable;

class App
{
    public function __construct()
    {
        $this->checkEnv();
    }

    private function checkEnv(): void
    {
        if(empty($_ENV['ROOT_PROJECT'])) {
            throw new MyFrameworkException('No has definido el ROOT_PROJECT en tus variables de entorno');
        }

        if(!is_dir($_ENV['ROOT_PROJECT'])) {
            throw new MyFrameworkException("El directorio ROOT_PROJECT no existe.");
        }

        $last_char = substr($_ENV['ROOT_PROJECT'], -1);

        if(in_array($last_char, ['/', '\\'])) {
            $_ENV['ROOT_PROJECT'] = substr_replace($_ENV['ROOT_PROJECT'] ,"",-1);
        }

        if(!isset($_ENV['DEBUG_MODE'])) {
            throw new MyFrameworkException('Debes definir la variable DEBUG_MODE en "true" o "false"');
        }

        if(is_bool($_ENV['DEBUG_MODE'])) {
            return;
        }

        if(!is_string($_ENV['DEBUG_MODE'])) {
            throw new MyFrameworkException('Formato invalido para variable de entorno DEBUG_MODE, valor permitidos "true" o "false"');
        }

        $debug_mode = strtolower($_ENV['DEBUG_MODE']);
        if($debug_mode === 'true') {
            $_ENV['DEBUG_MODE'] = true;
        }
        elseif($debug_mode === 'false') {
            $_ENV['DEBUG_MODE'] = false;
        }
        else {
            throw new MyFrameworkException('Formato invalido para variable de entorno DEBUG_MODE, valor permitidos "true" o "false"');
        }
    }

    public function send()
    {

        try {
            $route_info = Router::getRouteInfo();

            $path_params = $route_info['path_params'];
            $handler = $route_info['handler'];

            if(is_callable($handler) || !preg_match('/@/', $handler)) {
                $response = call_user_func_array($handler, array_values($path_params));
            }
            else {
                list($class, $method) = explode('@', $handler, 2);
                $response = call_user_func_array([new $class, $method], array_values($path_params));
            }

            if(!($response instanceof Response)) {
                throw new MyFrameworkException('Formato de respuesta invalido');
            }

            $response->returnData();
        }
        catch(HttpException $e) {
            $response = new Response('json', $e->getMessage(), $e->getCode());
            $response->returnData();
        }
        catch(Throwable $e) {
            if($_ENV['DEBUG_MODE']) {
                throw $e;
            }
            $this->saveLog($e);
            $response = new Response('json', 'Internal Server Error', 500);
            $response->returnData();
        }
    }

    private function saveLog($e): void
    {
        $log_dir = $_ENV['ROOT_PROJECT'] . '/logs/';

        if(!is_dir($log_dir)) {
            mkdir($log_dir);
        }

        $data = [
            'DATE' => date('Y-m-d H:i:s'),
            'ENDPOINT' => $_SERVER['REQUEST_URI'] ?? '',
            'METHOD' => $_SERVER['REQUEST_METHOD'] ?? '',
            'MESSAGE_ERROR' => $e->getMessage(),
            'TRACE' => $e->getTrace()[0]
        ];

        $log_file = $log_dir . 'logs-' . date('Y-m-d') . '.log';

        file_put_contents($log_file, json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
    }
}