<?php

namespace MyFramework\Middlewares;

use MyFramework\Requests\Request;

abstract class Middleware
{
    abstract public function handle(Request $request): Request;
}

