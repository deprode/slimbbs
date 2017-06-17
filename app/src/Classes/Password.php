<?php

namespace App\Classes;

class Password
{
    public function __construct(string $admin_id = null, string $password = null)
    {
        $this->admin_id = $admin_id;
        $this->admin_pass = $password;
    }

    // パスワード検証
    public function checkPassword($input_pass, $del_pass)
    {
        return is_null($input_pass) || password_verify((string)$input_pass, $del_pass);
    }

    // 暗号化済みのパスワード生成
    public function toHashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function checkAdminPassword(string $id = null, string $password = null)
    {
        return ($id === $this->admin_id) && (password_verify($password, $this->admin_pass));
    }
}