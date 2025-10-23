<?php

namespace App\Enums;

class RoleEnum
{
    const ADMIN = 'admin';
    const CUSTOMER = 'customer';

    public static function getRoles(): array
    {
        return [
            self::ADMIN,
            self::CUSTOMER,
        ];
    }
}