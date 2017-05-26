<?php

namespace App\Classes;

class Password
{
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
}