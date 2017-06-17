<?php

namespace App\Classes;

class File
{
    private $fp;
    private $path;

    public function open(string $path, string $mode)
    {
        $this->close();

        if (!is_readable($path)) {
            throw new \RuntimeException('ログファイルは読み込みできません');
        }
        if (!is_writable($path)) {
            throw new \RuntimeException('ログファイルは書き込みできません');
        }
        $this->path = $path;
        $this->fp = fopen($path, $mode);
        if (!is_resource($this->fp)) {
            throw new \RuntimeException('ログファイルが開けません');
        }
        flock($this->fp, LOCK_EX);
    }

    public static function create(string $file_path)
    {
        // ログファイルがないときに作成
        if (!file_exists($file_path)) {
            touch($file_path);
        }
    }

    public function read()
    {
        $file_size = filesize($this->path);
        $data = null;
        if ($file_size > 0) {
            $data = fread($this->fp, filesize($this->path));
        }
        return $data;
    }

    public function write($data)
    {
        rewind($this->fp);
        ftruncate($this->fp, 0);
        if (fwrite($this->fp, $data) == false) {
            throw new \Exception('書き込みに失敗');
        }
        fflush($this->fp);
    }

    private function close()
    {
        if ($this->fp == null) {
            return;
        }

        flock($this->fp, LOCK_UN);
        fclose($this->fp);
    }

    public function __destruct()
    {
        $this->close();
    }
}