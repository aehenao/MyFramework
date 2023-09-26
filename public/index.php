<?php

// var_dump(__DIR__ . '/./vendor/autoload.php');
// exit();
require __DIR__ . '/../vendor/autoload.php';

$_ENV['DEBUG_MODE'] = true;
$_ENV['ROOT_PROJECT'] = __DIR__ . '/../';

use MyFramework\Router;

Router::get('/hello', function() {
    return view('json', 'Hello world');
});
Router::post('/hola', function() {
    return view('json', 'Hello world');
});

$app = new MyFramework\App();
$app->send();