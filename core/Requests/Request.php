<?php 

namespace MyFramework\Requests;
class Request 
{
    private $data = [];

    public function setData(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getData(string $key)
    {
        return $this->data[$key] ?? null;
    }

}