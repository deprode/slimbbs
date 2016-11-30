<?php
namespace App\Classes;

class Config
{
    public $path;
    private $config;

    public function __construct($path)
    {
        $this->path = $path;

        // 設定を読み込む
        if (is_readable($this->path)) {
            $data = file_get_contents($this->path);
            $this->config = json_decode($data, true);
        } else {
            throw new \Exception("Unreadable config file");
        }
    }

    public function getConfig($key)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }
        return null;
    }

    public function getConfigs()
    {
        return $this->config;
    }

    public function setConfigs($configs)
    {
        if ($configs && is_array($configs)) {
            $this->config = $configs;
        }
    }

    // クラス内の$configsに格納されている設定を保存する
    public function saveConfig()
    {
        if (!$this->path || !$this->config) {
            return false;
        }
        
        // JSON形式に変換して保存
        $data = json_encode($this->config);
        $result = file_put_contents($this->path, $data, LOCK_EX);

        return $result !== false;
    }
}
