<?php


if(!function_exists('view')) {
    function view(
        string $type,
        $data = null,
        int $status_code = 200,
        array $headers = []
    ): MyFramework\Response
    {
        return new MyFramework\Response($type, $data, $status_code, $headers);
    }
}