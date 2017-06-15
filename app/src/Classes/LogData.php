<?php

namespace App\Classes;

class LogData
{
    private $id;
    private $name;
    private $subject;
    private $body;
    private $email;
    private $url;
    private $del_pass;
    private $host;
    private $created;

    public function __construct($data)
    {
        $this->id = $data['id'] ?? $this->generateId();
        $this->name = $data['name'] ?? '';
        $this->subject = $data['subject'] ?? '';
        $this->body = $data['body'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->url = $data['url'] ?? '';
        $this->del_pass = (mb_strlen($data['del_pass']) > 0) ? $this->createDeletePass($data['del_pass']) : '';
        $this->host = $data['host'] ?? '';
        $this->created = $data['created'] ?? date_create('now');
    }

    public function generateId()
    {
        return bin2hex(openssl_random_pseudo_bytes(6));
    }

    public function createDeletePass($pass)
    {
        $password = new \App\Classes\Password();
        return $password->toHashPassword($pass);
    }

    public function getData()
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'subject'  => $this->subject,
            'body'     => $this->body,
            'email'    => $this->email,
            'url'      => $this->url,
            'del_pass' => $this->del_pass,
            'host'     => $this->host,
            'created'  => $this->created->format('Y-m-d H:i:s')
        ];
    }
}