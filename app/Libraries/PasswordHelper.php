<?php

namespace App\Libraries;

class PasswordHelper
{
    /**
     * 创建用户密码
     * @param string $password
     * @return string
     */
    public static function createUserPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * 检测用户密码
     * @param string $verifyPassword 需要被验证的密码
     * @param string $password 数据库中加密的密码
     * @return bool
     */
    public static function checkUserPassword(string $verifyPassword, string $password): bool
    {
        return password_verify($verifyPassword, $password);
    }
}
