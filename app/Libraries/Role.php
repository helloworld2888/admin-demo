<?php
/**
 * 用在角色的代号管理,方便后期修改code值后统一修改
 */

namespace App\Libraries;

class Role
{
    const SUPER_ADMIN = 'Super Admin';  //超级管理员

    /**
     * 获取所有常量数组
     * @return array
     */
    public static function getConstants(): array
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    /**
     * 判断角色在不在定义列表中
     * @param string $string
     * @return bool
     */
    public static function hasRoleByConst(string $string): bool
    {
        $array = self::getConstants();
        if (!in_array($string, $array)) {
            return false;
        }
        return true;
    }
}
