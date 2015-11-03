<?php
namespace App\Classes;

class Config
{
    public $path;
    private $config;

    public function __construct($path) {
        $this->path = $path;

        $data = file_get_contents($this->path);
        $this->config = json_decode($data, true);
    }

    public function getConfig ($key) {
        return $this->config[$key];
    }
}